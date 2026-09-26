using System;
using System.ComponentModel;
using System.IO;
using System.Runtime.CompilerServices;
using System.Windows.Media.Imaging;
using ImageMagick;

namespace ImageConverterWin.Models
{
    public enum ConversionStatus
    {
        Pending,
        Processing,
        Completed,
        Failed
    }

    public class ImageItem : INotifyPropertyChanged
    {
        public Guid Id { get; } = Guid.NewGuid();
        public string FilePath { get; }
        public string FileName { get; }
        public long OriginalFileSize { get; }
        public int PixelWidth { get; set; }
        public int PixelHeight { get; set; }
        public BitmapSource? Thumbnail { get; set; }

        private ConversionStatus _status = ConversionStatus.Pending;
        private string? _errorMessage;
        private string? _outputPath;

        public ConversionStatus Status
        {
            get => _status;
            set { _status = value; OnPropertyChanged(); }
        }

        public string? ErrorMessage
        {
            get => _errorMessage;
            set { _errorMessage = value; OnPropertyChanged(); }
        }

        public string? OutputPath
        {
            get => _outputPath;
            set { _outputPath = value; OnPropertyChanged(); }
        }

        public ImageItem(string filePath)
        {
            FilePath = filePath;
            FileName = Path.GetFileName(filePath);

            var fi = new FileInfo(filePath);
            OriginalFileSize = fi.Exists ? fi.Length : 0;

            LoadMetadataAndThumbnail();
        }

        private void LoadMetadataAndThumbnail()
        {
            try
            {
                using var image = new MagickImage();
                var info = new MagickImageInfo(FilePath);
                PixelWidth = (int)info.Width;
                PixelHeight = (int)info.Height;

                // Load thumbnail efficiently
                image.Ping(FilePath);
                image.Read(FilePath);
                image.Resize(new MagickGeometry(60, 60) { IgnoreAspectRatio = false });

                using var ms = new MemoryStream(image.ToByteArray(MagickFormat.Png));
                var bitmap = new BitmapImage();
                bitmap.BeginInit();
                bitmap.CacheOption = BitmapCacheOption.OnLoad;
                bitmap.StreamSource = ms;
                bitmap.EndInit();
                bitmap.Freeze();

                Thumbnail = bitmap;
            }
            catch
            {
                PixelWidth = 0;
                PixelHeight = 0;
                Thumbnail = null;
            }
        }

        public string FormattedSize
        {
            get
            {
                string[] suf = { "B", "KB", "MB", "GB", "TB" };
                if (OriginalFileSize == 0) return "0 B";
                long bytes = Math.Abs(OriginalFileSize);
                int place = Convert.ToInt32(Math.Floor(Math.Log(bytes, 1024)));
                double num = Math.Round(bytes / Math.Pow(1024, place), 1);
                return (Math.Sign(OriginalFileSize) * num).ToString() + " " + suf[place];
            }
        }

        public string DimensionsString
        {
            get
            {
                if (PixelWidth > 0 && PixelHeight > 0)
                    return $"{PixelWidth} × {PixelHeight} px";
                return "Unknown size";
            }
        }

        public event PropertyChangedEventHandler? PropertyChanged;
        protected void OnPropertyChanged([CallerMemberName] string? name = null)
        {
            PropertyChanged?.Invoke(this, new PropertyChangedEventArgs(name));
        }
    }
}
