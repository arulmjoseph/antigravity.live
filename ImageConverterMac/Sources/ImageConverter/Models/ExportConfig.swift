import Foundation
import UniformTypeIdentifiers

public enum TargetFormat: String, CaseIterable, Identifiable {
    case webp = "WEBP"
    case jpeg = "JPG"
    case png = "PNG"
    case pdf = "PDF"
    case heic = "HEIC"
    case avif = "AVIF"
    case tiff = "TIFF"
    case bmp = "BMP"
    case gif = "GIF"

    public var id: String { rawValue }

    public var displayName: String {
        switch self {
        case .webp: return "WEBP — Best for Website ⭐"
        case .jpeg: return "JPG — Standard Web & Photos"
        case .png:  return "PNG — Lossless & Transparency"
        case .pdf:  return "PDF — Optimized Document & PDF"
        case .heic: return "HEIC — Apple High Efficiency"
        case .avif: return "AVIF — Modern Next-Gen"
        case .tiff: return "TIFF — Uncompressed"
        case .bmp:  return "BMP — Bitmap"
        case .gif:  return "GIF — Graphics"
        }
    }

    public var fileExtension: String {
        switch self {
        case .webp: return "webp"
        case .jpeg: return "jpg"
        case .png:  return "png"
        case .pdf:  return "pdf"
        case .heic: return "heic"
        case .avif: return "avif"
        case .tiff: return "tiff"
        case .bmp:  return "bmp"
        case .gif:  return "gif"
        }
    }

    public var utType: UTType {
        switch self {
        case .webp: return UTType.webP
        case .jpeg: return .jpeg
        case .png:  return .png
        case .pdf:  return .pdf
        case .heic: return .heic
        case .avif: return UTType("public.avif") ?? .jpeg
        case .tiff: return .tiff
        case .bmp:  return .bmp
        case .gif:  return .gif
        }
    }

    public var supportsQuality: Bool {
        self == .jpeg || self == .heic || self == .avif || self == .webp || self == .pdf
    }
}

public enum SizeOption: String, CaseIterable, Identifiable {
    case customWidth = "Custom Width"
    case original = "Original Size"

    public var id: String { rawValue }
}

public enum NamingMode: String, CaseIterable, Identifiable {
    case original = "Original Name"
    case customSequence = "Custom Sequence"
    case seoKeywords = "Bulk SEO Keywords"

    public var id: String { rawValue }
}

public enum ExportDestination: String, CaseIterable, Identifiable {
    case desktopFolder = "New Desktop Folder"
    case customFolder = "Custom Folder..."

    public var id: String { rawValue }
}

public struct ExportConfig {
    public var format: TargetFormat = .webp
    public var sizeOption: SizeOption = .customWidth
    public var customWidthText: String = "500" // User editable custom width
    public var jpegQuality: Double = 0.85
    public var optimizeForWeb: Bool = true // Strip metadata, optimal compression
    
    // Naming options
    public var namingMode: NamingMode = .seoKeywords
    public var customBaseName: String = "image"
    public var startSequenceIndex: Int = 1
    public var numberPadding: Int = 3 // e.g. 001, 002
    
    // SEO Keywords Renamer
    public var seoKeywordsText: String = """
    * CFO financial strategy meeting Middle East
    * corporate governance board meeting Dubai
    * business executives financial planning documents GCC
    * finance consultant reviewing reports with client
    * corporate tax advisory meeting UAE
    * executive financial analysis modern office
    * business restructuring advisory meeting
    * auditor reviewing financial statements office
    """
    public var randomizeSEOKeywords: Bool = true
    
    public var appendWidthSuffix: Bool = false // e.g. photo_500w.webp
    
    public var customFolderName: String = ""
    public var destinationType: ExportDestination = .desktopFolder
    public var customDestinationPath: String = ""

    public var customWidthValue: Int {
        let cleaned = customWidthText.trimmingCharacters(in: .whitespacesAndNewlines)
        return max(1, Int(cleaned) ?? 500)
    }

    public init() {}

    /// Parse and clean raw SEO keyword input lines into clean URL/SEO-friendly dash-separated slugs
    public func sanitizedSEOKeywords() -> [String] {
        let rawLines = seoKeywordsText.components(separatedBy: .newlines)
        var cleanedSlugs: [String] = []

        for line in rawLines {
            var trimmed = line.trimmingCharacters(in: .whitespacesAndNewlines)
            if trimmed.isEmpty { continue }

            // Strip bullet symbols (*, -, •, numbers like 1.)
            while trimmed.hasPrefix("*") || trimmed.hasPrefix("-") || trimmed.hasPrefix("•") || trimmed.hasPrefix("#") {
                trimmed.removeFirst()
                trimmed = trimmed.trimmingCharacters(in: .whitespacesAndNewlines)
            }
            
            // Strip leading digits and dots (e.g. "1. ")
            if let regex = try? NSRegularExpression(pattern: "^\\d+[\\.\\)\\-]?\\s*") {
                let nsString = trimmed as NSString
                let results = regex.matches(in: trimmed, range: NSRange(location: 0, length: nsString.length))
                if let match = results.first {
                    trimmed = nsString.substring(from: match.range.length)
                }
            }

            trimmed = trimmed.trimmingCharacters(in: .whitespacesAndNewlines)
            if trimmed.isEmpty { continue }

            // Replace spaces, underscores, and consecutive hyphens with a single dash
            let slug = trimmed.lowercased()
                .replacingOccurrences(of: "[^a-z0-9\\s\\-]", with: "", options: .regularExpression)
                .replacingOccurrences(of: "[\\s_]+", with: "-", options: .regularExpression)
                .replacingOccurrences(of: "-+", with: "-", options: .regularExpression)
                .trimmingCharacters(in: CharacterSet(charactersIn: "-"))

            if !slug.isEmpty {
                cleanedSlugs.append(slug)
            }
        }

        return cleanedSlugs
    }

    /// Calculate output dimensions for a given source dimension preserving proportionality
    public func calculateDimensions(originalWidth: Int, originalHeight: Int) -> (width: Int, height: Int) {
        guard originalWidth > 0 && originalHeight > 0 else {
            return (originalWidth, originalHeight)
        }

        switch sizeOption {
        case .original:
            return (originalWidth, originalHeight)

        case .customWidth:
            let reqW = Double(customWidthValue)
            let origW = Double(originalWidth)
            let origH = Double(originalHeight)
            let aspectRatio = origW / origH
            let reqH = (reqW / aspectRatio).rounded()
            return (max(1, Int(reqW)), max(1, Int(reqH)))
        }
    }
}
