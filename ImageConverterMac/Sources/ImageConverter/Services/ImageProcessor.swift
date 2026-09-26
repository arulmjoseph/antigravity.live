import Foundation
import AppKit
import CoreGraphics
import ImageIO
import UniformTypeIdentifiers
import PDFKit

public struct ImageProcessor {

    private static func findCWebpExecutable() -> String? {
        if let resourcePath = Bundle.main.resourcePath {
            let bundleCwebp = (resourcePath as NSString).appendingPathComponent("cwebp")
            if FileManager.default.isExecutableFile(atPath: bundleCwebp) {
                return bundleCwebp
            }
        }
        let candidatePaths = [
            "/opt/homebrew/bin/cwebp",
            "/usr/local/bin/cwebp",
            "/usr/bin/cwebp"
        ]
        for path in candidatePaths {
            if FileManager.default.isExecutableFile(atPath: path) {
                return path
            }
        }
        return nil
    }

    public static func processImage(from sourceURL: URL, to destinationURL: URL, config: ExportConfig) throws {
        let isPDFSource = sourceURL.pathExtension.lowercased() == "pdf" || UTType(filenameExtension: sourceURL.pathExtension)?.conforms(to: .pdf) == true

        if isPDFSource {
            try processPDFSource(from: sourceURL, to: destinationURL, config: config)
        } else {
            try processImageSource(from: sourceURL, to: destinationURL, config: config)
        }
    }

    private static func processPDFSource(from sourceURL: URL, to destinationURL: URL, config: ExportConfig) throws {
        guard let pdfDoc = PDFDocument(url: sourceURL), pdfDoc.pageCount > 0 else {
            throw NSError(domain: "ImageProcessor", code: 1, userInfo: [NSLocalizedDescriptionKey: "Failed to open PDF document: \(sourceURL.lastPathComponent)"])
        }

        if config.format == .pdf {
            // PDF -> PDF Optimization
            var pageImages: [CGImage] = []
            for i in 0..<pdfDoc.pageCount {
                guard let page = pdfDoc.page(at: i) else { continue }
                let bounds = page.bounds(for: .mediaBox)
                let (targetWidth, targetHeight) = config.calculateDimensions(originalWidth: Int(bounds.width), originalHeight: Int(bounds.height))
                if let cgImg = renderPDFPageToCGImage(page, targetWidth: targetWidth, targetHeight: targetHeight) {
                    pageImages.append(cgImg)
                }
            }
            guard !pageImages.isEmpty else {
                throw NSError(domain: "ImageProcessor", code: 2, userInfo: [NSLocalizedDescriptionKey: "Failed to render pages from PDF document."])
            }
            try createPDF(from: pageImages, destinationURL: destinationURL, config: config)
        } else {
            // PDF -> Image Conversion (Page 1)
            guard let firstPage = pdfDoc.page(at: 0) else {
                throw NSError(domain: "ImageProcessor", code: 3, userInfo: [NSLocalizedDescriptionKey: "PDF document contains no valid pages."])
            }
            let bounds = firstPage.bounds(for: .mediaBox)
            let (targetWidth, targetHeight) = config.calculateDimensions(originalWidth: Int(bounds.width), originalHeight: Int(bounds.height))
            guard let pageCGImage = renderPDFPageToCGImage(firstPage, targetWidth: targetWidth, targetHeight: targetHeight) else {
                throw NSError(domain: "ImageProcessor", code: 4, userInfo: [NSLocalizedDescriptionKey: "Failed to render PDF page into image bitmap."])
            }
            
            try encodeCGImage(pageCGImage, to: destinationURL, config: config, width: targetWidth, height: targetHeight)
        }
    }

