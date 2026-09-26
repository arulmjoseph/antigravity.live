import SwiftUI
import AppKit

struct ExportProgressView: View {
    @ObservedObject var manager: ExportManager
    @Environment(\.dismiss) private var dismiss

    var sizeSavingsString: String {
        let orig = ByteCountFormatter.string(fromByteCount: manager.totalOriginalBytes, countStyle: .file)
        let conv = ByteCountFormatter.string(fromByteCount: manager.totalConvertedBytes, countStyle: .file)
        if manager.totalOriginalBytes > 0 && manager.totalConvertedBytes > 0 {
            let diff = manager.totalOriginalBytes - manager.totalConvertedBytes
            let pct = Int((Double(diff) / Double(manager.totalOriginalBytes)) * 100)
            if pct > 0 {
                return "\(orig) → \(conv) (\(pct)% smaller)"
            } else if pct < 0 {
                return "\(orig) → \(conv) (\(abs(pct))% larger)"
            }
        }
        return "\(orig) → \(conv)"
    }

    var body: some View {
        VStack(spacing: 20) {
            if manager.isExporting {
                // In-progress view
                VStack(spacing: 16) {
                    ProgressView()
                        .scaleEffect(1.2)

                    VStack(spacing: 6) {
                        Text("Converting Images...")
                            .font(.headline)

                        Text("Processing \(manager.currentProcessingIndex) of \(manager.totalToProcess): \(manager.currentProcessingName)")
                            .font(.caption)
                            .foregroundColor(.secondary)
                            .lineLimit(1)
                            .truncationMode(.middle)
                    }

                    ProgressView(value: manager.exportProgress, total: 1.0)
                        .progressViewStyle(.linear)
                        .frame(width: 280)

                    Text("\(Int(manager.exportProgress * 100))%")
                        .font(.caption.monospacedDigit())
                        .foregroundColor(.secondary)

                    Button("Cancel") {
                        manager.cancelExport()
                    }
                    .buttonStyle(.bordered)
                    .controlSize(.small)
                }
                .padding(24)
            } else {
                // Success / Completed view
                VStack(spacing: 16) {
                    ZStack {
                        Circle()
                            .fill(Color.green.opacity(0.15))
                            .frame(width: 64, height: 64)
                        Image(systemName: "checkmark.circle.fill")
                            .font(.system(size: 38))
                            .foregroundColor(.green)
                    }

                    VStack(spacing: 6) {
                        Text("Export Completed!")
                            .font(.title2.weight(.bold))

                        Text("All images were converted to \(manager.config.format.rawValue) and saved in a new folder on your Desktop.")
                            .font(.subheadline)
                            .foregroundColor(.secondary)
                            .multilineTextAlignment(.center)
                            .frame(maxWidth: 320)
                    }

                    if manager.totalConvertedBytes > 0 {
                        HStack(spacing: 6) {
                            Image(systemName: "internaldrive.fill")
                                .foregroundColor(.secondary)
                                .font(.caption)
                            Text(sizeSavingsString)
                                .font(.caption.weight(.medium))
                                .foregroundColor(.secondary)
                        }
                        .padding(.horizontal, 12)
                        .padding(.vertical, 6)
                        .background(Color(nsColor: .controlBackgroundColor))
                        .cornerRadius(8)
                    }

                    HStack(spacing: 12) {
                        Button(action: {
                            manager.revealInFinder()
                        }) {
                            Label("Open in Finder", systemImage: "folder.badge.gearshape")
                        }
                        .buttonStyle(.borderedProminent)
                        .controlSize(.regular)

                        Button("Done") {
                            manager.showSuccessSheet = false
                        }
                        .buttonStyle(.bordered)
                        .controlSize(.regular)
                    }
                    .padding(.top, 8)
                }
                .padding(28)
            }
        }
        .frame(minWidth: 380, minHeight: 240)
    }
}
