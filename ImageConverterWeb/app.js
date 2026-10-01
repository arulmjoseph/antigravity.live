// Image & PDF Optimizer Web App Engine
// Features Before/After Split Slider, Size Estimation, & Active Defaults

class AppState {
  constructor() {
    this.items = [];
    this.selectedItemId = null;
    this.format = 'webp';
    this.quality = 0.85;
    this.widthOption = 'original'; // 'original', '1920', '1600', '1200', 'custom'
    this.customWidthVal = 500;
    this.noUpscale = true;
    this.optimizeWeb = true;
    this.removeMetadata = true;
    this.preserveTrans = false;
    this.progressiveJpeg = false;
    this.namingMode = 'original'; // 'original', 'sequence', 'seo'
    this.seqBase = 'image';
    this.seoKeywords = '';
    this.theme = 'dark';
    this.isExporting = false;
  }
}

const state = new AppState();

// DOM Elements
const dropZone = document.getElementById('dropZone');
const queueContainer = document.getElementById('queueContainer');
const queueList = document.getElementById('queueList');
const queueCount = document.getElementById('queueCount');
const queueSizeStats = document.getElementById('queueSizeStats');
const btnClearAll = document.getElementById('btnClearAll');
const btnAddMore = document.getElementById('btnAddMore');

const fileInput = document.getElementById('fileInput');
const folderInput = document.getElementById('folderInput');
const btnSelectFiles = document.getElementById('btnSelectFiles');
const btnSelectFolder = document.getElementById('btnSelectFolder');

const btnConvertMain = document.getElementById('btnConvertMain');
const btnSubStats = document.getElementById('btnSubStats');

const qualityRange = document.getElementById('qualityRange');
const qualityValText = document.getElementById('qualityValText');

const formatDesc = document.getElementById('formatDesc');
const namingPreview = document.getElementById('namingPreview');

const btnThemeToggle = document.getElementById('btnThemeToggle');
const helpModal = document.getElementById('helpModal');
const btnHelp = document.getElementById('btnHelp');
const btnCloseHelp = document.getElementById('btnCloseHelp');

// Comparison Elements
const compFileName = document.getElementById('compFileName');
const compDimensions = document.getElementById('compDimensions');
const imgLeft = document.getElementById('imgLeft');
const imgRight = document.getElementById('imgRight');
const splitHandle = document.getElementById('splitHandle');
const splitContainer = document.getElementById('splitContainer');
const optBadge = document.getElementById('optBadge');
const statOrigSize = document.getElementById('statOrigSize');
const statOrigDim = document.getElementById('statOrigDim');
const statOptSize = document.getElementById('statOptSize');
const statOptDim = document.getElementById('statOptDim');
const statSavingsTag = document.getElementById('statSavingsTag');

