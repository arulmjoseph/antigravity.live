using System;
using System.IO;
using System.Linq;
using System.Windows;
using System.Windows.Controls;
using ImageConverterWin.Models;
using ImageConverterWin.Services;
using Microsoft.Win32;

namespace ImageConverterWin.Views
{
    public partial class MainWindow : Window
    {
        public ExportManager Manager { get; } = new ExportManager();

        public MainWindow()
        {
            InitializeComponent();
            DataContext = Manager;

            PopulateFormats();
            QueueListView.ItemsSource = Manager.Items;

            Manager.Items.CollectionChanged += Items_CollectionChanged;
        }

        private void PopulateFormats()
        {
            foreach (TargetFormat format in Enum.GetValues(typeof(TargetFormat)))
            {
                FormatComboBox.Items.Add(format);
            }
            FormatComboBox.SelectedItem = TargetFormat.WebP;
        }

        private void Items_CollectionChanged(object? sender, System.Collections.Specialized.NotifyCollectionChangedEventArgs e)
        {
            if (Manager.Items.Count > 0)
            {
                DropZoneArea.Visibility = Visibility.Collapsed;
                QueueArea.Visibility = Visibility.Visible;
                ItemCountText.Text = $"{Manager.Items.Count} {(Manager.Items.Count == 1 ? "Item" : "Items")}";
            }
            else
            {
                DropZoneArea.Visibility = Visibility.Visible;
                QueueArea.Visibility = Visibility.Collapsed;
            }
        }

        private void SelectFiles_Click(object sender, RoutedEventArgs e)
        {
            var dialog = new OpenFileDialog
            {
                Multiselect = true,
                Title = "Select Images or PDFs",
                Filter = "All Supported Files|*.jpg;*.jpeg;*.png;*.webp;*.pdf;*.heic;*.avif;*.tiff;*.bmp;*.gif|Images|*.jpg;*.jpeg;*.png;*.webp;*.heic;*.avif;*.tiff;*.bmp;*.gif|PDF Files|*.pdf"
            };

            if (dialog.ShowDialog() == true)
            {
                Manager.AddFiles(dialog.FileNames);
            }
        }

        private void SelectFolder_Click(object sender, RoutedEventArgs e)
        {
            var dialog = new OpenFolderDialog
            {
                Title = "Select Folder Containing Images/PDFs"
            };

            if (dialog.ShowDialog() == true)
            {
                Manager.AddFiles(new[] { dialog.FolderName });
            }
        }

        private void Window_DragOver(object sender, DragEventArgs e)
        {
            if (e.Data.GetDataPresent(DataFormats.FileDrop))
            {
                e.Effects = DragDropEffects.Copy;
            }
            else
            {
                e.Effects = DragDropEffects.None;
            }
            e.Handled = true;
        }

        private void Window_Drop(object sender, DragEventArgs e)
        {
            if (e.Data.GetDataPresent(DataFormats.FileDrop))
            {
                string[] files = (string[])e.Data.GetData(DataFormats.FileDrop);
                Manager.AddFiles(files);
            }
        }

        private void ClearAll_Click(object sender, RoutedEventArgs e)
        {
            Manager.ClearAll();
        }

        private void RemoveItem_Click(object sender, RoutedEventArgs e)
        {
            if (sender is Button btn && btn.Tag is ImageItem item)
            {
                Manager.RemoveItem(item);
            }
        }

        private void FormatComboBox_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            if (FormatComboBox.SelectedItem is TargetFormat format)
            {
                Manager.Config.Format = format;
            }
        }

        private void QualitySlider_ValueChanged(object sender, RoutedPropertyChangedEventArgs<double> e)
        {
            if (QualityText != null)
            {
                QualityText.Text = $"{(int)(e.NewValue * 100)}%";
                Manager.Config.JpegQuality = e.NewValue;
            }
        }

        private void SizeRadio_Checked(object sender, RoutedEventArgs e)
        {
            if (Manager == null) return;
            if (CustomWidthRadio.IsChecked == true)
                Manager.Config.SizeOption = SizeOption.CustomWidth;
            else
                Manager.Config.SizeOption = SizeOption.Original;
        }

        private void NamingRadio_Checked(object sender, RoutedEventArgs e)
        {
            if (Manager == null) return;
            if (SeoNamingRadio.IsChecked == true)
                Manager.Config.NamingMode = NamingMode.BulkSEOKeywords;
            else if (SequenceNamingRadio.IsChecked == true)
                Manager.Config.NamingMode = NamingMode.CustomSequence;
            else
                Manager.Config.NamingMode = NamingMode.OriginalName;
        }

        private async void Export_Click(object sender, RoutedEventArgs e)
        {
            if (Manager.Items.Count == 0) return;

            // Sync text inputs
            Manager.Config.CustomWidthText = WidthTextBox.Text;
            Manager.Config.SeoKeywordsText = SeoKeywordsTextBox.Text;
            Manager.Config.OptimizeForWeb = OptimizeWebCheckBox.IsChecked == true;

            ExportButton.IsEnabled = false;
            ExportProgressBar.Visibility = Visibility.Visible;

            await Manager.ExportAllAsync();

            ExportButton.IsEnabled = true;
            ExportProgressBar.Visibility = Visibility.Collapsed;
            OpenFolderButton.Visibility = Visibility.Visible;
            ProgressStatusText.Text = "✅ Conversion Complete!";
        }

        private void OpenFolder_Click(object sender, RoutedEventArgs e)
        {
            Manager.RevealInExplorer();
        }

        private void Help_Click(object sender, RoutedEventArgs e)
        {
            var helpWin = new HelpWindow { Owner = this };
            helpWin.ShowDialog();
        }
    }
}
