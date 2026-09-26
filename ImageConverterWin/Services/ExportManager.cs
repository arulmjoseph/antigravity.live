using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.ComponentModel;
using System.Diagnostics;
using System.IO;
using System.Linq;
using System.Runtime.CompilerServices;
using System.Threading;
using System.Threading.Tasks;
using ImageConverterWin.Models;

namespace ImageConverterWin.Services
{
    public class ExportManager : INotifyPropertyChanged
    {
        public ObservableCollection<ImageItem> Items { get; } = new ObservableCollection<ImageItem>();
        public ExportConfig Config { get; } = new ExportConfig();

        private bool _isExporting = false;
        private double _exportProgress = 0.0;
        private string _currentProcessingName = "";
        private int _currentProcessingIndex = 0;
        private int _totalToProcess = 0;
        private string? _lastExportFolderPath;
        private string? _errorMessage;

        public bool IsExporting
        {
            get => _isExporting;
            set { _isExporting = value; OnPropertyChanged(); }
        }

        public double ExportProgress
        {
            get => _exportProgress;
            set { _exportProgress = value; OnPropertyChanged(); }
        }

        public string CurrentProcessingName
        {
            get => _currentProcessingName;
            set { _currentProcessingName = value; OnPropertyChanged(); }
        }

        public int CurrentProcessingIndex
        {
            get => _currentProcessingIndex;
            set { _currentProcessingIndex = value; OnPropertyChanged(); }
        }

        public int TotalToProcess
        {
            get => _totalToProcess;
            set { _totalToProcess = value; OnPropertyChanged(); }
        }

        public string? LastExportFolderPath
        {
            get => _lastExportFolderPath;
            set { _lastExportFolderPath = value; OnPropertyChanged(); }
        }

        public string? ErrorMessage
        {
            get => _errorMessage;
            set { _errorMessage = value; OnPropertyChanged(); }
        }

        public void AddFiles(IEnumerable<string> paths)
        {
            var supportedExts = new HashSet<string>(StringComparer.OrdinalIgnoreCase)
            {
                ".jpg", ".jpeg", ".png", ".webp", ".pdf", ".heic", ".avif", ".tiff", ".bmp", ".gif"
            };

            var existing = new HashSet<string>(Items.Select(x => x.FilePath), StringComparer.OrdinalIgnoreCase);

            foreach (var path in paths)
            {
                if (Directory.Exists(path))
                {
                    var files = Directory.GetFiles(path, "*.*", SearchOption.AllDirectories)
                        .Where(f => supportedExts.Contains(Path.GetExtension(f)));
                    foreach (var f in files)
                    {
                        if (!existing.Contains(f))
                        {
                            Items.Add(new ImageItem(f));
                            existing.Add(f);
                        }
                    }
                }
                else if (File.Exists(path) && supportedExts.Contains(Path.GetExtension(path)))
                {
                    if (!existing.Contains(path))
                    {
                        Items.Add(new ImageItem(path));
                        existing.Add(path);
                    }
                }
            }
        }

        public void RemoveItem(ImageItem item)
        {
            Items.Remove(item);
        }

        public void ClearAll()
        {
            Items.Clear();
            LastExportFolderPath = null;
            ExportProgress = 0.0;
        }

        public string ProjectedBaseName(int index)
        {
            switch (Config.NamingMode)
            {
                case NamingMode.OriginalName:
                    if (index < Items.Count) return Path.GetFileNameWithoutExtension(Items[index].FileName);
                    return $"image-{index + 1}";

                case NamingMode.CustomSequence:
                    string cleanBase = string.IsNullOrWhiteSpace(Config.CustomBaseName) ? "image" : Config.CustomBaseName.Trim();
                    int seqNum = Config.StartSequenceIndex + index;
                    string paddedNum = seqNum.ToString($"D{Config.NumberPadding}");
                    return $"{cleanBase}_{paddedNum}";

                case NamingMode.BulkSEOKeywords:
                    var keywords = Config.SanitizedSEOKeywords();
                    if (keywords.Count == 0) return $"seo-image-{index + 1}";
                    int kIdx = index % keywords.Count;
                    int repeatCount = index / keywords.Count;
                    if (repeatCount > 0) return $"{keywords[kIdx]}-{repeatCount + 1}";
                    return keywords[kIdx];

                default:
                    return $"image-{index + 1}";
            }
        }