// Init - Start with empty items array
document.addEventListener('DOMContentLoaded', () => {
  if (window.pdfjsLib) {
    window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
  }
  state.theme = localStorage.getItem('imageConverterTheme') ||
    (window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark');
  document.body.setAttribute('data-theme', state.theme);
  btnThemeToggle.textContent = state.theme === 'dark' ? '☀️' : '🌙';
  state.items = [];
  state.selectedItemId = null;
  renderAll();

  setupEventListeners();
  setupSplitSlider();
});

function setupEventListeners() {
  // Format Pills & Dropdown
  const btnMoreFormat = document.getElementById('btnMoreFormat');
  const moreFormatMenu = document.getElementById('moreFormatMenu');

  if (btnMoreFormat && moreFormatMenu) {
    btnMoreFormat.addEventListener('click', (e) => {
      e.stopPropagation();
      moreFormatMenu.classList.toggle('hidden');
    });

    document.querySelectorAll('.dropdown-item').forEach(itemBtn => {
      itemBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        document.querySelectorAll('.pill-btn[data-format]').forEach(b => b.classList.remove('active'));
        btnMoreFormat.classList.add('active');

        state.format = itemBtn.dataset.format;
        btnMoreFormat.textContent = `${state.format.toUpperCase()} ∨`;
        moreFormatMenu.classList.add('hidden');
        updateFormatDesc();
        renderAll();
      });
    });

    document.addEventListener('click', () => {
      if (moreFormatMenu) moreFormatMenu.classList.add('hidden');
    });
  }

  document.querySelectorAll('.pill-btn[data-format]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.pill-btn[data-format]').forEach(b => b.classList.remove('active'));
      if (btnMoreFormat) {
        btnMoreFormat.classList.remove('active');
        btnMoreFormat.textContent = 'More ∨';
      }
      btn.classList.add('active');
      state.format = btn.dataset.format;
      updateFormatDesc();
      renderAll();
    });
  });

  // Quality Preset Pills
  document.querySelectorAll('[data-qpreset]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('[data-qpreset]').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      if (btn.dataset.qpreset === 'smaller') state.quality = 0.60;
      if (btn.dataset.qpreset === 'balanced') state.quality = 0.85;
      if (btn.dataset.qpreset === 'high') state.quality = 0.95;
      qualityRange.value = Math.round(state.quality * 100);
      qualityValText.textContent = `${qualityRange.value}%`;
      renderAll();
    });
  });

  qualityRange.addEventListener('input', (e) => {
    const val = parseInt(e.target.value);
    state.quality = val / 100.0;
    qualityValText.textContent = `${val}%`;
    document.querySelectorAll('[data-qpreset]').forEach(b => b.classList.remove('active'));
    renderAll();
  });

  // Resize Image Pills
  document.querySelectorAll('[data-width]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('[data-width]').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      state.widthOption = btn.dataset.width;
      const customRow = document.getElementById('customWidthInputRow');
      if (state.widthOption === 'custom') customRow.classList.remove('hidden');
      else customRow.classList.add('hidden');
      renderAll();
    });
  });

  document.getElementById('customWidthVal').addEventListener('input', (e) => {
    state.customWidthVal = Math.max(10, parseInt(e.target.value) || 500);
    renderAll();
  });

  // File Naming Pills
  document.querySelectorAll('[data-naming]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('[data-naming]').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      state.namingMode = btn.dataset.naming;

      document.getElementById('seqInputBox').classList.toggle('hidden', state.namingMode !== 'sequence');
      document.getElementById('seoInputBox').classList.toggle('hidden', state.namingMode !== 'seo');
      renderAll();
    });
  });

  document.getElementById('seqBaseText').addEventListener('input', (e) => {
    state.seqBase = e.target.value || 'image';
    renderAll();
  });

  document.getElementById('seoText').addEventListener('input', (e) => {
    state.seoKeywords = e.target.value;
    renderAll();
  });

  // File Input Pickers
  btnSelectFiles.addEventListener('click', () => fileInput.click());
  btnSelectFolder.addEventListener('click', () => folderInput.click());
  fileInput.addEventListener('change', (e) => handleUserFiles(e.target.files));
  folderInput.addEventListener('change', (e) => handleUserFiles(e.target.files));

  dropZone.addEventListener('click', (e) => {
    if (!e.target.closest('button')) fileInput.click();
  });
  dropZone.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      fileInput.click();
    }
  });

  // Drop Zone Drag & Drop
  dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('drag-over'); });
  dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
  dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('drag-over');
    if (e.dataTransfer.files) handleUserFiles(e.dataTransfer.files);
  });

  btnClearAll.addEventListener('click', () => {
    state.items = [];
    state.selectedItemId = null;
    renderAll();
  });
  btnAddMore.addEventListener('click', () => fileInput.click());

  // Theme Toggle
  btnThemeToggle.addEventListener('click', () => {
    state.theme = state.theme === 'dark' ? 'light' : 'dark';
    document.body.setAttribute('data-theme', state.theme);
    btnThemeToggle.textContent = state.theme === 'dark' ? '☀️' : '🌙';
    localStorage.setItem('imageConverterTheme', state.theme);
  });

  // Help Modal
  btnHelp.addEventListener('click', () => helpModal.classList.remove('hidden'));
  btnCloseHelp.addEventListener('click', () => helpModal.classList.add('hidden'));
  helpModal.addEventListener('click', (e) => {
    if (e.target === helpModal) helpModal.classList.add('hidden');
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') helpModal.classList.add('hidden');
  });

  const checkboxBindings = {
    chkNoUpscale: 'noUpscale',
    chkOptimizeWeb: 'optimizeWeb',
    chkRemoveMetadata: 'removeMetadata',
    chkPreserveTrans: 'preserveTrans',
    chkProgressiveJpeg: 'progressiveJpeg'
  };
  Object.entries(checkboxBindings).forEach(([id, key]) => {
    document.getElementById(id).addEventListener('change', (e) => {
      state[key] = e.target.checked;
      renderAll();
    });
  });

  // Main Batch Convert Action
  btnConvertMain.addEventListener('click', downloadBatchZip);
}

