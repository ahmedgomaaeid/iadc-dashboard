<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $magazine->name }} - IADC</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/brand/logo-2.svg') }}" />
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Feather Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.29.0/feather.min.css">
    
    <!-- PDF.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    
    <!-- StPageFlip -->
    <script src="https://cdn.jsdelivr.net/npm/page-flip@2.0.7/dist/js/page-flip.browser.js"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(180deg, #f5f7fa 0%, #e4e8ed 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Top Bar */
        .top-bar {
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            padding: 15px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .top-bar-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .back-link {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .back-link:hover {
            color: #1e293b;
        }
        
        .divider {
            width: 1px;
            height: 20px;
            background: #e2e8f0;
        }
        
        .mag-title {
            color: #1e293b;
            font-size: 16px;
            font-weight: 600;
        }
        
        .top-bar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .zoom-controls {
            display: flex;
            align-items: center;
            gap: 4px;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 10px;
        }
        
        .zoom-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            background: transparent;
            color: #64748b;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .zoom-btn:hover {
            background: #fff;
            color: #ab1f2e;
        }
        
        .zoom-level {
            font-size: 12px;
            color: #64748b;
            min-width: 45px;
            text-align: center;
            font-weight: 500;
        }
        
        .icon-btn {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #64748b;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .icon-btn:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            color: #1e293b;
        }
        
        .download-btn {
            padding: 10px 20px;
            border-radius: 10px;
            border: none;
            background: #ab1f2e;
            color: #fff;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(171, 31, 46, 0.35);
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
            position: relative;
            overflow: auto;
        }
        
        /* Book Container */
        .book-container {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .flipbook-wrapper {
            position: relative;
            box-shadow: 
                0 25px 60px rgba(0,0,0,0.15),
                0 10px 25px rgba(0,0,0,0.1);
            border-radius: 4px;
            transition: transform 0.3s ease;
            transform-origin: center center;
        }
        
        .flipbook {
            background: transparent;
        }
        
        .page {
            background: #fff;
            overflow: hidden;
        }
        
        .page canvas {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .page-cover {
            background: #ab1f2e;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #fff;
            text-align: center;
            padding: 40px;
        }
        
        .page-cover h2 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .page-cover p {
            font-size: 14px;
            opacity: 0.8;
        }
        
        /* Navigation Arrows */
        .nav-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #64748b;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 100;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .nav-arrow:hover {
            background: #ab1f2e;
            border-color: #ab1f2e;
            color: #fff;
            transform: translateY(-50%) scale(1.1);
        }
        
        .nav-arrow:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            background: #f1f5f9;
        }
        
        .nav-arrow:disabled:hover {
            transform: translateY(-50%);
            background: #f1f5f9;
            border-color: #e2e8f0;
            color: #64748b;
        }
        
        .nav-arrow.prev {
            left: 30px;
        }
        
        .nav-arrow.next {
            right: 30px;
        }
        
        .nav-arrow svg {
            width: 24px;
            height: 24px;
        }
        
        /* Bottom Bar */
        .bottom-bar {
            background: #fff;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.08);
            padding: 15px 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }
        
        .page-indicator {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .page-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #64748b;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .page-btn:hover:not(:disabled) {
            background: #f1f5f9;
            color: #ab1f2e;
        }
        
        .page-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }
        
        .page-text {
            color: #64748b;
            font-size: 14px;
        }
        
        .page-text span {
            color: #ab1f2e;
            font-weight: 600;
        }
        
        .page-slider {
            width: 200px;
            -webkit-appearance: none;
            height: 6px;
            border-radius: 3px;
            background: #e2e8f0;
            outline: none;
        }
        
        .page-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #ab1f2e;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 2px 8px rgba(171, 31, 46, 0.4);
        }
        
        .page-slider::-webkit-slider-thumb:hover {
            transform: scale(1.15);
        }
        
        /* Loading */
        .loading-screen {
            position: fixed;
            inset: 0;
            background: linear-gradient(180deg, #f5f7fa 0%, #e4e8ed 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 25px;
            z-index: 9999;
            transition: opacity 0.5s ease;
        }
        
        .loading-screen.hidden {
            opacity: 0;
            pointer-events: none;
        }
        
        .loader {
            width: 60px;
            height: 60px;
            border: 3px solid #e2e8f0;
            border-top-color: #ab1f2e;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .loading-text {
            color: #64748b;
            font-size: 14px;
        }
        
        .loading-progress {
            width: 200px;
            height: 6px;
            background: #e2e8f0;
            border-radius: 3px;
            overflow: hidden;
        }
        
        .loading-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #ab1f2e, #ab1f2e);
            width: 0%;
            transition: width 0.3s;
            border-radius: 3px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .top-bar {
                padding: 12px 15px;
            }
            
            .mag-title {
                display: none;
            }
            
            .divider {
                display: none;
            }
            
            .download-btn span {
                display: none;
            }
            
            .download-btn {
                padding: 10px;
            }
            
            .main-content {
                padding: 15px;
            }
            
            .nav-arrow {
                width: 40px;
                height: 40px;
            }
            
            .nav-arrow.prev {
                left: 10px;
            }
            
            .nav-arrow.next {
                right: 10px;
            }
            
            .bottom-bar {
                padding: 12px 15px;
            }
            
            .page-slider {
                width: 120px;
            }
        }
    </style>
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading-screen" id="loadingScreen">
        <div class="loader"></div>
        <div class="loading-text">Loading Magazine...</div>
        <div class="loading-progress">
            <div class="loading-progress-bar" id="progressBar"></div>
        </div>
    </div>

    <!-- Top Bar -->
    <div class="top-bar">
        <div class="top-bar-left">
            <a href="{{ route('landing') }}" class="back-link">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Back
            </a>
            <div class="divider"></div>
            <div class="mag-title">{{ $magazine->name }}</div>
        </div>
        <div class="top-bar-right">
            <div class="zoom-controls">
                <button class="zoom-btn" id="zoomOutBtn" title="Zoom Out">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="M21 21l-4.35-4.35M8 11h6"/>
                    </svg>
                </button>
                <span class="zoom-level" id="zoomLevel">100%</span>
                <button class="zoom-btn" id="zoomInBtn" title="Zoom In">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="M21 21l-4.35-4.35M11 8v6M8 11h6"/>
                    </svg>
                </button>
            </div>
            <button class="icon-btn" id="fullscreenBtn" title="Fullscreen">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/>
                </svg>
            </button>
            <a href="{{ asset('storage/' . $magazine->pdf_file) }}" download class="download-btn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>
                </svg>
                <span>Download PDF</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <button class="nav-arrow prev" id="prevBtn" disabled>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M15 18l-6-6 6-6"/>
            </svg>
        </button>
        
        <div class="book-container">
            <div class="flipbook-wrapper">
                <div id="flipbook" class="flipbook"></div>
            </div>
        </div>
        
        <button class="nav-arrow next" id="nextBtn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 18l6-6-6-6"/>
            </svg>
        </button>
    </div>

    <!-- Bottom Bar -->
    <div class="bottom-bar">
        <div class="page-indicator">
            <button class="page-btn" id="firstPage" title="First Page">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 17l-5-5 5-5M18 17l-5-5 5-5"/>
                </svg>
            </button>
            <div class="page-text">
                Page <span id="currentPage">1</span> of <span id="totalPages">-</span>
            </div>
            <button class="page-btn" id="lastPage" title="Last Page">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M13 17l5-5-5-5M6 17l5-5-5-5"/>
                </svg>
            </button>
        </div>
        <input type="range" class="page-slider" id="pageSlider" min="0" value="0">
    </div>

    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        
        const pdfUrl = "{{ asset('storage/' . $magazine->pdf_file) }}";
        let pageFlip = null;
        let pdfDoc = null;
        let totalPages = 0;
        
        const flipbookEl = document.getElementById('flipbook');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const firstPageBtn = document.getElementById('firstPage');
        const lastPageBtn = document.getElementById('lastPage');
        const currentPageEl = document.getElementById('currentPage');
        const totalPagesEl = document.getElementById('totalPages');
        const pageSlider = document.getElementById('pageSlider');
        const loadingScreen = document.getElementById('loadingScreen');
        const progressBar = document.getElementById('progressBar');
        const fullscreenBtn = document.getElementById('fullscreenBtn');
        
        let currentZoom = 1;
        const minZoom = 0.5;
        const maxZoom = 2;
        const zoomStep = 0.25;
        
        // Check if screen is wide enough for 2-page spread
        function isWideScreen() {
            return window.innerWidth >= 1200;
        }
        
        // Calculate book dimensions for 80vh max height
        function getBookDimensions() {
            const maxHeight = window.innerHeight * 0.7;
            const aspectRatio = 0.7; // Typical book aspect ratio
            const availableWidth = isWideScreen() ? (window.innerWidth - 200) / 2 : window.innerWidth - 150;
            const pageWidth = Math.min(maxHeight * aspectRatio, availableWidth / (isWideScreen() ? 2 : 1));
            const pageHeight = pageWidth / aspectRatio;
            
            return {
                width: Math.floor(pageWidth * currentZoom),
                height: Math.floor(Math.min(pageHeight, maxHeight) * currentZoom)
            };
        }
        
        // Render PDF page to canvas
        async function renderPageToCanvas(pageNum, width, height) {
            const page = await pdfDoc.getPage(pageNum);
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            
            const viewport = page.getViewport({ scale: 1 });
            const scale = Math.min(width / viewport.width, height / viewport.height) * 2;
            const scaledViewport = page.getViewport({ scale });
            
            canvas.width = scaledViewport.width;
            canvas.height = scaledViewport.height;
            canvas.style.width = '100%';
            canvas.style.height = '100%';
            
            await page.render({
                canvasContext: ctx,
                viewport: scaledViewport
            }).promise;
            
            return canvas;
        }
        
        // Initialize the flipbook
        async function initFlipbook() {
            try {
                // Load PDF
                const loadingTask = pdfjsLib.getDocument(pdfUrl);
                
                loadingTask.onProgress = function(progress) {
                    if (progress.total > 0) {
                        const percent = (progress.loaded / progress.total) * 50;
                        progressBar.style.width = percent + '%';
                    }
                };
                
                pdfDoc = await loadingTask.promise;
                totalPages = pdfDoc.numPages;
                totalPagesEl.textContent = totalPages;
                pageSlider.max = totalPages - 1;
                
                const dims = getBookDimensions();
                
                // Initialize PageFlip - use 2-page spread on wide screens
                const useDoublePages = isWideScreen();
                
                pageFlip = new St.PageFlip(flipbookEl, {
                    width: dims.width,
                    height: dims.height,
                    size: 'fixed',
                    minWidth: 250,
                    maxWidth: 800,
                    minHeight: 350,
                    maxHeight: 1200,
                    showCover: true,
                    mobileScrollSupport: false,
                    swipeDistance: 30,
                    flippingTime: 800,
                    usePortrait: !useDoublePages,
                    startZIndex: 0,
                    autoSize: true,
                    maxShadowOpacity: 0.3,
                    drawShadow: true
                });
                
                // Load pages
                const pages = [];
                for (let i = 1; i <= totalPages; i++) {
                    const pageDiv = document.createElement('div');
                    pageDiv.className = 'page';
                    pageDiv.style.background = '#fff';
                    
                    const canvas = await renderPageToCanvas(i, dims.width, dims.height);
                    pageDiv.appendChild(canvas);
                    pages.push(pageDiv);
                    
                    // Update progress
                    const percent = 50 + ((i / totalPages) * 50);
                    progressBar.style.width = percent + '%';
                }
                
                pageFlip.loadFromHTML(pages);
                
                // Event listeners
                pageFlip.on('flip', (e) => {
                    updatePageInfo(e.data);
                });
                
                updatePageInfo(0);
                
                // Hide loading
                setTimeout(() => {
                    loadingScreen.classList.add('hidden');
                }, 500);
                
            } catch (error) {
                console.error('Error:', error);
                loadingScreen.innerHTML = `
                    <div style="color: #ef4444; text-align: center;">
                        <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <div style="margin-top: 20px; font-size: 16px; color: #1e293b;">Failed to load magazine</div>
                        <a href="{{ route('landing') }}" style="color: #6366f1; margin-top: 15px; display: inline-block;">Go Back</a>
                    </div>
                `;
            }
        }
        
        function updatePageInfo(pageIndex) {
            const currentPage = pageIndex + 1;
            currentPageEl.textContent = currentPage;
            pageSlider.value = pageIndex;
            
            prevBtn.disabled = pageIndex === 0;
            nextBtn.disabled = pageIndex >= totalPages - 1;
            firstPageBtn.disabled = pageIndex === 0;
            lastPageBtn.disabled = pageIndex >= totalPages - 1;
        }
        
        // Navigation
        prevBtn.addEventListener('click', () => pageFlip.flipPrev());
        nextBtn.addEventListener('click', () => pageFlip.flipNext());
        firstPageBtn.addEventListener('click', () => pageFlip.flip(0));
        lastPageBtn.addEventListener('click', () => pageFlip.flip(totalPages - 1));
        
        pageSlider.addEventListener('input', (e) => {
            pageFlip.flip(parseInt(e.target.value));
        });
        
        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') pageFlip.flipPrev();
            if (e.key === 'ArrowRight') pageFlip.flipNext();
            if (e.key === 'Home') pageFlip.flip(0);
            if (e.key === 'End') pageFlip.flip(totalPages - 1);
        });
        
        // Fullscreen
        fullscreenBtn.addEventListener('click', () => {
            if (document.fullscreenElement) {
                document.exitFullscreen();
            } else {
                document.documentElement.requestFullscreen();
            }
        });
        
        // Zoom controls
        const zoomInBtn = document.getElementById('zoomInBtn');
        const zoomOutBtn = document.getElementById('zoomOutBtn');
        const zoomLevelEl = document.getElementById('zoomLevel');
        
        function updateZoomLevel() {
            zoomLevelEl.textContent = Math.round(currentZoom * 100) + '%';
        }
        
        zoomInBtn.addEventListener('click', () => {
            if (currentZoom < maxZoom) {
                currentZoom = Math.min(maxZoom, currentZoom + zoomStep);
                updateZoomLevel();
                // Apply zoom via CSS transform for smoother experience
                document.querySelector('.flipbook-wrapper').style.transform = `scale(${currentZoom})`;
            }
        });
        
        zoomOutBtn.addEventListener('click', () => {
            if (currentZoom > minZoom) {
                currentZoom = Math.max(minZoom, currentZoom - zoomStep);
                updateZoomLevel();
                document.querySelector('.flipbook-wrapper').style.transform = `scale(${currentZoom})`;
            }
        });
        
        // Add keyboard shortcuts for zoom
        document.addEventListener('keydown', (e) => {
            if (e.key === '+' || e.key === '=') {
                zoomInBtn.click();
            }
            if (e.key === '-') {
                zoomOutBtn.click();
            }
            if (e.key === '0') {
                currentZoom = 1;
                updateZoomLevel();
                document.querySelector('.flipbook-wrapper').style.transform = `scale(1)`;
            }
        });
        
        // Initialize
        initFlipbook();
    </script>
</body>
</html>
