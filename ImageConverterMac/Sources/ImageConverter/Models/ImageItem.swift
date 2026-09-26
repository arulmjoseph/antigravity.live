import Foundation
import AppKit
import UniformTypeIdentifiers
import PDFKit

public struct ImageItem: Identifiable, Equatable {
    public let id: UUID
    public let url: URL
    public let fileName: String
    public let originalFileSize: Int64
    public var pixelWidth: Int
    public var pixelHeight: Int
    public var thumbnail: NSImage?
    public var status: ConversionStatus = .pending

    public enum ConversionStatus: Equatable {
        case pending
        case processing
        case completed(outputURL: URL)
        case failed(error: String)
    }

    public init(url: URL) {
        self.id = UUID()
        self.url = url
        self.fileName = url.lastPathComponent
        
        let fileAttributes = try? FileManager.default.attributesOfItem(atPath: url.path)
        self.originalFileSize = (fileAttributes?[.size] as? Int64) ?? 0
        
        // Read image/PDF dimensions and metadata efficiently
        if (url.pathExtension.lowercased() == "pdf" || UTType(filenameExtension: url.pathExtension)?.conforms(to: .pdf) == true),
           let pdfDoc = PDFDocument(url: url), pdfDoc.pageCount > 0, let page = pdfDoc.page(at: 0) {
            let bounds = page.bounds(for: .mediaBox)
            self.pixelWidth = Int(bounds.width)
            self.pixelHeight = Int(bounds.height)
            self.thumbnail = page.thumbnail(of: NSSize(width: 60, height: 60), for: .mediaBox)
        } else if let imageSource = CGImageSourceCreateWithURL(url as CFURL, nil),
           let properties = CGImageSourceCopyPropertiesAtIndex(imageSource, 0, nil) as? [CFString: Any] {
            self.pixelWidth = properties[kCGImagePropertyPixelWidth] as? Int ?? 0
            self.pixelHeight = properties[kCGImagePropertyPixelHeight] as? Int ?? 0
            
            // Generate low-res thumbnail quickly
            let thumbOptions: [CFString: Any] = [
                kCGImageSourceCreateThumbnailFromImageAlways: true,
                kCGImageSourceShouldCacheImmediately: true,
                kCGImageSourceCreateThumbnailWithTransform: true,
                kCGImageSourceThumbnailMaxPixelSize: 120
            ]
            if let thumbCG = CGImageSourceCreateThumbnailAtIndex(imageSource, 0, thumbOptions as CFDictionary) {
                self.thumbnail = NSImage(cgImage: thumbCG, size: NSSize(width: 60, height: 60))
            }
        } else {
            self.pixelWidth = 0
            self.pixelHeight = 0
            self.thumbnail = nil
        }
    }

    public var formattedSize: String {
        ByteCountFormatter.string(fromByteCount: originalFileSize, countStyle: .file)
    }

    public var dimensionsString: String {
        if pixelWidth > 0 && pixelHeight > 0 {
            return "\(pixelWidth) × \(pixelHeight) px"
        }
        return "Unknown size"
    }

    public static func == (lhs: ImageItem, rhs: ImageItem) -> Bool {
        lhs.id == rhs.id && lhs.status == rhs.status
    }
}
