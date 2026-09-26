# Image Converter for Windows 🖼️📄

A native Windows application built with C# .NET 8 WPF, Magick.NET, high-performance WebP processing, PDF optimization, custom width scaling, and bulk SEO keyword slug renaming.

---

## Features

- **Bulk Image & PDF Conversion**: Convert images and PDFs to **WEBP**, **PDF**, **JPG**, **PNG**, **HEIC**, **AVIF**, **TIFF**, **BMP**, or **GIF**.
- **PDF File Optimization (PDF → PDF)**: Downsample & compress embedded images in PDF documents to drastically shrink file sizes.
- **Custom Width & Aspect Ratio Scaling**: Specify custom pixel width (e.g. `500px`) with automatic proportional height calculation.
- **Bulk SEO Keyword Slugs**: Paste keyword lists to auto-generate SEO-friendly dash-separated filenames (`cfo-financial-strategy-meeting-middle-east.webp`).
- **Drag-and-Drop Interface**: Drop single files, bulk images, PDF documents, or entire folders.
- **Standalone Single File EXE**: Compiles into a single self-contained `ImageConverterWin.exe` with zero external runtime requirements.
- **Installable Setup Package (`ImageConverter_Setup_v1.0.exe`)**: Standard Windows setup wizard with Desktop shortcut, Start Menu entry, and Add/Remove Programs uninstaller.

---

## How to Build the Executable & Setup Installer

### Prerequisites
- [.NET 8.0 SDK](https://dotnet.microsoft.com/download/dotnet/8.0) or Visual Studio 2022.
- [Inno Setup 6](https://jrsoftware.org/isdl.php) *(Required for setup installer generation)*.

### Option 1: Automated Installer Build Script
Double-click `build_installer.bat` or run:
```cmd
build_installer.bat
```
- **Single Portable EXE**: `bin\Release\net8.0-windows\win-x64\publish\ImageConverterWin.exe`
- **Installable Setup Package**: `Output\ImageConverter_Setup_v1.0.exe`

### Option 2: GitHub Actions Automated Build
Every push to GitHub triggers `.github/workflows/build_windows_installer.yml` which automatically builds and attaches both `ImageConverterWin.exe` and `ImageConverter_Setup_v1.0.exe` as downloadable release artifacts!

---

Created by **arulmjoseph.com**