        public async Task ExportAllAsync(CancellationToken cancellationToken = default)
        {
            if (Items.Count == 0 || IsExporting) return;

            IsExporting = true;
            ExportProgress = 0.0;
            TotalToProcess = Items.Count;
            CurrentProcessingIndex = 0;

            string targetFolderPath;
            if (Config.DestinationType == ExportDestination.CustomFolder && !string.IsNullOrWhiteSpace(Config.CustomDestinationPath))
            {
                targetFolderPath = Config.CustomDestinationPath;
            }
            else
            {
                string desktop = Environment.GetFolderPath(Environment.SpecialFolder.Desktop);
                string folderName = !string.IsNullOrWhiteSpace(Config.CustomFolderName)
                    ? Config.CustomFolderName.Trim()
                    : $"Converted_Images_{DateTime.Now:yyyy-MM-dd_HHmmss}";
                targetFolderPath = Path.Combine(desktop, folderName);
            }

            Directory.CreateDirectory(targetFolderPath);
            LastExportFolderPath = targetFolderPath;

            var seoList = Config.SanitizedSEOKeywords();
            var usedFileNames = new HashSet<string>(StringComparer.OrdinalIgnoreCase);

            await Task.Run(() =>
            {
                for (int i = 0; i < Items.Count; i++)
                {
                    if (cancellationToken.IsCancellationRequested) break;

                    var item = Items[i];
                    CurrentProcessingIndex = i + 1;
                    CurrentProcessingName = item.FileName;
                    item.Status = ConversionStatus.Processing;

                    string baseName;
                    switch (Config.NamingMode)
                    {
                        case NamingMode.OriginalName:
                            baseName = Path.GetFileNameWithoutExtension(item.FileName);
                            break;
                        case NamingMode.CustomSequence:
                            string cb = string.IsNullOrWhiteSpace(Config.CustomBaseName) ? "image" : Config.CustomBaseName.Trim();
                            int seqNum = Config.StartSequenceIndex + i;
                            baseName = $"{cb}_{seqNum.ToString($"D{Config.NumberPadding}")}";
                            break;
                        case NamingMode.BulkSEOKeywords:
                            if (seoList.Count == 0)
                            {
                                baseName = $"seo-image-{i + 1}";
                            }
                            else
                            {
                                int kIdx = i % seoList.Count;
                                int rCount = i / seoList.Count;
                                baseName = rCount > 0 ? $"{seoList[kIdx]}-{rCount + 1}" : seoList[kIdx];
                            }
                            break;
                        default:
                            baseName = $"image-{i + 1}";
                            break;
                    }

                    string suffix = (Config.AppendWidthSuffix && Config.SizeOption == SizeOption.CustomWidth)
                        ? $"_{Config.CustomWidthValue}w"
                        : "";

                    string ext = Config.FileExtension;
                    string outName = $"{baseName}{suffix}.{ext}";
                    int counter = 1;
                    while (usedFileNames.Contains(outName))
                    {
                        outName = $"{baseName}{suffix}_{counter}.{ext}";
                        counter++;
                    }
                    usedFileNames.Add(outName);

                    string destFile = Path.Combine(targetFolderPath, outName);

                    try
                    {
                        ImageProcessor.ProcessFile(item.FilePath, destFile, Config);
                        item.OutputPath = destFile;
                        item.Status = ConversionStatus.Completed;
                    }
                    catch (Exception ex)
                    {
                        item.ErrorMessage = ex.Message;
                        item.Status = ConversionStatus.Failed;
                    }

                    ExportProgress = (double)(i + 1) / Items.Count;
                }
            }, cancellationToken);

            IsExporting = false;
        }

        public void RevealInExplorer()
        {
            if (!string.IsNullOrEmpty(LastExportFolderPath) && Directory.Exists(LastExportFolderPath))
            {
                Process.Start("explorer.exe", LastExportFolderPath);
            }
        }

        public event PropertyChangedEventHandler? PropertyChanged;
        protected void OnPropertyChanged([CallerMemberName] string? name = null)
        {
            PropertyChanged?.Invoke(this, new PropertyChangedEventArgs(name));
        }
    }
}