    private static func processImageSource(from sourceURL: URL, to destinationURL: URL, config: ExportConfig) throws {
        guard let source = CGImageSourceCreateWithURL(sourceURL as CFURL, nil) else {
            throw NSError(domain: "ImageProcessor", code: 5, userInfo: [NSLocalizedDescriptionKey: "Failed to read source image: \(sourceURL.lastPathComponent)"])
        }

        guard let properties = CGImageSourceCopyPropertiesAtIndex(source, 0, nil) as? [CFString: Any] else {
            throw NSError(domain: "ImageProcessor", code: 6, userInfo: [NSLocalizedDescriptionKey: "Failed to read image metadata."])
        }

        let origWidth = properties[kCGImagePropertyPixelWidth] as? Int ?? 0
        let origHeight = properties[kCGImagePropertyPixelHeight] as? Int ?? 0

        guard origWidth > 0 && origHeight > 0 else {
            throw NSError(domain: "ImageProcessor", code: 7, userInfo: [NSLocalizedDescriptionKey: "Invalid image dimensions."])
        }

        let (targetWidth, targetHeight) = config.calculateDimensions(originalWidth: origWidth, originalHeight: origHeight)

        var image: CGImage?
        let maxDim = max(targetWidth, targetHeight)
        
        let thumbOptions: [CFString: Any] = [
            kCGImageSourceCreateThumbnailFromImageAlways: true,
            kCGImageSourceShouldCacheImmediately: true,
            kCGImageSourceCreateThumbnailWithTransform: true,
            kCGImageSourceThumbnailMaxPixelSize: maxDim
        ]

        if config.sizeOption != .original {
            image = CGImageSourceCreateThumbnailAtIndex(source, 0, thumbOptions as CFDictionary)
        }
        
        if image == nil {
            let options: [CFString: Any] = [
                kCGImageSourceShouldCacheImmediately: true
            ]
            image = CGImageSourceCreateImageAtIndex(source, 0, options as CFDictionary)
        }

        guard let sourceCG = image else {
            throw NSError(domain: "ImageProcessor", code: 8, userInfo: [NSLocalizedDescriptionKey: "Failed to render bitmap from source."])
        }

        let finalCGImage: CGImage
        if sourceCG.width != targetWidth || sourceCG.height != targetHeight {
            let colorSpace = sourceCG.colorSpace ?? CGColorSpace(name: CGColorSpace.sRGB)!
            let bitmapInfo: UInt32 = (config.format == .jpeg || config.format == .bmp || config.format == .pdf) 
                ? CGImageAlphaInfo.noneSkipLast.rawValue 
                : CGImageAlphaInfo.premultipliedLast.rawValue

            guard let context = CGContext(
                data: nil,
                width: targetWidth,
                height: targetHeight,
                bitsPerComponent: 8,
                bytesPerRow: 0,
                space: colorSpace,
                bitmapInfo: bitmapInfo
            ) else {
                throw NSError(domain: "ImageProcessor", code: 9, userInfo: [NSLocalizedDescriptionKey: "Failed to allocate drawing context for resizing."])
            }

            if config.format == .jpeg || config.format == .bmp || config.format == .pdf {
                context.setFillColor(CGColor(srgbRed: 1, green: 1, blue: 1, alpha: 1))
                context.fill(CGRect(x: 0, y: 0, width: targetWidth, height: targetHeight))
            }

            context.interpolationQuality = .high
            context.draw(sourceCG, in: CGRect(x: 0, y: 0, width: targetWidth, height: targetHeight))

            guard let rendered = context.makeImage() else {
                throw NSError(domain: "ImageProcessor", code: 10, userInfo: [NSLocalizedDescriptionKey: "Failed to generate resized image."])
            }
            finalCGImage = rendered
        } else {
            finalCGImage = sourceCG
        }

        if config.format == .pdf {
            try createPDF(from: [finalCGImage], destinationURL: destinationURL, config: config)
        } else {
            try encodeCGImage(finalCGImage, to: destinationURL, config: config, width: targetWidth, height: targetHeight)
        }
    }

    private static func encodeCGImage(_ cgImage: CGImage, to destinationURL: URL, config: ExportConfig, width: Int, height: Int) throws {
        if config.format == .webp {
            try exportWebP(cgImage: cgImage, destinationURL: destinationURL, quality: config.jpegQuality, optimizeForWeb: config.optimizeForWeb)
            return
        }

        guard let destination = CGImageDestinationCreateWithURL(
            destinationURL as CFURL,
            config.format.utType.identifier as CFString,
            1,
            nil
        ) else {
            throw NSError(domain: "ImageProcessor", code: 11, userInfo: [NSLocalizedDescriptionKey: "Unsupported format encoding: \(config.format.rawValue)"])
        }

        var destinationProperties: [CFString: Any] = [:]
        if config.format.supportsQuality {
            destinationProperties[kCGImageDestinationLossyCompressionQuality] = config.jpegQuality
        }
        
        if config.optimizeForWeb {
            destinationProperties[kCGImageDestinationDateTime] = nil
        }

        CGImageDestinationAddImage(destination, cgImage, destinationProperties as CFDictionary)

        if !CGImageDestinationFinalize(destination) {
            throw NSError(domain: "ImageProcessor", code: 12, userInfo: [NSLocalizedDescriptionKey: "Failed to save encoded image to \(destinationURL.lastPathComponent)."])
        }
    }

    private static func createPDF(from images: [CGImage], destinationURL: URL, config: ExportConfig) throws {
        guard !images.isEmpty else {
            throw NSError(domain: "ImageProcessor", code: 13, userInfo: [NSLocalizedDescriptionKey: "No images provided for PDF generation."])
        }

        let pdfData = NSMutableData()
        guard let consumer = CGDataConsumer(data: pdfData as CFMutableData) else {
            throw NSError(domain: "ImageProcessor", code: 14, userInfo: [NSLocalizedDescriptionKey: "Failed to create PDF data consumer."])
        }

        var firstBox = CGRect(x: 0, y: 0, width: CGFloat(images[0].width), height: CGFloat(images[0].height))
        
        var pdfInfo: [CFString: Any] = [:]
        if !config.optimizeForWeb {
            pdfInfo[kCGPDFContextCreator] = "Image Converter (arulmjoseph.com)" as CFString
        }

        guard let pdfContext = CGContext(consumer: consumer, mediaBox: &firstBox, pdfInfo as CFDictionary) else {
            throw NSError(domain: "ImageProcessor", code: 15, userInfo: [NSLocalizedDescriptionKey: "Failed to create PDF graphics context."])
        }

        for cgImage in images {
            var pageBox = CGRect(x: 0, y: 0, width: CGFloat(cgImage.width), height: CGFloat(cgImage.height))
            pdfContext.beginPage(mediaBox: &pageBox)
            
            let compressedCGImage = compressImageToJPEG(cgImage, quality: config.jpegQuality) ?? cgImage
            pdfContext.draw(compressedCGImage, in: pageBox)
            pdfContext.endPage()
        }

        pdfContext.closePDF()
        try pdfData.write(to: destinationURL, options: .atomic)
    }

