import Foundation
import AppKit
import CoreGraphics
import Vision
import ImageIO

public struct ImageVisionAnalyzer {

    /// Uses Apple Vision Machine Learning framework to analyze visual content of an image and generate a descriptive SEO slug
    public static func analyzeImageContent(url: URL, userKeywords: [String] = []) -> String {
        guard let source = CGImageSourceCreateWithURL(url as CFURL, nil) else {
            return fallbackSlug(from: url)
        }

        let thumbOptions: [CFString: Any] = [
            kCGImageSourceCreateThumbnailFromImageAlways: true,
            kCGImageSourceShouldCacheImmediately: true,
            kCGImageSourceCreateThumbnailWithTransform: true,
            kCGImageSourceThumbnailMaxPixelSize: 1024
        ]

        guard let cgImage = CGImageSourceCreateThumbnailAtIndex(source, 0, thumbOptions as CFDictionary) else {
            return fallbackSlug(from: url)
        }

        var detectedCategories: [String] = []
        var detectedText: [String] = []

        let handler = VNImageRequestHandler(cgImage: cgImage, options: [:])

        // 1. Classification Request
        let classifyRequest = VNClassifyImageRequest()
        
        // 2. Text Recognition Request
        let textRequest = VNRecognizeTextRequest()
        textRequest.recognitionLevel = .fast

        try? handler.perform([classifyRequest, textRequest])

        if let classifyResults = classifyRequest.results {
            // Filter out overly generic categories and take highest confidence specific identifiers
            let ignored: Set<String> = ["material", "textile", "object", "thing", "indoor", "outdoor", "item"]
            let topMatches = classifyResults
                .filter { $0.confidence > 0.05 }
                .map { $0.identifier.lowercased() }
                .flatMap { $0.components(separatedBy: "_") }
                .filter { !ignored.contains($0) && $0.count > 2 }

            for cat in topMatches {
                if !detectedCategories.contains(cat) {
                    detectedCategories.append(cat)
                }
                if detectedCategories.count >= 4 { break }
            }
        }

        if let textResults = textRequest.results {
            for obs in textResults.prefix(3) {
                if let topCandidate = obs.topCandidates(1).first {
                    let clean = topCandidate.string.lowercased()
                        .replacingOccurrences(of: "[^a-z0-9]", with: "", options: .regularExpression)
                    if clean.count > 3 && !detectedText.contains(clean) {
                        detectedText.append(clean)
                    }
                }
            }
        }

        // If user provided custom SEO keywords, match best fit or blend
        if !userKeywords.isEmpty {
            for keyword in userKeywords {
                let lowerK = keyword.lowercased()
                for cat in detectedCategories {
                    if lowerK.contains(cat) {
                        return sanitize(lowerK)
                    }
                }
            }
        }

        // Combine top detected categories into a clean SEO slug
        var parts = detectedCategories
        if parts.isEmpty {
            parts = detectedText
        }

        if parts.isEmpty {
            return fallbackSlug(from: url)
        }

        return sanitize(parts.prefix(3).joined(separator: "-"))
    }

    private static func fallbackSlug(from url: URL) -> String {
        let base = (url.lastPathComponent as NSString).deletingPathExtension
        let clean = base.lowercased()
            .replacingOccurrences(of: "[^a-z0-9\\s\\-]", with: "", options: .regularExpression)
            .replacingOccurrences(of: "[\\s_]+", with: "-", options: .regularExpression)
            .trimmingCharacters(in: CharacterSet(charactersIn: "-"))
        
        if clean.count > 2 && Int(clean) == nil {
            return clean
        }
        return "photo-scene"
    }

    private static func sanitize(_ text: String) -> String {
        return text.lowercased()
            .replacingOccurrences(of: "[^a-z0-9\\s\\-]", with: "", options: .regularExpression)
            .replacingOccurrences(of: "[\\s_]+", with: "-", options: .regularExpression)
            .replacingOccurrences(of: "-+", with: "-", options: .regularExpression)
            .trimmingCharacters(in: CharacterSet(charactersIn: "-"))
    }
}
