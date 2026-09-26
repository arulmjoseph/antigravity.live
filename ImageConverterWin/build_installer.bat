@echo off
echo ==========================================================
echo 🔨 Building Image Converter Windows Executable & Installer
echo ==========================================================

echo Step 1: Compiling .NET 8 Standalone Single-File Executable...
dotnet publish ImageConverterWin.csproj -c Release -r win-x64 --self-contained true -p:PublishSingleFile=true -p:EnableCompressionInSingleFile=true

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo ❌ .NET Publish failed. Please check error output.
    pause
    exit /b %ERRORLEVEL%
)

echo.
echo ✅ Executable built successfully at: bin\Release\net8.0-windows\win-x64\publish\ImageConverterWin.exe

echo.
echo Step 2: Generating Windows Setup Installer (.exe)...
set "ISCC_PATH=C:\Program Files (x86)\Inno Setup 6\ISCC.exe"

if exist "%ISCC_PATH%" (
    "%ISCC_PATH%" installer.iss
    if %ERRORLEVEL% EQU 0 (
        echo.
        echo ==========================================================
        echo 🎉 Installer Package Created Successfully!
        echo 📦 Setup Package: Output\ImageConverter_Setup_v1.0.exe
        echo ==========================================================
    ) else (
        echo ❌ Inno Setup compilation failed.
    )
) else (
    echo 💡 Inno Setup Compiler (ISCC.exe) not detected at "%ISCC_PATH%".
    echo    You can install Inno Setup from https://jrsoftware.org/isdl.php to auto-generate the setup .exe package!
)

pause
