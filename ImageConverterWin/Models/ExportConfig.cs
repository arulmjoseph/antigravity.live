using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Linq;
using System.Runtime.CompilerServices;
using System.Text.RegularExpressions;

namespace ImageConverterWin.Models
{
    public enum TargetFormat
    {
        [Description("WEBP — Best for Website ⭐")]
        WebP,
        [Description("PDF — Optimized Document & PDF 📄")]
        Pdf,
        [Description("JPG — Standard Web & Photos")]
        Jpeg,
        [Description("PNG — Lossless & Transparency")]
        Png,
        [Description("HEIC — Apple High Efficiency")]
        Heic,
        [Description("AVIF — Modern Next-Gen")]
        Avif,
        [Description("TIFF — Uncompressed")]
        Tiff,
        [Description("BMP — Bitmap")]
        Bmp,
        [Description("GIF — Graphics")]
        Gif
    }

    public enum SizeOption
    {
        CustomWidth,
        Original
    }

    public enum NamingMode
    {
        OriginalName,
        CustomSequence,
        BulkSEOKeywords
    }

    public enum ExportDestination
    {
        DesktopFolder,
        CustomFolder
    }

    public class ExportConfig : INotifyPropertyChanged
    {
        private TargetFormat _format = TargetFormat.WebP;
        private SizeOption _sizeOption = SizeOption.CustomWidth;
        private string _customWidthText = "500";
        private double _jpegQuality = 0.85;
        private bool _optimizeForWeb = true;

        private NamingMode _namingMode = NamingMode.BulkSEOKeywords;
        private string _customBaseName = "image";
        private int _startSequenceIndex = 1;
        private int _numberPadding = 3;

        private string _seoKeywordsText = @"* CFO financial strategy meeting Middle East
* corporate governance board meeting Dubai
* business executives financial planning documents GCC
* finance consultant reviewing reports with client
* corporate tax advisory meeting UAE
* executive financial analysis modern office
* business restructuring advisory meeting
* auditor reviewing financial statements office";

        private bool _randomizeSEOKeywords = true;
        private bool _appendWidthSuffix = false;
        private ExportDestination _destinationType = ExportDestination.DesktopFolder;
        private string _customDestinationPath = "";
        private string _customFolderName = "";

        public TargetFormat Format
        {
            get => _format;
            set { _format = value; OnPropertyChanged(); OnPropertyChanged(nameof(SupportsQuality)); }
        }

        public SizeOption SizeOption
        {
            get => _sizeOption;
            set { _sizeOption = value; OnPropertyChanged(); }
        }

        public string CustomWidthText
        {
            get => _customWidthText;
            set { _customWidthText = value; OnPropertyChanged(); OnPropertyChanged(nameof(CustomWidthValue)); }
        }

        public double JpegQuality
        {
            get => _jpegQuality;
            set { _jpegQuality = value; OnPropertyChanged(); }
        }

        public bool OptimizeForWeb
        {
            get => _optimizeForWeb;
            set { _optimizeForWeb = value; OnPropertyChanged(); }
        }

        public NamingMode NamingMode
        {
            get => _namingMode;
            set { _namingMode = value; OnPropertyChanged(); }
        }

        public string CustomBaseName
        {
            get => _customBaseName;
            set { _customBaseName = value; OnPropertyChanged(); }
        }

        public int StartSequenceIndex
        {
            get => _startSequenceIndex;
            set { _startSequenceIndex = value; OnPropertyChanged(); }
        }

        public int NumberPadding
        {
            get => _numberPadding;
            set { _numberPadding = value; OnPropertyChanged(); }
        }

        public string SeoKeywordsText
        {
            get => _seoKeywordsText;
            set { _seoKeywordsText = value; OnPropertyChanged(); }
        }

        public bool RandomizeSEOKeywords
        {
            get => _randomizeSEOKeywords;
            set { _randomizeSEOKeywords = value; OnPropertyChanged(); }
        }

        public bool AppendWidthSuffix
        {
            get => _appendWidthSuffix;
            set { _appendWidthSuffix = value; OnPropertyChanged(); }
        }

        public ExportDestination DestinationType
        {
            get => _destinationType;
            set { _destinationType = value; OnPropertyChanged(); }
        }

        public string CustomDestinationPath
        {
            get => _customDestinationPath;
            set { _customDestinationPath = value; OnPropertyChanged(); }
        }

        public string CustomFolderName
        {
            get => _customFolderName;
            set { _customFolderName = value; OnPropertyChanged(); }
        }

        public bool SupportsQuality => Format == TargetFormat.Jpeg || Format == TargetFormat.WebP || Format == TargetFormat.Heic || Format == TargetFormat.Avif || Format == TargetFormat.Pdf;

        public int CustomWidthValue
        {
            get
            {
                if (int.TryParse(CustomWidthText?.Trim(), out int val))
                {
                    return Math.Max(1, val);
                }
                return 500;
            }
        }

        public string FileExtension => Format switch
        {
            TargetFormat.WebP => "webp",
            TargetFormat.Pdf => "pdf",
            TargetFormat.Jpeg => "jpg",
            TargetFormat.Png => "png",
            TargetFormat.Heic => "heic",
            TargetFormat.Avif => "avif",
            TargetFormat.Tiff => "tiff",
            TargetFormat.Bmp => "bmp",
            TargetFormat.Gif => "gif",
            _ => "jpg"
        };

        public List<string> SanitizedSEOKeywords()
        {
            var result = new List<string>();
            if (string.IsNullOrWhiteSpace(SeoKeywordsText)) return result;

            var rawLines = SeoKeywordsText.Split(new[] { "\r\n", "\r", "\n" }, StringSplitOptions.RemoveEmptyEntries);

            foreach (var line in rawLines)
            {
                string trimmed = line.Trim();
                if (string.IsNullOrEmpty(trimmed)) continue;

                // Strip bullet symbols (*, -, •, #)
                while (trimmed.StartsWith("*") || trimmed.StartsWith("-") || trimmed.StartsWith("•") || trimmed.StartsWith("#"))
                {
                    trimmed = trimmed.Substring(1).Trim();
                }

                // Strip leading digits and dots (e.g. "1. ")
                trimmed = Regex.Replace(trimmed, @"^\d+[\.\)\-]?\s*", "").Trim();
                if (string.IsNullOrEmpty(trimmed)) continue;

                // Convert to lower dash-separated SEO slug
                string slug = trimmed.ToLowerInvariant();
                slug = Regex.Replace(slug, @"[^a-z0-9\s\-]", "");
                slug = Regex.Replace(slug, @"[\s_]+", "-");
                slug = Regex.Replace(slug, @"-+", "-").Trim('-');

                if (!string.IsNullOrEmpty(slug))
                {
                    result.Add(slug);
                }
            }

            return result;
        }

        public (int width, int height) CalculateDimensions(int originalWidth, int originalHeight)
        {
            if (originalWidth <= 0 || originalHeight <= 0) return (originalWidth, originalHeight);
            if (SizeOption == SizeOption.Original) return (originalWidth, originalHeight);

            double reqW = CustomWidthValue;
            double origW = originalWidth;
            double origH = originalHeight;
            double aspectRatio = origW / origH;
            double reqH = Math.Round(reqW / aspectRatio);

            return (Math.Max(1, (int)reqW), Math.Max(1, (int)reqH));
        }

        public event PropertyChangedEventHandler? PropertyChanged;
        protected void OnPropertyChanged([CallerMemberName] string? name = null)
        {
            PropertyChanged?.Invoke(this, new PropertyChangedEventArgs(name));
        }
    }
}