async function handleUserFiles(fileList) {
  for (const file of fileList) {
    if (file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf')) {
      try {
        const pdfDoc = await PDFLib.PDFDocument.load(await file.arrayBuffer(), { updateMetadata: false });
        const pages = pdfDoc.getPages();
        const firstPage = pages[0];
        const pageSize = firstPage ? firstPage.getSize() : { width: 0, height: 0 };
        const pdfPreview = `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(`
          <svg xmlns="http://www.w3.org/2000/svg" width="640" height="420" viewBox="0 0 640 420">
            <rect width="640" height="420" fill="#f3f4f6"/>
            <rect x="220" y="70" width="200" height="280" rx="12" fill="#ffffff" stroke="#d1d5db"/>
            <path d="M350 70v70h70" fill="#fee2e2" stroke="#d1d5db"/>
            <rect x="255" y="205" width="130" height="62" rx="8" fill="#dc2626"/>
            <text x="320" y="247" text-anchor="middle" font-family="Arial, sans-serif" font-size="34" font-weight="700" fill="#ffffff">PDF</text>
            <text x="320" y="305" text-anchor="middle" font-family="Arial, sans-serif" font-size="18" fill="#4b5563">${pages.length} ${pages.length === 1 ? 'page' : 'pages'}</text>
          </svg>`)} `;
        const item = {
          id: Math.random().toString(36).substring(2),
          name: file.name,
          format: 'PDF',
          origW: Math.round(pageSize.width),
          origH: Math.round(pageSize.height),
          pageCount: pages.length,
          origSize: file.size,
          src: pdfPreview.trim(),
          file,
          isPdf: true
        };
        state.items.push(item);
        if (!state.selectedItemId) state.selectedItemId = item.id;
        selectPdfOutput();
        renderAll();
      } catch (error) {
        window.alert(`Could not open ${file.name}. The PDF may be encrypted or damaged.`);
      }
      continue;
    }

    if (file.type.startsWith('image/')) {
      const url = URL.createObjectURL(file);
      const img = new Image();
      img.onload = () => {
        const item = {
          id: Math.random().toString(36).substring(2),
          name: file.name,
          format: file.name.split('.').pop().toUpperCase(),
          origW: img.naturalWidth || 1920,
          origH: img.naturalHeight || 1080,
          origSize: file.size,
          src: url,
          file: file,
          isPdf: false
        };
        state.items.push(item);
        if (!state.selectedItemId) state.selectedItemId = item.id;
        renderAll();
      };
      img.src = url;
    }
  }
}

function selectPdfOutput() {
  state.format = 'pdf';
  document.querySelectorAll('.pill-btn[data-format]').forEach(button => button.classList.remove('active'));
  const moreButton = document.getElementById('btnMoreFormat');
  const moreMenu = document.getElementById('moreFormatMenu');
  if (moreButton) {
    moreButton.classList.add('active');
    moreButton.textContent = 'PDF ∨';
  }
  if (moreMenu) moreMenu.classList.add('hidden');
  updateFormatDesc();
}

function updateFormatDesc() {
  const descs = {
    webp: 'WebP — Best for websites (smaller size, great quality)',
    avif: 'AVIF — Modern next-gen image compression format',
    jpg: 'JPG — Standard photo format for web & print',
    png: 'PNG — Lossless format with full transparency support',
    pdf: 'PDF — Optimized document vector & raster PDF format',
    bmp: 'BMP — Bitmap image format',
    gif: 'GIF — Graphics image format',
    tiff: 'TIFF — High quality uncompressed image format',
    heic: 'HEIC — Apple High Efficiency image format'
  };
  formatDesc.textContent = descs[state.format] || descs.webp;
}

function computeEstimatedSize(origBytes, quality, item = null) {
  if (item?.isPdf) {
    const pdfFactor = Math.min(0.85, Math.max(0.35, quality * 0.70));
    return Math.round(origBytes * pdfFactor);
  }
  let factor = 0.20;
  if (state.format === 'jpg') factor = 0.35;
  if (state.format === 'png') factor = 0.70;
  if (state.format === 'avif') factor = 0.15;
  if (state.format === 'pdf') factor = 0.40;

  factor = factor * (quality / 0.85);
  return Math.round(origBytes * Math.max(0.08, factor));
}

