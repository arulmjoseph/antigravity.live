@echo off
echo ===================================================
echo 🔨 Building Image Converter for Windows (Single EXE)...
echo ===================================================

dotnet publish ImageConverterWin.csproj -c Release -r win-x64 --self-contained true -p:PublishSingleFile=true -p:EnableCompressionInSingleFile=true

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ✅ Build Complete!
    echo 📦 Executable location: bin\Release\net8.0-windows\win-x64\publish\ImageConverterWin.exe
) else (
    echo.
    echo ❌ Build failed. Please check error output above.
)
pause
