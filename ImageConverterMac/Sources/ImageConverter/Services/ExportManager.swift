import Foundation
import AppKit
import UniformTypeIdentifiers
import Combine

@MainActor
public class ExportManager: ObservableObject {
    @Published public var items: [ImageItem] = []
    @Published public var config: ExportConfig = ExportConfig()
    @Published public var isExporting: Bool = false
    @Published public var exportProgress: Double = 0.0
    @Published public var currentProcessingName: String = ""
    @Published public var currentProcessingIndex: Int = 0
    @Published public var totalToProcess: Int = 0
    @Published public var lastExportFolderURL: URL?
    @Published public var showSuccessSheet: Bool = false
    @Published public var errorMessage: String?
    @Published public var totalOriginalBytes: Int64 = 0
    @Published public var totalConvertedBytes: Int64 = 0

    private var exportTask: Task<Void, Never>?

    public init() {}

    public func addURLs(_ urls: [URL]) {
        var newURLs: [URL] = []
        let fileManager = FileManager.default

        for url in urls {
            var isDir: ObjCBool = false
            if fileManager.fileExists(atPath: url.path, isDirectory: &isDir) {
                if isDir.boolValue {
                    if let enumerator = fileManager.enumerator(at: url, includingPropertiesForKeys: [.isRegularFileKey], options: [.skipsHiddenFiles, .skipsPackageDescendants]) {
                        for case let fileURL as URL in enumerator {
                            if isSupportedURL(fileURL) {
                                newURLs.append(fileURL)
                            }
                        }
                    }
                } else if isSupportedURL(url) {
                    newURLs.append(url)
                }
            }
        }

        let existingPaths = Set(items.map { $0.url.path })
        for url in newURLs where !existingPaths.contains(url.path) {
            let item = ImageItem(url: url)
            if item.pixelWidth > 0 && item.pixelHeight > 0 {
                items.append(item)
            }
        }
    }

    private func isSupportedURL(_ url: URL) -> Bool {
        if let type = UTType(filenameExtension: url.pathExtension) {
            return type.conforms(to: .image) || type.conforms(to: .pdf)
        }
        return url.pathExtension.lowercased() == "pdf"
    }

    public func removeItem(id: UUID) {
        objectWillChange.send()
        items.removeAll { $0.id == id }
    }

    public func clearAll() {
        objectWillChange.send()
        items.removeAll()
        lastExportFolderURL = nil
        exportProgress = 0.0
    }

    public func selectCustomDestination() {
        let panel = NSOpenPanel()
        panel.canChooseFiles = false
        panel.canChooseDirectories = true
        panel.allowsMultipleSelection = false
        panel.title = "Select Export Destination Folder"
        if panel.runModal() == .OK, let selectedURL = panel.url {
            objectWillChange.send()
            config.destinationType = .customFolder
            config.customDestinationPath = selectedURL.path
        }
    }

    /// Map individual item index to projected output base name
    public func projectedBaseName(for index: Int) -> String {
        switch config.namingMode {
        case .original:
            guard index < items.count else { return "image-\(index + 1)" }
            let item = items[index]
            return (item.fileName as NSString).deletingPathExtension

        case .customSequence:
            let cleanBase = config.customBaseName.trimmingCharacters(in: .whitespacesAndNewlines).isEmpty ? "image" : config.customBaseName.trimmingCharacters(in: .whitespacesAndNewlines)
            let seqNum = config.startSequenceIndex + index
            let paddedNum = String(format: "%0\(config.numberPadding)d", seqNum)
            return "\(cleanBase)_\(paddedNum)"

        case .seoKeywords:
            let keywords = config.sanitizedSEOKeywords()
            if keywords.isEmpty {
                return "seo-image-\(index + 1)"
            }
            let kIndex = index % keywords.count
            let repeatCount = index / keywords.count
            let base = keywords[kIndex]
            if repeatCount > 0 {
                return "\(base)-\(repeatCount + 1)"
            }
            return base
        }
    }

