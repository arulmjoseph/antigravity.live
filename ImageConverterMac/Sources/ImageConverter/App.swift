import SwiftUI
import AppKit

@main
struct ImageConverterApp: App {
    var body: some Scene {
        WindowGroup {
            ContentView()
        }
        .windowStyle(.titleBar)
        .windowToolbarStyle(.unified)
        .commands {
            CommandGroup(replacing: .appInfo) {
                Button("About Image Converter") {
                    NSApplication.shared.orderFrontStandardAboutPanel(
                        options: [
                            NSApplication.AboutPanelOptionKey.credits: NSAttributedString(
                                string: "Created with ❤️ by arulmjoseph.com\nVisit https://arulmjoseph.com for more tools.",
                                attributes: [
                                    .font: NSFont.systemFont(ofSize: 11),
                                    .foregroundColor: NSColor.secondaryLabelColor
                                ]
                            ),
                            NSApplication.AboutPanelOptionKey(rawValue: "Copyright"): "© 2026 arulmjoseph.com"
                        ]
                    )
                }
            }
            CommandGroup(replacing: .help) {
                Link("Visit arulmjoseph.com", destination: URL(string: "https://arulmjoseph.com")!)
            }
            CommandGroup(replacing: .newItem) {}
        }
    }
}
