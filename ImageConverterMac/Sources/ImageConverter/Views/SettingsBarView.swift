import SwiftUI

struct SettingsBarView: View {
    @ObservedObject var manager: ExportManager

    // Collapsible section states (all expanded by default)
    @State private var isFormatExpanded: Bool = true
    @State private var isSizeExpanded: Bool = true
    @State private var isRenamingExpanded: Bool = true
    @State private var isDestinationExpanded: Bool = true

    var sampleRenamedPreview: String {
        switch manager.config.namingMode {
        case .original:
            return "Original filename preserved"
        case .customSequence:
            let base = manager.config.customBaseName.trimmingCharacters(in: .whitespacesAndNewlines).isEmpty ? "image" : manager.config.customBaseName.trimmingCharacters(in: .whitespacesAndNewlines)
            let num1 = String(format: "%0\(manager.config.numberPadding)d", manager.config.startSequenceIndex)
            let num2 = String(format: "%0\(manager.config.numberPadding)d", manager.config.startSequenceIndex + 1)
            var suffix = ""
            if manager.config.appendWidthSuffix && manager.config.sizeOption == .customWidth {
                suffix = "_\(manager.config.customWidthValue)w"
            }
            let ext = manager.config.format.fileExtension
            return "\(base)_\(num1)\(suffix).\(ext), \(base)_\(num2)\(suffix).\(ext)..."
        case .seoKeywords:
            let keywords = manager.config.sanitizedSEOKeywords()
            if keywords.isEmpty {
                return "Paste keywords to generate SEO filenames"
            }
            var suffix = ""
            if manager.config.appendWidthSuffix && manager.config.sizeOption == .customWidth {
                suffix = "_\(manager.config.customWidthValue)w"
            }
            let ext = manager.config.format.fileExtension
            let k1 = keywords[0]
            let k2 = keywords.count > 1 ? keywords[1] : keywords[0]
            return "\(k1)\(suffix).\(ext), \(k2)\(suffix).\(ext)..."
        }
    }