    private static func compressImageToJPEG(_ cgImage: CGImage, quality: Double) -> CGImage? {
        let data = NSMutableData()
        guard let dest = CGImageDestinationCreateWithData(data as CFMutableData, UTType.jpeg.identifier as CFString, 1, nil) else {
            return nil
        }
        let options: [CFString: Any] = [
            kCGImageDestinationLossyCompressionQuality: quality
        ]
        CGImageDestinationAddImage(dest, cgImage, options as CFDictionary)
        guard CGImageDestinationFinalize(dest) else { return nil }
        
        guard let source = CGImageSourceCreateWithData(data as CFData, nil) else { return nil }
        return CGImageSourceCreateImageAtIndex(source, 0, nil)
    }

    private static func renderPDFPageToCGImage(_ page: PDFPage, targetWidth: Int, targetHeight: Int) -> CGImage? {
        let bounds = page.bounds(for: .mediaBox)
        guard bounds.width > 0 && bounds.height > 0 else { return nil }

        let w = targetWidth > 0 ? targetWidth : Int(bounds.width * 2.0)
        let h = targetHeight > 0 ? targetHeight : Int(bounds.height * 2.0)

        let colorSpace = CGColorSpace(name: CGColorSpace.sRGB)!
        guard let context = CGContext(
            data: nil,
            width: w,
            height: h,
            bitsPerComponent: 8,
            bytesPerRow: 0,
            space: colorSpace,
            bitmapInfo: CGImageAlphaInfo.noneSkipLast.rawValue
        ) else {
            return nil
        }

        context.setFillColor(CGColor(srgbRed: 1, green: 1, blue: 1, alpha: 1))
        context.fill(CGRect(x: 0, y: 0, width: CGFloat(w), height: CGFloat(h)))

        let scaleX = CGFloat(w) / bounds.width
        let scaleY = CGFloat(h) / bounds.height
        context.saveGState()
        context.scaleBy(x: scaleX, y: scaleY)
        page.draw(with: .mediaBox, to: context)
        context.restoreGState()

        return context.makeImage()
    }

    private static func exportWebP(cgImage: CGImage, destinationURL: URL, quality: Double, optimizeForWeb: Bool) throws {
        let tempPNGURL = FileManager.default.temporaryDirectory.appendingPathComponent(UUID().uuidString + ".png")
        defer { try? FileManager.default.removeItem(at: tempPNGURL) }

        guard let dest = CGImageDestinationCreateWithURL(tempPNGURL as CFURL, UTType.png.identifier as CFString, 1, nil) else {
            throw NSError(domain: "ImageProcessor", code: 16, userInfo: [NSLocalizedDescriptionKey: "Failed to create temp buffer for WebP conversion."])
        }
        CGImageDestinationAddImage(dest, cgImage, nil)
        guard CGImageDestinationFinalize(dest) else {
            throw NSError(domain: "ImageProcessor", code: 17, userInfo: [NSLocalizedDescriptionKey: "Failed to write temp PNG for WebP."])
        }

        guard let cwebpPath = findCWebpExecutable() else {
            throw NSError(domain: "ImageProcessor", code: 18, userInfo: [NSLocalizedDescriptionKey: "WebP converter tool (cwebp) not found on system."])
        }

        let qInt = max(1, min(100, Int((quality * 100).rounded())))
        var arguments = ["-q", "\(qInt)"]
        if optimizeForWeb {
            arguments.append(contentsOf: ["-metadata", "none", "-m", "6"])
        }
        arguments.append(contentsOf: [tempPNGURL.path, "-o", destinationURL.path])

        let process = Process()
        process.executableURL = URL(fileURLWithPath: cwebpPath)
        process.arguments = arguments
        
        let pipe = Pipe()
        process.standardError = pipe
        process.standardOutput = pipe

        try process.run()
        process.waitUntilExit()

        if process.terminationStatus != 0 {
            let errorData = pipe.fileHandleForReading.readDataToEndOfFile()
            let errorStr = String(data: errorData, encoding: .utf8) ?? "cwebp exited with error code \(process.terminationStatus)"
            throw NSError(domain: "ImageProcessor", code: 19, userInfo: [NSLocalizedDescriptionKey: "WebP encoding failed: \(errorStr)"])
        }
    }
}
