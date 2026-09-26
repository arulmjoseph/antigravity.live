import SwiftUI
import UniformTypeIdentifiers
import AppKit

struct DropZoneView: View {
    @ObservedObject var manager: ExportManager
    @State private var isTargeted: Bool = false

    var body: some View {
        ZStack {
            RoundedRectangle(cornerRadius: 16, style: .continuous)
                .fill(isTargeted ? Color.accentColor.opacity(0.12) : Color(nsColor: .controlBackgroundColor).opacity(0.5))
                .overlay(
                    RoundedRectangle(cornerRadius: 16, style: .continuous)
                        .strokeBorder(
                            isTargeted ? Color.accentColor : Color.secondary.opacity(0.3),
                            style: StrokeStyle(lineWidth: isTargeted ? 2.5 : 1.5, dash: [8, 6])
                        )
                )
                .animation(.easeInOut(duration: 0.2), value: isTargeted)

            VStack(spacing: 16) {
                ZStack {
                    Circle()
                        .fill(Color.accentColor.opacity(0.15))
                        .frame(width: 72, height: 72)
                    
                    Image(systemName: isTargeted ? "arrow.down.doc.fill" : "photo.stack.fill")
                        .font(.system(size: 32, weight: .semibold))
                        .foregroundColor(.accentColor)
                        .scaleEffect(isTargeted ? 1.15 : 1.0)
                        .animation(.spring(response: 0.3, dampingFraction: 0.6), value: isTargeted)
                }

                VStack(spacing: 6) {
                    Text("Drag & Drop Images & PDFs Here")
                        .font(.title3.weight(.bold))
                        .foregroundColor(.primary)

                    Text("Drop single or bulk files, PDFs, or entire folders")
                        .font(.subheadline)
                        .foregroundColor(.secondary)
                }

                HStack(spacing: 12) {
                    Button(action: openFilePicker) {
                        Label("Select Files", systemImage: "doc.badge.plus")
                            .font(.body.weight(.medium))
                            .padding(.horizontal, 4)
                    }
                    .buttonStyle(.borderedProminent)
                    .controlSize(.regular)

                    Button(action: openFolderPicker) {
                        Label("Select Folder", systemImage: "folder.badge.plus")
                            .font(.body.weight(.medium))
                    }
                    .buttonStyle(.bordered)
                    .controlSize(.regular)
                }

                HStack(spacing: 6) {
                    Image(systemName: "checkmark.circle.fill")
                        .foregroundColor(.green)
                        .font(.caption2)
                    Text("Supports PNG, JPG, WEBP, PDF, HEIC, TIFF, BMP, GIF & RAW")
                        .font(.caption)
                        .foregroundColor(.secondary)
                }
                .padding(.top, 4)

                HStack(spacing: 4) {
                    Text("Created by")
                        .font(.caption2)
                        .foregroundColor(.secondary.opacity(0.8))
                    Link("arulmjoseph.com", destination: URL(string: "https://arulmjoseph.com")!)
                        .font(.caption2.weight(.medium))
                        .foregroundColor(.accentColor)
                }
                .padding(.top, 2)
            }
            .padding(32)
        }
        .onDrop(of: [.fileURL], isTargeted: $isTargeted) { providers in
            loadProviders(providers)
            return true
        }
    }

    private func openFilePicker() {
        let panel = NSOpenPanel()
        panel.allowsMultipleSelection = true
        panel.canChooseDirectories = false
        panel.canChooseFiles = true
        panel.allowedContentTypes = [.image, .pdf]
        panel.title = "Select Images or PDFs to Convert"

        if panel.runModal() == .OK {
            manager.addURLs(panel.urls)
        }
    }

    private func openFolderPicker() {
        let panel = NSOpenPanel()
        panel.allowsMultipleSelection = true
        panel.canChooseDirectories = true
        panel.canChooseFiles = false
        panel.title = "Select Folder Containing Images"

        if panel.runModal() == .OK {
            manager.addURLs(panel.urls)
        }
    }

    private func loadProviders(_ providers: [NSItemProvider]) {
        for provider in providers {
            _ = provider.loadObject(ofClass: URL.self) { url, _ in
                guard let url = url else { return }
                DispatchQueue.main.async {
                    self.manager.addURLs([url])
                }
            }
        }
    }
}