    var body: some View {
        ScrollView {
            VStack(alignment: .leading, spacing: 16) {
                
                // Section 1: Target Format & Quality
                CollapsibleSection(
                    title: "Target Format & Compression",
                    icon: "photo.on.rectangle.angled",
                    isExpanded: $isFormatExpanded
                ) {
                    VStack(alignment: .leading, spacing: 12) {
                        HStack {
                            Text("Format")
                                .font(.caption.weight(.medium))
                                .foregroundColor(.secondary)
                            Spacer()
                            if manager.config.format == .webp {
                                Text("Best for Web ⭐")
                                    .font(.system(size: 11, weight: .bold))
                                    .padding(.horizontal, 8)
                                    .padding(.vertical, 2)
                                    .background(Color.green.opacity(0.15))
                                    .foregroundColor(.green)
                                    .cornerRadius(4)
                            }
                        }

                        Picker("", selection: $manager.config.format) {
                            ForEach(TargetFormat.allCases) { format in
                                Text(format.displayName).tag(format)
                            }
                        }
                        .pickerStyle(.menu)
                        .labelsHidden()
                        .frame(maxWidth: .infinity, alignment: .leading)

                        if manager.config.format == .webp {
                            Text("💡 WebP offers superior compression for fastest page loading speeds.")
                                .font(.caption)
                                .foregroundColor(.secondary)
                        } else if manager.config.format == .pdf {
                            Text("📄 Exports images/PDFs into an optimized PDF document. Quality slider controls PDF image compression.")
                                .font(.caption)
                                .foregroundColor(.secondary)
                        }

                        Toggle(isOn: $manager.config.optimizeForWeb) {
                            VStack(alignment: .leading, spacing: 2) {
                                Text("Optimize for Web")
                                    .font(.body.weight(.medium))
                                Text("Strips EXIF/GPS metadata & maximizes compression")
                                    .font(.caption)
                                    .foregroundColor(.secondary)
                            }
                        }
                        .toggleStyle(.checkbox)

                        if manager.config.format.supportsQuality {
                            VStack(alignment: .leading, spacing: 6) {
                                HStack {
                                    Text("Compression Quality")
                                        .font(.subheadline.weight(.medium))
                                        .foregroundColor(.primary)
                                    Spacer()
                                    Text("\(Int(manager.config.jpegQuality * 100))%")
                                        .font(.subheadline.monospacedDigit().weight(.bold))
                                        .foregroundColor(.accentColor)
                                }

                                Slider(value: $manager.config.jpegQuality, in: 0.1...1.0, step: 0.05)
                            }
                            .padding(.top, 4)
                        }
                    }
                }

                Divider()

                // Section 2: Image Dimensions & Custom Width
                CollapsibleSection(
                    title: "Image Size / Width",
                    icon: "aspectratio",
                    isExpanded: $isSizeExpanded
                ) {
                    VStack(alignment: .leading, spacing: 12) {
                        Picker("", selection: $manager.config.sizeOption) {
                            ForEach(SizeOption.allCases) { option in
                                Text(option.rawValue).tag(option)
                            }
                        }
                        .pickerStyle(.segmented)

                        if manager.config.sizeOption == .customWidth {
                            VStack(alignment: .leading, spacing: 10) {
                                HStack(spacing: 10) {
                                    Text("Width:")
                                        .font(.body.weight(.medium))
                                        .foregroundColor(.primary)

                                    TextField("e.g. 500", text: $manager.config.customWidthText)
                                        .textFieldStyle(.roundedBorder)
                                        .frame(width: 90)
                                        .multilineTextAlignment(.leading)

                                    Text("pixels")
                                        .font(.body)
                                        .foregroundColor(.secondary)

                                    Spacer()
                                }

                                HStack(spacing: 6) {
                                    Image(systemName: "aspectratio.fill")
                                        .font(.caption)
                                        .foregroundColor(.accentColor)
                                    Text("Height automatically scales proportionally")
                                        .font(.caption)
                                        .foregroundColor(.secondary)
                                }
                            }
                            .padding(12)
                            .background(Color(nsColor: .controlBackgroundColor).opacity(0.6))
                            .cornerRadius(8)
                        }
                    }
                }

                Divider()

                // Section 3: File Renaming & SEO Keywords
                CollapsibleSection(
                    title: "File Renaming",
                    icon: "tag",
                    isExpanded: $isRenamingExpanded
                ) {
                    VStack(alignment: .leading, spacing: 12) {
                        Picker("", selection: $manager.config.namingMode) {
                            ForEach(NamingMode.allCases) { mode in
                                Text(mode.rawValue).tag(mode)
                            }
                        }
                        .pickerStyle(.segmented)

                        if manager.config.namingMode == .customSequence {
                            VStack(alignment: .leading, spacing: 10) {
                                HStack(spacing: 10) {
                                    Text("Base Name:")
                                        .font(.body.weight(.medium))
                                        .foregroundColor(.primary)

                                    TextField("e.g. test1", text: $manager.config.customBaseName)
                                        .textFieldStyle(.roundedBorder)
                                }

                                HStack(spacing: 10) {
                                    Text("Digits:")
                                        .font(.body)
                                        .foregroundColor(.secondary)

                                    Picker("", selection: $manager.config.numberPadding) {
                                        Text("001 (3 digits)").tag(3)
                                        Text("01 (2 digits)").tag(2)
                                        Text("1 (1 digit)").tag(1)
                                    }
                                    .pickerStyle(.menu)
                                    .labelsHidden()
                                }
                            }
                            .padding(12)
                            .background(Color(nsColor: .controlBackgroundColor).opacity(0.6))
                            .cornerRadius(8)
                        } else if manager.config.namingMode == .seoKeywords {
                            VStack(alignment: .leading, spacing: 10) {
                                HStack {
                                    Text("Bulk SEO Phrases:")
                                        .font(.subheadline.weight(.medium))
                                        .foregroundColor(.primary)
                                    Spacer()
                                    Text("\(manager.config.sanitizedSEOKeywords().count) Slugs Ready")
                                        .font(.caption.weight(.bold))
                                        .padding(.horizontal, 8)
                                        .padding(.vertical, 2)
                                        .background(Color.green.opacity(0.15))
                                        .foregroundColor(.green)
                                        .cornerRadius(4)
                                }

                                TextEditor(text: $manager.config.seoKeywordsText)
                                    .font(.system(size: 12, design: .monospaced))
                                    .frame(minHeight: 130, maxHeight: 220)
                                    .padding(4)
                                    .overlay(RoundedRectangle(cornerRadius: 6).stroke(Color.secondary.opacity(0.25), lineWidth: 1))

                                Toggle(isOn: $manager.config.randomizeSEOKeywords) {
                                    Text("Randomize keyword assignment across images")
                                        .font(.body)
                                }
                                .toggleStyle(.checkbox)
                            }
                            .padding(12)
                            .background(Color(nsColor: .controlBackgroundColor).opacity(0.6))
                            .cornerRadius(8)
                        }

                        // Live Filename Preview Box
                        VStack(alignment: .leading, spacing: 4) {
                            Text("Filename Preview:")
                                .font(.caption.weight(.bold))
                                .foregroundColor(.secondary)
                            Text(sampleRenamedPreview)
                                .font(.system(size: 12, design: .monospaced))
                                .foregroundColor(.accentColor)
                                .lineLimit(3)
                        }
                        .padding(10)
                        .frame(maxWidth: .infinity, alignment: .leading)
                        .background(Color(nsColor: .controlBackgroundColor))
                        .cornerRadius(8)

                        if manager.config.sizeOption == .customWidth {
                            Toggle(isOn: $manager.config.appendWidthSuffix) {
                                Text("Append width tag to filename (e.g. _500w)")
                                    .font(.body)
                            }
                            .toggleStyle(.checkbox)
                        }
                    }
                }

                Divider()

                // Section 4: Destination
                CollapsibleSection(
                    title: "Export Destination",
                    icon: "folder.badge.gearshape",
                    isExpanded: $isDestinationExpanded
                ) {
                    VStack(alignment: .leading, spacing: 10) {
                        Picker("", selection: $manager.config.destinationType) {
                            ForEach(ExportDestination.allCases) { dest in
                                Text(dest.rawValue).tag(dest)
                            }
                        }
                        .pickerStyle(.segmented)

                        if manager.config.destinationType == .desktopFolder {
                            Text("Creates a new folder on Desktop automatically.")
                                .font(.caption)
                                .foregroundColor(.secondary)
                        } else {
                            HStack {
                                Text(manager.config.customDestinationPath.isEmpty ? "No folder chosen" : (manager.config.customDestinationPath as NSString).lastPathComponent)
                                    .font(.body)
                                    .foregroundColor(.secondary)
                                    .lineLimit(1)
                                    .truncationMode(.middle)
                                Spacer()
                                Button("Choose...") {
                                    manager.selectCustomDestination()
                                }
                                .buttonStyle(.bordered)
                                .controlSize(.regular)
                            }
                        }
                    }
                }

                Spacer(minLength: 16)

                // Export Button
                Button(action: {
                    manager.exportAll()
                }) {
                    HStack {
                        Image(systemName: "arrow.right.circle.fill")
                            .font(.title2)
                        Text(manager.items.isEmpty ? "Add Images to Export" : "Export \(manager.items.count) \(manager.items.count == 1 ? "Image" : "Images")")
                            .font(.title3.weight(.bold))
                    }
                    .frame(maxWidth: .infinity)
                    .padding(.vertical, 10)
                }
                .buttonStyle(.borderedProminent)
                .controlSize(.large)
                .disabled(manager.items.isEmpty || manager.isExporting)

                // Footer Credit (Flush Left)
                HStack(spacing: 4) {
                    Text("Created by")
                        .font(.body)
                        .foregroundColor(.secondary)
                    Link("arulmjoseph.com", destination: URL(string: "https://arulmjoseph.com")!)
                        .font(.body.weight(.semibold))
                        .foregroundColor(.accentColor)
                }
                .frame(maxWidth: .infinity, alignment: .leading)
                .padding(.top, 4)
                .padding(.bottom, 8)
            }
            .padding(20)
        }
        .frame(maxWidth: .infinity, alignment: .leading)
        .background(Color(nsColor: .windowBackgroundColor).opacity(0.85))
    }
}

