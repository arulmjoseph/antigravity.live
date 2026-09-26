import SwiftUI
import UniformTypeIdentifiers
import AppKit

struct ContentView: View {
    @StateObject private var manager = ExportManager()
    @State private var isWindowDropTargeted: Bool = false
    @State private var settingsWidth: CGFloat = 430 // Wider, non-congested default width
    @State private var showHelpGuide: Bool = false

    var body: some View {
        VStack(spacing: 0) {
            // App Top Toolbar / Navigation
            HStack {
                HStack(spacing: 8) {
                    Image(systemName: "photo.stack.fill")
                        .font(.title3)
                        .foregroundColor(.accentColor)
                    Text("Image Converter & Resizer")
                        .font(.headline.weight(.bold))
                        .foregroundColor(.primary)
                }

                Spacer()

                Button(action: { showHelpGuide = true }) {
                    Label("Help & Guide", systemImage: "questionmark.circle")
                        .font(.subheadline.weight(.medium))
                }
                .buttonStyle(.bordered)
                .controlSize(.regular)
            }
            .padding(.horizontal, 16)
            .padding(.vertical, 8)
            .background(Color(nsColor: .windowBackgroundColor))

            Divider()

            HStack(spacing: 0) {
                // Main Content Area (DropZone or List)
                Group {
                    if manager.items.isEmpty {
                        DropZoneView(manager: manager)
                            .padding(20)
                    } else {
                        ImageListView(manager: manager)
                    }
                }
                .frame(minWidth: 420, maxWidth: .infinity, maxHeight: .infinity)

                // Resizable Divider
                ResizableDivider(settingsWidth: $settingsWidth, minWidth: 360, maxWidth: 650)

                // Settings Sidebar (Spacious Width)
                SettingsBarView(manager: manager)
                    .frame(width: settingsWidth)
                    .frame(maxHeight: .infinity)
            }
        }
        .frame(minWidth: 900, minHeight: 600)
        .onDrop(of: [.fileURL], isTargeted: $isWindowDropTargeted) { providers in
            for provider in providers {
                _ = provider.loadObject(ofClass: URL.self) { url, _ in
                    guard let url = url else { return }
                    DispatchQueue.main.async {
                        manager.addURLs([url])
                    }
                }
            }
            return true
        }
        .sheet(isPresented: $showHelpGuide) {
            HelpGuideView()
        }
        .sheet(isPresented: Binding(
            get: { manager.isExporting || manager.showSuccessSheet },
            set: { newValue in
                if !newValue {
                    manager.showSuccessSheet = false
                }
            }
        )) {
            ExportProgressView(manager: manager)
        }
        .alert("Error", isPresented: Binding(
            get: { manager.errorMessage != nil },
            set: { if !$0 { manager.errorMessage = nil } }
        )) {
            Button("OK") { manager.errorMessage = nil }
        } message: {
            Text(manager.errorMessage ?? "")
        }
    }
}

/// A draggable divider component that resizes the right sidebar width smoothly
struct ResizableDivider: View {
    @Binding var settingsWidth: CGFloat
    let minWidth: CGFloat
    let maxWidth: CGFloat
    @State private var isHovering: Bool = false

    var body: some View {
        ZStack {
            Rectangle()
                .fill(Color(nsColor: .separatorColor))
                .frame(width: 1)

            Rectangle()
                .fill(isHovering ? Color.accentColor.opacity(0.3) : Color.clear)
                .frame(width: 7)
        }
        .frame(maxHeight: .infinity)
        .contentShape(Rectangle())
        .onHover { hovering in
            isHovering = hovering
            if hovering {
                NSCursor.resizeLeftRight.push()
            } else {
                NSCursor.pop()
            }
        }
        .gesture(
            DragGesture(minimumDistance: 1)
                .onChanged { value in
                    let newWidth = settingsWidth - value.translation.width
                    settingsWidth = min(maxWidth, max(minWidth, newWidth))
                }
        )
    }
}
