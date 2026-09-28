#!/bin/bash
set -e

APP_NAME="FocusAlert"
APP_BUNDLE="${APP_NAME}.app"
CONTENTS_DIR="${APP_BUNDLE}/Contents"
MACOS_DIR="${CONTENTS_DIR}/MacOS"
RESOURCES_DIR="${CONTENTS_DIR}/Resources"
ARCH=$(uname -m)

echo "Building ${APP_NAME} for ${ARCH} (macOS 13+)..."

# Clean up previous build
rm -rf "${APP_BUNDLE}"

# Create app bundle directory structure
mkdir -p "${MACOS_DIR}"
mkdir -p "${RESOURCES_DIR}"

# Generate AppIcon.icns if source image exists and icns doesn't
if [ -f "app_icon_source.png" ] && [ ! -f "AppIcon.icns" ]; then
    echo "Generating AppIcon.icns..."
    mkdir -p AppIcon.iconset
    sips -z 16 16 app_icon_source.png --out AppIcon.iconset/icon_16x16.png > /dev/null
    sips -z 32 32 app_icon_source.png --out AppIcon.iconset/icon_16x16@2x.png > /dev/null
    sips -z 32 32 app_icon_source.png --out AppIcon.iconset/icon_32x32.png > /dev/null
    sips -z 64 64 app_icon_source.png --out AppIcon.iconset/icon_32x32@2x.png > /dev/null
    sips -z 128 128 app_icon_source.png --out AppIcon.iconset/icon_128x128.png > /dev/null
    sips -z 256 256 app_icon_source.png --out AppIcon.iconset/icon_128x128@2x.png > /dev/null
    sips -z 256 256 app_icon_source.png --out AppIcon.iconset/icon_256x256.png > /dev/null
    sips -z 512 512 app_icon_source.png --out AppIcon.iconset/icon_256x256@2x.png > /dev/null
    sips -z 512 512 app_icon_source.png --out AppIcon.iconset/icon_512x512.png > /dev/null
    sips -z 1024 1024 app_icon_source.png --out AppIcon.iconset/icon_512x512@2x.png > /dev/null
    iconutil -c icns AppIcon.iconset -o AppIcon.icns
    rm -rf AppIcon.iconset
fi

if [ -f "AppIcon.icns" ]; then
    cp AppIcon.icns "${RESOURCES_DIR}/AppIcon.icns"
fi

# Compile FocusAlert.swift into executable
swiftc -parse-as-library \
    -target "${ARCH}-apple-macosx13.0" \
    -framework Cocoa \
    -framework SwiftUI \
    -framework IOKit \
    -framework ServiceManagement \
    -framework Carbon \
    FocusAlert.swift \
    -o "${MACOS_DIR}/${APP_NAME}"

# Create Info.plist
cat <<EOF > "${CONTENTS_DIR}/Info.plist"
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
    <key>CFBundleDevelopmentRegion</key>
    <string>en</string>
    <key>CFBundleExecutable</key>
    <string>${APP_NAME}</string>
    <key>CFBundleIconFile</key>
    <string>AppIcon</string>
    <key>CFBundleIdentifier</key>
    <string>com.nextlo.focusalert</string>
    <key>CFBundleInfoDictionaryVersion</key>
    <string>6.0</string>
    <key>CFBundleName</key>
    <string>${APP_NAME}</string>
    <key>CFBundlePackageType</key>
    <string>APPL</string>
    <key>CFBundleShortVersionString</key>
    <string>1.0</string>
    <key>CFBundleVersion</key>
    <string>1</string>
    <key>LSMinimumSystemVersion</key>
    <string>13.0</string>
    <key>LSUIElement</key>
    <true/>
    <key>NSAppleEventsUsageDescription</key>
    <string>FocusAlert uses Automation to control System Events and Finder for Dark Mode, volume control, and emptying the Trash.</string>
</dict>
</plist>
EOF

# Ad-hoc code sign the app bundle
echo "Ad-hoc code signing ${APP_BUNDLE}..."
codesign -s - --force --deep "${APP_BUNDLE}"

echo "Build complete: ${APP_BUNDLE}"
