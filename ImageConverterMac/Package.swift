// swift-tools-version: 5.9
import PackageDescription

let package = Package(
    name: "ImageConverter",
    platforms: [
        .macOS(.v13)
    ],
    products: [
        .executable(name: "ImageConverter", targets: ["ImageConverter"])
    ],
    targets: [
        .executableTarget(
            name: "ImageConverter",
            path: "Sources/ImageConverter"
        )
    ]
)