/// Custom collapsible section view with flush left alignment and smooth toggle animation
struct CollapsibleSection<Content: View>: View {
    let title: String
    let icon: String
    @Binding var isExpanded: Bool
    @ViewBuilder let content: () -> Content

    var body: some View {
        VStack(alignment: .leading, spacing: 0) {
            Button(action: {
                withAnimation(.easeInOut(duration: 0.2)) {
                    isExpanded.toggle()
                }
            }) {
                HStack(spacing: 8) {
                    Image(systemName: icon)
                        .foregroundColor(.accentColor)
                        .font(.subheadline)
                        .frame(width: 18, alignment: .center)
                    
                    Text(title)
                        .font(.subheadline.weight(.semibold))
                        .foregroundColor(.primary)
                    
                    Spacer()
                    
                    Image(systemName: isExpanded ? "chevron.down" : "chevron.right")
                        .font(.caption.weight(.bold))
                        .foregroundColor(.secondary)
                }
                .padding(.vertical, 4)
                .contentShape(Rectangle())
            }
            .buttonStyle(.plain)

            if isExpanded {
                VStack(alignment: .leading, spacing: 12) {
                    content()
                }
                .padding(.top, 10)
                .transition(.opacity.combined(with: .move(edge: .top)))
            }
        }
        .frame(maxWidth: .infinity, alignment: .leading)
    }
}
