import SwiftUI

struct HelpGuideView: View {
    @Environment(\.dismiss) private var dismiss

    var body: some View {
        VStack(spacing: 0) {
            // Header Bar
            HStack {
                Image(systemName: "questionmark.circle.fill")
                    .font(.title2)
                    .foregroundColor(.accentColor)
                Text("Image Converter Help & Guide")
                    .font(.title3.weight(.bold))
                Spacer()
                Button("Done") { dismiss() }
                    .buttonStyle(.borderedProminent)
            }
            .padding(16)
            .background(Color(nsColor: .windowBackgroundColor))

            Divider()

            // Main Content Area
            ScrollView {
                VStack(alignment: .leading, spacing: 20) {
                    
                    // 1. Quick Start Guide
                    HelpSectionCard(
                        title: "1. Quick Start & Drag-and-Drop",
                        icon: "square.and.arrow.down.fill"
                    ) {
                        VStack(alignment: .leading, spacing: 8) {
                            Text("Drag and drop single images, bulk images, or entire folders into the app window.")
                                .font(.body)
                                .foregroundColor(.secondary)

                            HStack(spacing: 12) {
                                Label("Drop files anywhere", systemImage: "arrow.down.doc.fill")
                                    .font(.caption.weight(.medium))
                                Label("Add folders recursively", systemImage: "folder.fill")
                                    .font(.caption.weight(.medium))
                            }
                            .foregroundColor(.accentColor)
                        }
                    }

                    // 2. Format Selection & WebP
                    HelpSectionCard(
                        title: "2. Format Selection & Web Optimizer",
                        icon: "photo.on.rectangle.angled"
                    ) {
                        VStack(alignment: .leading, spacing: 8) {
                            Text("Choose your export format from the Target Format dropdown:")
                                .font(.body)
                                .foregroundColor(.secondary)

                             VStack(alignment: .leading, spacing: 4) {
                                BulletPoint(title: "WEBP (Recommended ⭐)", detail: "Superior compression for fast website loading.")
                                BulletPoint(title: "PDF Document 📄", detail: "Optimizes existing PDFs & converts images into compact PDF files.")
                                BulletPoint(title: "JPG / JPEG", detail: "Standard web and photo format.")
                                BulletPoint(title: "PNG", detail: "Lossless encoding with full transparency support.")
                                BulletPoint(title: "HEIC / AVIF / TIFF / BMP", detail: "Next-gen and uncompressed formats.")
                            }

                            Text("💡 PDF Optimization: Drag PDF files and export as PDF to downsample embedded images, compress PDF file size, and strip document metadata.")
                                .font(.caption)
                                .foregroundColor(.secondary)
                                .padding(8)
                                .background(Color(nsColor: .controlBackgroundColor))
                                .cornerRadius(6)
                        }
                    }

                    // 3. Custom Width & Aspect Ratio
                    HelpSectionCard(
                        title: "3. Width Resizing & Proportional Height",
                        icon: "aspectratio.fill"
                    ) {
                        VStack(alignment: .leading, spacing: 8) {
                            Text("Enter any pixel width (e.g. 200, 500, 1200) under 'Custom Width'.")
                                .font(.body)
                                .foregroundColor(.secondary)

                            Text("The app automatically computes proportional height for each image based on its aspect ratio so images never stretch or distort:")
                                .font(.caption)
                                .foregroundColor(.secondary)

                            Text("Target Height = Original Height × (Target Width / Original Width)")
                                .font(.system(size: 11, design: .monospaced))
                                .padding(8)
                                .background(Color(nsColor: .controlBackgroundColor))
                                .cornerRadius(6)
                        }
                    }

                    // 4. File Renaming & Bulk SEO Keywords
                    HelpSectionCard(
                        title: "4. File Renaming & Bulk SEO Keywords",
                        icon: "tag.fill"
                    ) {
                        VStack(alignment: .leading, spacing: 10) {
                            Text("Choose between three naming modes:")
                                .font(.body)
                                .foregroundColor(.secondary)

                            VStack(alignment: .leading, spacing: 6) {
                                BulletPoint(title: "Original Name", detail: "Preserves input file names.")
                                BulletPoint(title: "Custom Sequence", detail: "Enter base name 'test1' to output test1_001, test1_002.")
                                BulletPoint(title: "Bulk SEO Keywords", detail: "Paste raw keyword lists with spaces or bullets.")
                            }

                            Text("Example Bulk SEO Input:")
                                .font(.caption.weight(.bold))

                            Text("""
                            * CFO financial strategy meeting Middle East
                            * corporate governance board meeting Dubai
                            * business executives financial planning documents GCC
                            """)
                            .font(.system(size: 11, design: .monospaced))
                            .padding(8)
                            .background(Color(nsColor: .controlBackgroundColor))
                            .cornerRadius(6)

                            Text("Auto-converts to SEO slugs: cfo-financial-strategy-meeting-middle-east.webp")
                                .font(.caption.weight(.semibold))
                                .foregroundColor(.green)
                        }
                    }

                    // 5. Desktop Export & Creator Credits
                    HelpSectionCard(
                        title: "5. Automatic Desktop Export & Credits",
                        icon: "folder.badge.gearshape"
                    ) {
                        VStack(alignment: .leading, spacing: 8) {
                            Text("Converted images save automatically into a new timestamped folder on your Desktop or custom selected folder.")
                                .font(.body)
                                .foregroundColor(.secondary)

                            HStack(spacing: 4) {
                                Text("Created by")
                                    .font(.caption)
                                    .foregroundColor(.secondary)
                                Link("arulmjoseph.com", destination: URL(string: "https://arulmjoseph.com")!)
                                    .font(.caption.weight(.semibold))
                                    .foregroundColor(.accentColor)
                            }
                        }
                    }
                }
                .padding(20)
            }
        }
        .frame(width: 580, height: 500)
    }
}

struct HelpSectionCard<Content: View>: View {
    let title: String
    let icon: String
    @ViewBuilder let content: () -> Content

    var body: some View {
        VStack(alignment: .leading, spacing: 10) {
            HStack(spacing: 8) {
                Image(systemName: icon)
                    .foregroundColor(.accentColor)
                    .font(.headline)
                Text(title)
                    .font(.headline)
                    .foregroundColor(.primary)
            }

            content()
        }
        .padding(14)
        .frame(maxWidth: .infinity, alignment: .leading)
        .background(Color(nsColor: .controlBackgroundColor).opacity(0.6))
        .cornerRadius(10)
        .overlay(RoundedRectangle(cornerRadius: 10).stroke(Color.secondary.opacity(0.15), lineWidth: 1))
    }
}

struct BulletPoint: View {
    let title: String
    let detail: String

    var body: some View {
        HStack(alignment: .top, spacing: 6) {
            Text("•")
                .foregroundColor(.accentColor)
                .font(.body.weight(.bold))
            (Text(title + " ").font(.caption.weight(.bold)) + Text(detail).font(.caption).foregroundColor(.secondary))
        }
    }
}
