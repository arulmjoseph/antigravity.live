using System;
using System.IO;
using ImageConverterWin.Models;
using ImageMagick;

namespace ImageConverterWin.Services
{
    public static class ImageProcessor
    {
        public static void ProcessFile(string sourcePath, string destinationPath, ExportConfig config)
        {
            bool isPdfInput = Path.GetExtension(sourcePath).Equals(".pdf", StringComparison.OrdinalIgnoreCase);

            if (isPdfInput)
            {
                ProcessPdfSource(sourcePath, destinationPath, config);
            }
            else
            {
                ProcessImageSource(sourcePath, destinationPath, config);
            }
        }

        private static void ProcessPdfSource(string sourcePath, string destinationPath, ExportConfig config)
        {
            if (config.Format == TargetFormat.Pdf)
            {
                // PDF -> PDF Optimization (multi-page)
                using var collection = new MagickImageCollection();
                collection.Read(sourcePath);

                foreach (var image in collection)
                {
                    var (targetW, targetH) = config.CalculateDimensions((int)image.Width, (int)image.Height);
                    if (targetW != image.Width || targetH != image.Height)
                    {
                        image.Resize(new MagickGeometry((uint)targetW, (uint)targetH) { IgnoreAspectRatio = false });
                    }

                    image.Quality = (uint)Math.Clamp((int)(config.JpegQuality * 100), 1, 100);

                    if (config.OptimizeForWeb)
                    {
                        image.Strip();
                    }
                }

                collection.Write(destinationPath, MagickFormat.Pdf);
            }
            else
            {
                // PDF -> Image (Page 1)
                using var collection = new MagickImageCollection();
                var readSettings = new MagickReadSettings
                {
                    FrameIndex = 0,
                    FrameCount = 1
                };
                collection.Read(sourcePath, readSettings);

                if (collection.Count > 0)
                {
                    using var firstPage = collection[0];
                    var (targetW, targetH) = config.CalculateDimensions((int)firstPage.Width, (int)firstPage.Height);
                    if (targetW != firstPage.Width || targetH != firstPage.Height)
                    {
                        firstPage.Resize(new MagickGeometry((uint)targetW, (uint)targetH) { IgnoreAspectRatio = false });
                    }

                    firstPage.Quality = (uint)Math.Clamp((int)(config.JpegQuality * 100), 1, 100);

                    if (config.OptimizeForWeb)
                    {
                        firstPage.Strip();
                    }

                    MagickFormat format = MapTargetFormat(config.Format);
                    firstPage.Write(destinationPath, format);
                }
            }
        }

        private static void ProcessImageSource(string sourcePath, string destinationPath, ExportConfig config)
        {
            using var image = new MagickImage(sourcePath);

            var (targetW, targetH) = config.CalculateDimensions((int)image.Width, (int)image.Height);
            if (targetW != image.Width || targetH != image.Height)
            {
                image.Resize(new MagickGeometry((uint)targetW, (uint)targetH) { IgnoreAspectRatio = false });
            }

            image.Quality = (uint)Math.Clamp((int)(config.JpegQuality * 100), 1, 100);

            if (config.OptimizeForWeb)
            {
                image.Strip();
            }

            MagickFormat format = MapTargetFormat(config.Format);
            image.Write(destinationPath, format);
        }

        private static MagickFormat MapTargetFormat(TargetFormat format) => format switch
        {
            TargetFormat.WebP => MagickFormat.WebP,
            TargetFormat.Pdf => MagickFormat.Pdf,
            TargetFormat.Jpeg => MagickFormat.Jpeg,
            TargetFormat.Png => MagickFormat.Png,
            TargetFormat.Heic => MagickFormat.Heic,
            TargetFormat.Avif => MagickFormat.Avif,
            TargetFormat.Tiff => MagickFormat.Tiff,
            TargetFormat.Bmp => MagickFormat.Bmp,
            TargetFormat.Gif => MagickFormat.Gif,
            _ => MagickFormat.WebP
        };
    }
}