function formatBytes(bytes) {
  if (bytes === 0) return '0 B';
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(0)} KB`;
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

function sanitizedSEOKeywords() {
  const lines = state.seoKeywords.split('\n');
  const result = [];
  for (let line of lines) {
    let trimmed = line.trim();
    if (!trimmed) continue;
    while (trimmed.startsWith('*') || trimmed.startsWith('-') || trimmed.startsWith('•') || trimmed.startsWith('#')) {
      trimmed = trimmed.substring(1).trim();
    }
    trimmed = trimmed.replace(/^\d+[\.\)\-]?\s*/, '').trim();
    if (!trimmed) continue;

    const slug = trimmed.toLowerCase()
      .replace(/[^a-z0-9\s\-]/g, '')
      .replace(/[\s_]+/g, '-')
      .replace(/-+/g, '-')
      .replace(/^-|-$/g, '');

    if (slug) result.push(slug);
  }
  return result;
}

function getProjectedName(item, index) {
  let baseName = item.name.replace(/\.[^/.]+$/, "");
  if (state.namingMode === 'sequence') {
    baseName = `${state.seqBase}_${(index + 1).toString().padStart(3, '0')}`;
  } else if (state.namingMode === 'seo') {
    const list = sanitizedSEOKeywords();
    if (list.length > 0) {
      const kIdx = index % list.length;
      const rep = Math.floor(index / list.length);
      baseName = rep > 0 ? `${list[kIdx]}-${rep + 1}` : list[kIdx];
    } else {
      baseName = `seo-image-${index + 1}`;
    }
  }
  return `${baseName}.${item.isPdf ? 'pdf' : state.format}`;
}

function renderAll() {
  if (state.items.length === 0) {
    dropZone.classList.remove('hidden');
    queueContainer.classList.add('hidden');
    document.getElementById('comparisonCard').classList.add('hidden');
    btnConvertMain.disabled = true;
    document.getElementById('btnConvertLabel').innerHTML = 'Optimize Files &amp; Download ZIP <b aria-hidden="true">→</b>';
    btnSubStats.textContent = 'Add images to start converting';
    return;
  }

  dropZone.classList.add('hidden');
  queueContainer.classList.remove('hidden');
  document.getElementById('comparisonCard').classList.remove('hidden');
  btnConvertMain.disabled = false;

  // Render Queue Header Stats
  let totalOrig = 0;
  let totalEst = 0;
  state.items.forEach(item => {
    totalOrig += item.origSize;
    totalEst += computeEstimatedSize(item.origSize, state.quality, item);
  });

  const totalPct = Math.round((1 - (totalEst / totalOrig)) * 100);
  queueCount.textContent = `${state.items.length} ${state.items.length === 1 ? 'File' : 'Files'}`;
  queueSizeStats.innerHTML = `Total: ${formatBytes(totalOrig)} → ${formatBytes(totalEst)} (<span class="summary-savings">${totalPct}% smaller</span>)`;

  document.getElementById('btnConvertLabel').innerHTML = `Optimize ${state.items.length} ${state.items.length === 1 ? 'File' : 'Files'} &amp; Download ZIP <b aria-hidden="true">→</b>`;
  btnSubStats.textContent = `Estimated size: ${formatBytes(totalEst)} (${totalPct}% smaller)`;

  // Render Queue Items List
  queueList.innerHTML = '';
  state.items.forEach((item, idx) => {
    const estSize = computeEstimatedSize(item.origSize, state.quality, item);
    const pct = Math.round((1 - (estSize / item.origSize)) * 100);

    const div = document.createElement('div');
    div.className = `queue-item-row ${item.id === state.selectedItemId ? 'selected' : ''}`;
    div.onclick = () => {
      state.selectedItemId = item.id;
      renderAll();
    };

    div.innerHTML = `
      <img src="${item.src}" class="item-thumb" alt="thumb">
      <div class="item-main-info">
        <div class="item-name-row">
          <span class="item-filename">${item.name}</span>
          <span class="format-pill">${item.format}</span>
        </div>
        <div class="item-details-row">
          <span>${item.isPdf ? `${item.pageCount} ${item.pageCount === 1 ? 'page' : 'pages'}` : `${item.origW} × ${item.origH}`}</span>
          <span>•</span>
          <span>${formatBytes(item.origSize)} Original</span>
          <span class="size-arrow">→</span>
          <span style="color: #60a5fa; font-weight: 600;">${formatBytes(estSize)} Est.</span>
        </div>
      </div>
      <span class="savings-pill">${pct}% smaller</span>
      <span class="output-pill">${item.isPdf ? 'PDF' : state.format.toUpperCase()}</span>
      <button class="btn-item-remove" onclick="event.stopPropagation(); removeQueueItem('${item.id}')">✕</button>
    `;
    queueList.appendChild(div);
  });

  // Render Selected Comparison Card
  const activeItem = state.items.find(x => x.id === state.selectedItemId) || state.items[0];
  if (activeItem) {
    const activeEst = computeEstimatedSize(activeItem.origSize, state.quality, activeItem);
    const activePct = Math.round((1 - (activeEst / activeItem.origSize)) * 100);

    compFileName.textContent = activeItem.name;
    const activeDimensions = activeItem.isPdf
      ? `${activeItem.pageCount} ${activeItem.pageCount === 1 ? 'page' : 'pages'}`
      : `${activeItem.origW} × ${activeItem.origH}`;
    compDimensions.textContent = ` • ${activeDimensions} • ${formatBytes(activeItem.origSize)}`;

    imgLeft.src = activeItem.src;

    if (activeItem.isPdf) {
      imgRight.src = activeItem.src;
    } else {
      // Render live compressed canvas image for right side
      convertItemToBlob(activeItem).then(blob => {
        imgRight.src = URL.createObjectURL(blob);
      });
    }

    optBadge.textContent = activeItem.isPdf
      ? 'Optimized PDF'
      : `Optimized (${state.format.toUpperCase()}, ${Math.round(state.quality * 100)}%)`;

    statOrigSize.textContent = formatBytes(activeItem.origSize);
    statOrigDim.textContent = activeDimensions;

    statOptSize.textContent = formatBytes(activeEst);
    statOptDim.textContent = activeDimensions;
    statSavingsTag.textContent = `🟢 ${activePct}% smaller`;
  }

  namingPreview.textContent = `Example: ${getProjectedName(state.items[0], 0)}`;
}

window.removeQueueItem = function(id) {
  state.items = state.items.filter(x => x.id !== id);
  if (state.selectedItemId === id) {
    state.selectedItemId = state.items.length > 0 ? state.items[0].id : null;
  }
  renderAll();
};

// Interactive Before / After Split Slider Handling (Pixel-perfect clip-path)
function setupSplitSlider() {
  const updateSliderPos = (x) => {
    const rect = splitContainer.getBoundingClientRect();
    let pos = (x - rect.left) / rect.width;
    pos = Math.max(0, Math.min(1, pos));
    const pct = (pos * 100).toFixed(1);
    splitContainer.style.setProperty('--split-percent', `${pct}%`);
    splitHandle.style.left = `${pct}%`;
  };

  splitContainer.addEventListener('pointerdown', (e) => {
    splitContainer.setPointerCapture(e.pointerId);
    updateSliderPos(e.clientX);
  });
  splitContainer.addEventListener('pointermove', (e) => {
    if (splitContainer.hasPointerCapture(e.pointerId)) updateSliderPos(e.clientX);
  });
}

// Download Batch ZIP
async function downloadBatchZip() {
  if (state.items.length === 0 || state.isExporting) return;
  state.isExporting = true;
  btnConvertMain.disabled = true;
  document.getElementById('btnConvertLabel').textContent = 'Processing & Packaging ZIP…';

  const zip = new JSZip();

  for (let i = 0; i < state.items.length; i++) {
    const item = state.items[i];
    const outName = getProjectedName(item, i);
    const blob = await convertItemToBlob(item);
    zip.file(outName, blob);
  }

  const zipBlob = await zip.generateAsync({ type: 'blob' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(zipBlob);
  a.download = `Optimized_Files_${new Date().toISOString().slice(0, 10)}.zip`;
  a.click();

  state.isExporting = false;
  btnConvertMain.disabled = false;
  renderAll();
}

async function convertItemToBlob(item) {
  if (item.isPdf) {
    return compressPdfToBlob(item);
  }

  return new Promise((resolve) => {
    const img = new Image();
    img.onload = () => {
      const canvas = document.createElement('canvas');
      canvas.width = item.origW;
      canvas.height = item.origH;
      const ctx = canvas.getContext('2d');
      ctx.fillStyle = '#ffffff';
      ctx.fillRect(0, 0, canvas.width, canvas.height);
      ctx.drawImage(img, 0, 0);

      if (state.format === 'pdf') {
        canvas.toBlob(async jpegBlob => {
          if (!jpegBlob) {
            resolve(item.file);
            return;
          }
          const pdfDoc = await PDFLib.PDFDocument.create();
          const embeddedImage = await pdfDoc.embedJpg(await jpegBlob.arrayBuffer());
          const pdfPage = pdfDoc.addPage([canvas.width, canvas.height]);
          pdfPage.drawImage(embeddedImage, {
            x: 0,
            y: 0,
            width: canvas.width,
            height: canvas.height
          });
          const pdfBytes = await pdfDoc.save({ useObjectStreams: true });
          resolve(new Blob([pdfBytes], { type: 'application/pdf' }));
        }, 'image/jpeg', state.quality);
        return;
      }

      let mime = 'image/webp';
      if (state.format === 'jpg') mime = 'image/jpeg';
      if (state.format === 'png') mime = 'image/png';

      canvas.toBlob((b) => resolve(b), mime, state.quality);
    };
    img.src = item.src;
  });
}

async function compressPdfToBlob(item) {
  const sourceBytes = new Uint8Array(await item.file.arrayBuffer());

  if (!window.pdfjsLib) {
    return new Blob([sourceBytes], { type: 'application/pdf' });
  }

  const sourcePdf = await window.pdfjsLib.getDocument({ data: sourceBytes }).promise;
  const outputPdf = await PDFLib.PDFDocument.create();
  const jpegQuality = Math.min(0.82, Math.max(0.42, state.quality * 0.78));

  for (let pageNumber = 1; pageNumber <= sourcePdf.numPages; pageNumber++) {
    const sourcePage = await sourcePdf.getPage(pageNumber);
    const baseViewport = sourcePage.getViewport({ scale: 1 });
    let targetWidth = Math.round(baseViewport.width * 1.35);

    if (state.widthOption === 'custom') targetWidth = state.customWidthVal;
    if (['1920', '1600', '1200'].includes(state.widthOption)) targetWidth = Number(state.widthOption);

    let renderScale = targetWidth / baseViewport.width;
    if (state.noUpscale) renderScale = Math.min(renderScale, 1.35);
    renderScale = Math.max(0.65, renderScale);

    const viewport = sourcePage.getViewport({ scale: renderScale });
    const canvas = document.createElement('canvas');
    canvas.width = Math.max(1, Math.round(viewport.width));
    canvas.height = Math.max(1, Math.round(viewport.height));
    const context = canvas.getContext('2d', { alpha: false });
    context.fillStyle = '#ffffff';
    context.fillRect(0, 0, canvas.width, canvas.height);

    await sourcePage.render({ canvasContext: context, viewport }).promise;
    const pageBlob = await new Promise((resolve, reject) => {
      canvas.toBlob(
        blob => blob ? resolve(blob) : reject(new Error('PDF page compression failed')),
        'image/jpeg',
        jpegQuality
      );
    });

    const embeddedPage = await outputPdf.embedJpg(await pageBlob.arrayBuffer());
    const outputWidth = viewport.width / renderScale;
    const outputHeight = viewport.height / renderScale;
    const outputPage = outputPdf.addPage([outputWidth, outputHeight]);
    outputPage.drawImage(embeddedPage, {
      x: 0,
      y: 0,
      width: outputWidth,
      height: outputHeight
    });

    canvas.width = 1;
    canvas.height = 1;
    sourcePage.cleanup();
  }

  if (!state.removeMetadata) {
    try {
      const metadataSource = await PDFLib.PDFDocument.load(sourceBytes, { updateMetadata: false });
      outputPdf.setTitle(metadataSource.getTitle() || '');
      outputPdf.setAuthor(metadataSource.getAuthor() || '');
      outputPdf.setSubject(metadataSource.getSubject() || '');
      outputPdf.setKeywords(metadataSource.getKeywords() || []);
      outputPdf.setCreator(metadataSource.getCreator() || '');
      outputPdf.setProducer(metadataSource.getProducer() || '');
    } catch (_) {
      // Compression can continue even when optional metadata cannot be read.
    }
  }

  const compressedBytes = await outputPdf.save({ useObjectStreams: true, addDefaultPage: false });
  await sourcePdf.destroy();

  if (compressedBytes.length >= sourceBytes.length) {
    return new Blob([sourceBytes], { type: 'application/pdf' });
  }

  return new Blob([compressedBytes], { type: 'application/pdf' });
}
