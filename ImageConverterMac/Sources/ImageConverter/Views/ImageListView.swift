import SwiftUI

struct ImageListView: View {
    @ObservedObject var manager: ExportManager

    var body: some View {
        VStack(spacing: 0) {
            // Header Bar
            HStack {
                Text("\(manager.items.count) \(manager.items.count == 1 ? "Image" : "Images")")
                    .font(.headline)
                    .foregroundColor(.primary)

                Spacer()

                Button(action: openFilePicker) {
                    Label("Add More", systemImage: "plus")
                }
                .buttonStyle(.bordered)
                .controlSize(.small)

                Button(action: { manager.clearAll() }) {
                    Label("Clear All", systemImage: "trash")
                }
                .buttonStyle(.bordered)
                .controlSize(.small)
            }
            .padding(.horizontal, 16)
            .padding(.vertical, 10)
            .background(Color(nsColor: .windowBackgroundColor))

            Divider()

            // List of items
            ScrollView {
                LazyVStack(spacing: 8) {
                    ForEach(Array(manager.items.enumerated()), id: \.element.id) { index, item in
                        ImageRowView(item: item, index: index, manager: manager) {
                            manager.removeItem(id: item.id)
                        }
                    }
                }
                .padding(12)
            }
        }
    }

    private func openFilePicker() {
        let panel = NSOpenPanel()
        panel.allowsMultipleSelection = true
        panel.canChooseDirectories = true
        panel.canChooseFiles = true
        panel.allowedContentTypes = [.image, .pdf]
        panel.title = "Add More Files, PDFs or Folders"

        if panel.runModal() == .OK {
            manager.addURLs(panel.urls)
        }
    }
}

struct ImageRowView: View {
    let item: ImageItem
    let index: Int
    @ObservedObject var manager: ExportManager
    let onRemove: () -> Void

    var projectedOutputName: String {
        let baseName = manager.projectedBaseName(for: index)
        var suffix = ""
        if manager.config.appendWidthSuffix && manager.config.sizeOption == .customWidth {
            suffix = "_\(manager.config.customWidthValue)w"
        }
        return "\(baseName)\(suffix).\(manager.config.format.fileExtension)"
    }

    var projectedSizeString: String {
        guard item.pixelWidth > 0 && item.pixelHeight > 0 else { return "" }
        let (w, h) = manager.config.calculateDimensions(originalWidth: item.pixelWidth, originalHeight: item.pixelHeight)
        if manager.config.sizeOption == .original || (w == item.pixelWidth && h == item.pixelHeight) {
            return "\(w) × \(h) px"
        }
        return "\(item.pixelWidth) × \(item.pixelHeight) px → \(w) × \(h) px"
    }

    var body: some View {
        HStack(spacing: 12) {
            // Thumbnail
            Group {
                if let thumb = item.thumbnail {
                    Image(nsImage: thumb)
                        .resizable()
                        .aspectRatio(contentMode: .fit)
                } else {
                    Image(systemName: "photo")
                        .font(.title2)
                        .foregroundColor(.secondary)
                }
            }
            .frame(width: 44, height: 44)
            .background(Color(nsColor: .controlBackgroundColor))
            .cornerRadius(6)
            .overlay(RoundedRectangle(cornerRadius: 6).stroke(Color.secondary.opacity(0.2), lineWidth: 1))

            // File Info
            VStack(alignment: .leading, spacing: 3) {
                HStack(spacing: 6) {
                    Text(item.fileName)
                        .font(.body.weight(.medium))
                        .lineLimit(1)
                        .truncationMode(.middle)

                    Image(systemName: "arrow.right")
                        .font(.caption2)
                        .foregroundColor(.secondary)

                    Text(projectedOutputName)
                        .font(.caption.weight(.semibold))
                        .foregroundColor(.accentColor)
                        .lineLimit(1)
                        .truncationMode(.middle)
                }

                HStack(spacing: 8) {
                    Text(projectedSizeString)
                        .font(.caption)
                        .foregroundColor(.secondary)

                    Text("•")
                        .font(.caption2)
                        .foregroundColor(.secondary.opacity(0.5))

                    Text(item.formattedSize)
                        .font(.caption)
                        .foregroundColor(.secondary)
                }
            }

            Spacer()

            // Status Indicator
            switch item.status {
            case .pending:
                EmptyView()
            case .processing:
                ProgressView()
                    .controlSize(.small)
                    .padding(.trailing, 4)
            case .completed:
                Image(systemName: "checkmark.circle.fill")
                    .foregroundColor(.green)
                    .font(.body)
            case .failed(let error):
                Image(systemName: "exclamationmark.triangle.fill")
                    .foregroundColor(.red)
                    .font(.body)
                    .help(error)
            }

            // Remove Button
            Button(action: onRemove) {
                Image(systemName: "xmark.circle.fill")
                    .font(.body)
                    .foregroundColor(.secondary.opacity(0.6))
            }
            .buttonStyle(.plain)
            .help("Remove from queue")
        }
        .padding(.horizontal, 12)
        .padding(.vertical, 8)
        .background(Color(nsColor: .controlBackgroundColor).opacity(0.7))
        .cornerRadius(8)
    }
}