    public func exportAll() {
        guard !items.isEmpty else { return }
        guard !isExporting else { return }

        isExporting = true
        exportProgress = 0.0
        totalToProcess = items.count
        currentProcessingIndex = 0
        totalOriginalBytes = items.reduce(0) { $0 + $1.originalFileSize }
        totalConvertedBytes = 0

        for i in 0..<items.count {
            items[i].status = .pending
        }

        let fileManager = FileManager.default
        let targetFolderURL: URL

        if config.destinationType == .customFolder && !config.customDestinationPath.isEmpty {
            targetFolderURL = URL(fileURLWithPath: config.customDestinationPath)
        } else {
            let desktopURL = fileManager.urls(for: .desktopDirectory, in: .userDomainMask).first ?? URL(fileURLWithPath: NSHomeDirectory() + "/Desktop")
            let folderName: String
            if !config.customFolderName.trimmingCharacters(in: .whitespacesAndNewlines).isEmpty {
                folderName = config.customFolderName.trimmingCharacters(in: .whitespacesAndNewlines)
            } else {
                let dateFormatter = DateFormatter()
                dateFormatter.dateFormat = "yyyy-MM-dd_HHmmss"
                folderName = "Converted_Images_\(dateFormatter.string(from: Date()))"
            }
            targetFolderURL = desktopURL.appendingPathComponent(folderName, isDirectory: true)
            do {
                try fileManager.createDirectory(at: targetFolderURL, withIntermediateDirectories: true, attributes: nil)
            } catch {
                self.errorMessage = "Failed to create destination folder on Desktop: \(error.localizedDescription)"
                self.isExporting = false
                return
            }
        }

        self.lastExportFolderURL = targetFolderURL
        let currentConfig = self.config
        let seoList = currentConfig.sanitizedSEOKeywords()

        exportTask = Task { [items = self.items] in
            var usedFileNames = Set<String>()
            var convertedBytesAccum: Int64 = 0

            for (index, item) in items.enumerated() {
                if Task.isCancelled { break }

                self.currentProcessingIndex = index + 1
                self.currentProcessingName = item.fileName
                self.items[index].status = .processing

                // Base filename calculation
                let baseName: String
                switch currentConfig.namingMode {
                case .original:
                    baseName = (item.fileName as NSString).deletingPathExtension
                case .customSequence:
                    let cleanBase = currentConfig.customBaseName.trimmingCharacters(in: .whitespacesAndNewlines).isEmpty ? "image" : currentConfig.customBaseName.trimmingCharacters(in: .whitespacesAndNewlines)
                    let seqNum = currentConfig.startSequenceIndex + index
                    let paddedNum = String(format: "%0\(currentConfig.numberPadding)d", seqNum)
                    baseName = "\(cleanBase)_\(paddedNum)"
                case .seoKeywords:
                    if seoList.isEmpty {
                        baseName = "seo-image-\(index + 1)"
                    } else {
                        let kIdx = index % seoList.count
                        let repeatCount = index / seoList.count
                        if repeatCount > 0 {
                            baseName = "\(seoList[kIdx])-\(repeatCount + 1)"
                        } else {
                            baseName = seoList[kIdx]
                        }
                    }
                }

                // Suffix calculation
                var suffix = ""
                if currentConfig.appendWidthSuffix && currentConfig.sizeOption == .customWidth {
                    suffix = "_\(currentConfig.customWidthValue)w"
                }

                var outName = "\(baseName)\(suffix).\(currentConfig.format.fileExtension)"
                var counter = 1
                while usedFileNames.contains(outName.lowercased()) {
                    outName = "\(baseName)\(suffix)_\(counter).\(currentConfig.format.fileExtension)"
                    counter += 1
                }
                usedFileNames.insert(outName.lowercased())

                let destFileURL = targetFolderURL.appendingPathComponent(outName)

                let itemURL = item.url
                let processResult: Result<Int64, Error> = await Task.detached(priority: .userInitiated) {
                    do {
                        try ImageProcessor.processImage(from: itemURL, to: destFileURL, config: currentConfig)
                        let outAttrs = try? FileManager.default.attributesOfItem(atPath: destFileURL.path)
                        let outSize = (outAttrs?[.size] as? Int64) ?? 0
                        return .success(outSize)
                    } catch {
                        return .failure(error)
                    }
                }.value

                switch processResult {
                case .success(let outSize):
                    convertedBytesAccum += outSize
                    self.items[index].status = .completed(outputURL: destFileURL)
                    self.totalConvertedBytes = convertedBytesAccum
                    self.exportProgress = Double(index + 1) / Double(items.count)
                case .failure(let error):
                    self.items[index].status = .failed(error: error.localizedDescription)
                    self.exportProgress = Double(index + 1) / Double(items.count)
                }
            }

            self.isExporting = false
            self.showSuccessSheet = true
            NSSound(named: "Glass")?.play()
        }
    }

    public func cancelExport() {
        exportTask?.cancel()
        exportTask = nil
        isExporting = false
    }

    public func revealInFinder() {
        guard let folderURL = lastExportFolderURL else { return }
        NSWorkspace.shared.selectFile(nil, inFileViewerRootedAtPath: folderURL.path)
    }
}
