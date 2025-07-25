@extends('index')

@section('title', 'Add Posting - PublicForum')

@section('content')
  <div class="container py-4">
    <div class="card shadow-sm">
    <div class="card-header bg-light">
      <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Buat Posting Baru</h5>
    </div>

    <div class="card-body">

      <div class="d-flex align-items-center mb-4">
      <img src="assets/images/profile.png" alt="Profile" class="rounded-circle me-3" style="width: 42px; height: 42px;" />
      <div>
        <span class="text-muted small">Posting sebagai</span>
        <div class="fw-bold">@Crocodilo</div>
      </div>
      </div>


      <form id="createPostForm" enctype="multipart/form-data">
      @csrf



      <div class="mb-3 position-relative">
        <textarea class="form-control border-0" id="postContent" name="content" rows="4" placeholder="Apa yang ingin Anda bagikan hari ini?" maxlength="500" required></textarea>
        <div class="position-absolute bottom-0 end-0 me-2 mb-2 text-muted small">
        <span id="charCount">0</span>/500
        </div>
      </div>


      <div class="mb-3">
        <div id="dropArea" class="border border-dashed border-secondary rounded p-3 text-center">
        <i class="fas fa-cloud-upload-alt fa-2x mb-2 text-muted"></i>
        <p class="mb-1">Seret dan lepaskan file di sini</p>
        <p class="small text-muted mb-0">atau klik tombol di bawah untuk memilih file</p>
        </div>


        <div id="mediaStats" class="d-none mb-2">
        <span class="badge bg-primary me-2">
          <i class="far fa-image me-1"></i><span id="imageCount">0</span> Gambar
        </span>
        <span class="badge bg-danger">
          <i class="far fa-file-video me-1"></i><span id="videoCount">0</span> Video
        </span>
        <small class="text-muted ms-2">Maksimal 10 file</small>
        </div>


        <div id="mediaPreviewContainer" class="mb-3">
        <div id="mediaPreviewArea" class="row g-2"></div>
        </div>
      </div>


      <div class="d-flex justify-content-between align-items-center py-3 border-top border-bottom mb-3">
        <div class="btn-group">
        <button type="button" class="btn btn-outline-primary rounded-circle me-2" id="uploadImageBtn" data-bs-toggle="tooltip" title="Tambah Gambar (bisa pilih banyak)" onclick="document.getElementById('imageInput').click(); return false;">
          <i class="far fa-image"></i>
        </button>
        <button type="button" class="btn btn-outline-primary rounded-circle me-2" id="uploadVideoBtn" data-bs-toggle="tooltip" title="Tambah Video (bisa pilih banyak)" onclick="document.getElementById('videoInput').click(); return false;">
          <i class="far fa-file-video"></i>
        </button>
        </div>
      </div>


      <input type="file" id="imageInput" name="images[]" accept="image/*" multiple style="display: none;" />
      <input type="file" id="videoInput" name="videos[]" accept="video/*" multiple style="display: none;" />


      <div class="mb-4">
        <div class="input-group">
        <span class="input-group-text bg-light"><i class="fas fa-hashtag"></i></span>
        <input type="text" class="form-control" id="postTags" name="tags" placeholder="Tambahkan tag (pisahkan dengan spasi)" />
        </div>
      </div>


      <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary me-2" id="previewBtn" onclick="showPreview(); return false;">Pratinjau</button>
        <button type="submit" class="btn btn-danger" id="submitPostBtn">Posting</button>
      </div>
      </form>
    </div>
    </div>


    <div class="alert alert-success mt-3 d-flex align-items-center d-none" role="alert" id="successMessage">
    <i class="fas fa-check-circle me-2"></i>
    <div id="successText">Posting Anda telah berhasil dipublikasikan!</div>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>


    <div class="alert alert-danger mt-3 d-flex align-items-center d-none" role="alert" id="errorMessage">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <div id="errorText">Terjadi kesalahan saat membuat posting.</div>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>


    <div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="previewModalLabel">Pratinjau Posting</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-center mb-3">
          <img src="assets/images/profile.png" alt="Profile" class="rounded-circle me-3" style="width: 48px; height: 48px;" />
          <div>
            <div class="fw-bold">@Crocodilo <span class="text-muted small">· Baru saja</span></div>
            <div class="text-muted small">CTO @ PublicForum</div>
          </div>
          </div>

          <h5 id="previewContent" class="mb-3"></h5>


          <div id="previewMedia" class="mb-3"></div>


          <div id="previewTags" class="mb-2"></div>

          <hr>


          <div class="d-flex">
          <button class="btn btn-sm btn-link text-muted me-3">
            <i class="far fa-heart"></i> <span>0</span>
          </button>
          <button class="btn btn-sm btn-link text-muted me-3">
            <i class="far fa-comment"></i> <span>0</span>
          </button>
          <button class="btn btn-sm btn-link text-muted me-3">
            <i class="fas fa-retweet"></i> <span>0</span>
          </button>
          <button class="btn btn-sm btn-link text-muted me-3">
            <i class="far fa-share-square"></i>
          </button>
          <button class="btn btn-sm btn-link text-muted">
            <i class="far fa-bookmark"></i>
          </button>
          </div>
        </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="button" class="btn btn-primary" id="postAfterPreviewBtn">Posting Sekarang</button>
      </div>
      </div>
    </div>
    </div>
  </div>

  <style>
    /* Media preview styles */
    #mediaPreviewArea {
    min-height: 0;
    transition: min-height 0.3s;
    }

    #mediaPreviewArea:not(:empty) {
    min-height: 150px;
    }

    /* Drag and drop area */
    .border-dashed {
    border-style: dashed !important;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
    .col-6 {
      flex: 0 0 50%;
      max-width: 50%;
    }
    }
  </style>

  <script>
    // Use an IIFE to prevent global namespace pollution
    (function () {
    // Global variables - accessible through window for debugging
    window.mediaFiles = {
      images: [],
      videos: []
    };
    const MAX_FILES = 10;

    // Show preview function - exposed globally for onclick
    window.showPreview = function () {
      console.log("Showing preview");

      // Get elements - with null checks
      const content = document.getElementById('postContent')?.value || '';
      const tags = document.getElementById('postTags')?.value || '';

      const previewContent = document.getElementById('previewContent');
      const previewMedia = document.getElementById('previewMedia');
      const previewTags = document.getElementById('previewTags');

      if (!previewContent) {
      console.error("Preview elements not found");
      return;
      }

      // Content preview
      if (previewContent) {
      previewContent.textContent = content;
      }

      // Media preview
      if (previewMedia) {
      previewMedia.innerHTML = '';

      if (window.mediaFiles.images.length > 0 || window.mediaFiles.videos.length > 0) {
        // Create a carousel if more than one media
        if (window.mediaFiles.images.length + window.mediaFiles.videos.length > 1) {
        const carousel = document.createElement('div');
        carousel.id = 'previewCarousel';
        carousel.className = 'carousel slide';
        carousel.setAttribute('data-bs-ride', 'carousel');

        // Carousel indicators
        const indicators = document.createElement('div');
        indicators.className = 'carousel-indicators';

        // Carousel inner
        const inner = document.createElement('div');
        inner.className = 'carousel-inner';

        // Add all media items to carousel
        let activeIndex = 0;

        // First add images
        window.mediaFiles.images.forEach((image, index) => {
          // Indicator
          const indicator = document.createElement('button');
          indicator.setAttribute('type', 'button');
          indicator.setAttribute('data-bs-target', '#previewCarousel');
          indicator.setAttribute('data-bs-slide-to', activeIndex.toString());

          if (activeIndex === 0) {
          indicator.classList.add('active');
          }

          indicators.appendChild(indicator);

          // Slide
          const slide = document.createElement('div');
          slide.className = `carousel-item ${activeIndex === 0 ? 'active' : ''}`;

          slide.innerHTML = `
          <img src="${image.preview}" class="d-block w-100 rounded" style="max-height: 400px; object-fit: contain;">
        `;

          inner.appendChild(slide);
          activeIndex++;
        });

        // Then add videos
        window.mediaFiles.videos.forEach((video, index) => {
          // Indicator
          const indicator = document.createElement('button');
          indicator.setAttribute('type', 'button');
          indicator.setAttribute('data-bs-target', '#previewCarousel');
          indicator.setAttribute('data-bs-slide-to', activeIndex.toString());

          if (activeIndex === 0) {
          indicator.classList.add('active');
          }

          indicators.appendChild(indicator);

          // Slide
          const slide = document.createElement('div');
          slide.className = `carousel-item ${activeIndex === 0 ? 'active' : ''}`;

          slide.innerHTML = `
          <div class="bg-dark d-flex align-items-center justify-content-center rounded" style="height: 400px;">
          <i class="fas fa-play text-white fa-3x"></i>
          </div>
        `;

          inner.appendChild(slide);
          activeIndex++;
        });

        // Add controls if more than one item
        if (activeIndex > 1) {
          const prevControl = document.createElement('button');
          prevControl.className = 'carousel-control-prev';
          prevControl.setAttribute('type', 'button');
          prevControl.setAttribute('data-bs-target', '#previewCarousel');
          prevControl.setAttribute('data-bs-slide', 'prev');
          prevControl.innerHTML = `
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Previous</span>
        `;

          const nextControl = document.createElement('button');
          nextControl.className = 'carousel-control-next';
          nextControl.setAttribute('type', 'button');
          nextControl.setAttribute('data-bs-target', '#previewCarousel');
          nextControl.setAttribute('data-bs-slide', 'next');
          nextControl.innerHTML = `
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Next</span>
        `;

          carousel.appendChild(indicators);
          carousel.appendChild(inner);
          carousel.appendChild(prevControl);
          carousel.appendChild(nextControl);

          previewMedia.appendChild(carousel);
        } else {
          // Single media item, no need for carousel
          if (window.mediaFiles.images.length === 1) {
          previewMedia.innerHTML = `
          <img src="${window.mediaFiles.images[0].preview}" class="img-fluid rounded">
          `;
          } else if (window.mediaFiles.videos.length === 1) {
          previewMedia.innerHTML = `
          <div class="bg-dark d-flex align-items-center justify-content-center rounded" style="height: 300px;">
            <i class="fas fa-play text-white fa-3x"></i>
          </div>
          `;
          }
        }
        } else {
        // Single media item
        if (window.mediaFiles.images.length === 1) {
          previewMedia.innerHTML = `
          <img src="${window.mediaFiles.images[0].preview}" class="img-fluid rounded">
        `;
        } else if (window.mediaFiles.videos.length === 1) {
          previewMedia.innerHTML = `
          <div class="bg-dark d-flex align-items-center justify-content-center rounded" style="height: 300px;">
          <i class="fas fa-play text-white fa-3x"></i>
          </div>
        `;
        }
        }
      }
      }

      // Tags preview
      if (previewTags) {
      previewTags.innerHTML = '';
      if (tags.trim()) {
        const tagArray = tags.split(' ').filter(tag => tag.trim());

        tagArray.forEach(tag => {
        const tagSpan = document.createElement('span');
        tagSpan.className = 'badge bg-light text-primary me-1';
        tagSpan.innerHTML = `
        <i class="fas fa-hashtag small"></i> ${tag}
        `;

        previewTags.appendChild(tagSpan);
        });
      }
      }

      // Show modal
      try {
      if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        const previewModal = document.getElementById('previewModal');
        if (previewModal) {
        const modal = new bootstrap.Modal(previewModal);
        modal.show();
        } else {
        console.error("Preview modal not found");
        }
      } else {
        console.error("Bootstrap Modal not available");
      }
      } catch (e) {
      console.error("Error showing preview modal:", e);
      }
    };

    // Helper function to update media stats
    function updateMediaStats() {
      const totalFiles = window.mediaFiles.images.length + window.mediaFiles.videos.length;

      const imageCountEl = document.getElementById('imageCount');
      const videoCountEl = document.getElementById('videoCount');
      const mediaStats = document.getElementById('mediaStats');
      const imageBtn = document.getElementById('uploadImageBtn');
      const videoBtn = document.getElementById('uploadVideoBtn');
      const dropArea = document.getElementById('dropArea');

      // Using null checks to avoid errors
      if (imageCountEl) imageCountEl.textContent = window.mediaFiles.images.length;
      if (videoCountEl) videoCountEl.textContent = window.mediaFiles.videos.length;

      if (mediaStats) {
      if (totalFiles > 0) {
        mediaStats.classList.remove('d-none');
      } else {
        mediaStats.classList.add('d-none');
      }
      }

      // Update button states with null checks
      if (imageBtn) {
      if (window.mediaFiles.images.length > 0) {
        imageBtn.classList.add('btn-primary');
        imageBtn.classList.remove('btn-outline-primary');
      } else {
        imageBtn.classList.remove('btn-primary');
        imageBtn.classList.add('btn-outline-primary');
      }
      }

      if (videoBtn) {
      if (window.mediaFiles.videos.length > 0) {
        videoBtn.classList.add('btn-primary');
        videoBtn.classList.remove('btn-outline-primary');
      } else {
        videoBtn.classList.remove('btn-primary');
        videoBtn.classList.add('btn-outline-primary');
      }
      }

      // Display drop area when there's room for more files
      if (dropArea) {
      if (totalFiles < MAX_FILES) {
        dropArea.classList.remove('d-none');
      } else {
        dropArea.classList.add('d-none');
      }
      }

      console.log("Media stats updated:", totalFiles, "files");
    }

    // Helper function to render media previews
    function renderMediaPreviews() {
      const mediaArea = document.getElementById('mediaPreviewArea');
      if (!mediaArea) {
      console.error("Media preview area not found");
      return;
      }

      mediaArea.innerHTML = '';

      // First render images
      window.mediaFiles.images.forEach((image, index) => {
      const col = document.createElement('div');
      col.className = 'col-6 col-md-4 col-lg-3 mb-3';
      col.dataset.type = 'image';
      col.dataset.index = index;

      col.innerHTML = `
      <div class="position-relative">
        <img src="${image.preview}" class="img-fluid rounded" style="height: 150px; width: 100%; object-fit: cover;">
        <div class="position-absolute top-0 start-0 m-2">
        <span class="badge bg-dark">${index + 1}</span>
        </div>
        <div class="position-absolute top-0 end-0 d-flex m-1">
        <button type="button" class="btn btn-sm btn-light rounded-circle me-1 move-up" ${index === 0 ? 'disabled' : ''}>
          <i class="fas fa-arrow-up"></i>
        </button>
        <button type="button" class="btn btn-sm btn-light rounded-circle me-1 move-down" ${index === window.mediaFiles.images.length - 1 ? 'disabled' : ''}>
          <i class="fas fa-arrow-down"></i>
        </button>
        <button type="button" class="btn btn-sm btn-danger rounded-circle remove-media">
          <i class="fas fa-times"></i>
        </button>
        </div>
        <div class="position-absolute bottom-0 start-0 w-100 p-1 bg-dark bg-opacity-50 text-white small text-truncate">
        ${image.file.name}
        </div>
      </div>
      `;

      mediaArea.appendChild(col);
      });

      // Then render videos
      window.mediaFiles.videos.forEach((video, index) => {
      const col = document.createElement('div');
      col.className = 'col-6 col-md-4 col-lg-3 mb-3';
      col.dataset.type = 'video';
      col.dataset.index = index;

      col.innerHTML = `
      <div class="position-relative">
        <div class="bg-dark rounded d-flex align-items-center justify-content-center" style="height: 150px;">
        <i class="fas fa-play text-white fa-2x"></i>
        </div>
        <div class="position-absolute top-0 start-0 m-2">
        <span class="badge bg-danger">${index + 1}</span>
        </div>
        <div class="position-absolute top-0 end-0 d-flex m-1">
        <button type="button" class="btn btn-sm btn-light rounded-circle me-1 move-up" ${index === 0 ? 'disabled' : ''}>
          <i class="fas fa-arrow-up"></i>
        </button>
        <button type="button" class="btn btn-sm btn-light rounded-circle me-1 move-down" ${index === window.mediaFiles.videos.length - 1 ? 'disabled' : ''}>
          <i class="fas fa-arrow-down"></i>
        </button>
        <button type="button" class="btn btn-sm btn-danger rounded-circle remove-media">
          <i class="fas fa-times"></i>
        </button>
        </div>
        <div class="position-absolute bottom-0 start-0 w-100 p-1 bg-dark bg-opacity-50 text-white small text-truncate">
        ${video.file.name}
        </div>
      </div>
      `;

      mediaArea.appendChild(col);
      });

      // Set up event listeners for media controls
      document.querySelectorAll('.remove-media').forEach(btn => {
      btn.addEventListener('click', function () {
        const parent = this.closest('[data-type]');
        const type = parent.dataset.type;
        const index = parseInt(parent.dataset.index);

        if (type === 'image') {
        window.mediaFiles.images.splice(index, 1);
        } else if (type === 'video') {
        window.mediaFiles.videos.splice(index, 1);
        }

        renderMediaPreviews();
        updateMediaStats();
      });
      });

      // Move up/down functionality
      document.querySelectorAll('.move-up').forEach(btn => {
      btn.addEventListener('click', function () {
        const parent = this.closest('[data-type]');
        const type = parent.dataset.type;
        const index = parseInt(parent.dataset.index);

        if (index > 0) {
        if (type === 'image') {
          const temp = window.mediaFiles.images[index];
          window.mediaFiles.images[index] = window.mediaFiles.images[index - 1];
          window.mediaFiles.images[index - 1] = temp;
        } else if (type === 'video') {
          const temp = window.mediaFiles.videos[index];
          window.mediaFiles.videos[index] = window.mediaFiles.videos[index - 1];
          window.mediaFiles.videos[index - 1] = temp;
        }

        renderMediaPreviews();
        }
      });
      });

      document.querySelectorAll('.move-down').forEach(btn => {
      btn.addEventListener('click', function () {
        const parent = this.closest('[data-type]');
        const type = parent.dataset.type;
        const index = parseInt(parent.dataset.index);

        const maxIndex = type === 'image' ? window.mediaFiles.images.length - 1 : window.mediaFiles.videos.length - 1;

        if (index < maxIndex) {
        if (type === 'image') {
          const temp = window.mediaFiles.images[index];
          window.mediaFiles.images[index] = window.mediaFiles.images[index + 1];
          window.mediaFiles.images[index + 1] = temp;
        } else if (type === 'video') {
          const temp = window.mediaFiles.videos[index];
          window.mediaFiles.videos[index] = window.mediaFiles.videos[index + 1];
          window.mediaFiles.videos[index + 1] = temp;
        }

        renderMediaPreviews();
        }
      });
      });

      console.log("Media previews rendered");
    }

    // Helper function to process files (used by both drag-drop and file inputs)
    function processFiles(files, fileType) {
      if (!files || files.length === 0) {
      console.warn("No files to process");
      return;
      }

      const totalCurrentFiles = window.mediaFiles.images.length + window.mediaFiles.videos.length;
      const remainingSlots = MAX_FILES - totalCurrentFiles;

      if (remainingSlots <= 0) {
      alert(`Maksimal ${MAX_FILES} file dapat diunggah. Hapus beberapa file terlebih dahulu.`);
      return;
      }

      // Limit files to remaining slots
      const filesToProcess = Array.from(files).slice(0, remainingSlots);
      console.log(`Processing ${filesToProcess.length} files of type ${fileType}`);

      filesToProcess.forEach(file => {
      if (fileType === 'image' && file.type.startsWith('image/')) {
        try {
        const preview = URL.createObjectURL(file);
        window.mediaFiles.images.push({
          file: file,
          preview: preview
        });
        console.log(`Added image: ${file.name}`);
        } catch (e) {
        console.error("Error creating object URL for image:", e);
        }
      } else if (fileType === 'video' && file.type.startsWith('video/')) {
        window.mediaFiles.videos.push({
        file: file
        });
        console.log(`Added video: ${file.name}`);
      }
      });

      renderMediaPreviews();
      updateMediaStats();
    }

    // Function to submit form via AJAX
    function submitForm(formData) {
      const submitBtn = document.getElementById('submitPostBtn');
      const successMessage = document.getElementById('successMessage');
      const errorMessage = document.getElementById('errorMessage');

      // Show loading state
      if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memposting...';
      }

      // Hide previous messages
      if (successMessage) successMessage.classList.add('d-none');
      if (errorMessage) errorMessage.classList.add('d-none');

      // Submit via fetch API
      fetch('{{ route("posts.store") }}', {
      method: 'POST',
      body: formData,
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
        document.querySelector('input[name="_token"]')?.value,
      }
      })
      .then(response => response.json())
      .then(data => {
        console.log('Server response:', data);

        if (data.success) {
        // Show success message briefly
        if (successMessage) {
          const successText = document.getElementById('successText');
          if (successText) successText.textContent = data.message || 'Posting berhasil dibuat!';
          successMessage.classList.remove('d-none');
        }

        // Redirect to home page after short delay
        setTimeout(() => {
          if (data.redirect) {
          window.location.href = data.redirect;
          } else {
          window.location.href = '/'; // fallback to home
          }
        }, 1000);

        console.log("Post submitted successfully, redirecting...");
        } else {
        // Show error message
        if (errorMessage) {
          const errorText = document.getElementById('errorText');
          if (errorText) errorText.textContent = data.message || 'Terjadi kesalahan saat membuat posting.';
          errorMessage.classList.remove('d-none');
        }
        console.error("Error from server:", data);
        }
      })
      .catch(error => {
        console.error('Network error:', error);

        // Show error message
        if (errorMessage) {
        const errorText = document.getElementById('errorText');
        if (errorText) errorText.textContent = 'Terjadi kesalahan jaringan. Silakan coba lagi.';
        errorMessage.classList.remove('d-none');
        }
      })
      .finally(() => {
        // Reset button state
        if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Posting';
        }
      });
    }

    // Initialize the page
    function initTambahPage() {
      console.log("Tambah page initializing...");

      // Make sure dropArea is visible
      const dropArea = document.getElementById('dropArea');
      if (dropArea) {
      dropArea.classList.remove('d-none');
      }

      // Initialize character counter - with null checks
      const postContent = document.getElementById('postContent');
      const charCount = document.getElementById('charCount');

      if (postContent && charCount) {
      postContent.addEventListener('input', function () {
        charCount.textContent = this.value.length;
      });
      console.log("Character counter initialized");
      } else {
      console.error("Could not find postContent or charCount elements");
      }

      // Initialize tooltips - with error handling
      try {
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
        });
        console.log("Tooltips initialized");
      } else {
        console.warn("Bootstrap tooltip not available");
      }
      } catch (e) {
      console.error("Error initializing tooltips:", e);
      }

      // Set up drag and drop
      if (dropArea) {
      dropArea.addEventListener('dragover', function (e) {
        e.preventDefault();
        this.classList.add('bg-light');
      });

      dropArea.addEventListener('dragleave', function () {
        this.classList.remove('bg-light');
      });

      dropArea.addEventListener('drop', function (e) {
        e.preventDefault();
        this.classList.remove('bg-light');

        const files = e.dataTransfer.files;
        console.log(`Files dropped: ${files.length}`);

        // Process each file based on its type
        Array.from(files).forEach(file => {
        if (file.type.startsWith('image/')) {
          processFiles([file], 'image');
        } else if (file.type.startsWith('video/')) {
          processFiles([file], 'video');
        }
        });
      });

      console.log("Drag and drop initialized");
      } else {
      console.error("Drop area not found");
      }

      // Image input change
      const imageInput = document.getElementById('imageInput');
      if (imageInput) {
      imageInput.addEventListener('change', function () {
        if (this.files.length > 0) {
        console.log(`${this.files.length} images selected`);
        processFiles(this.files, 'image');
        }
      });
      console.log("Image input initialized");
      }

      // Video input change
      const videoInput = document.getElementById('videoInput');
      if (videoInput) {
      videoInput.addEventListener('change', function () {
        if (this.files.length > 0) {
        console.log(`${this.files.length} videos selected`);
        processFiles(this.files, 'video');
        }
      });
      console.log("Video input initialized");
      }

      // Form submission
      const createPostForm = document.getElementById('createPostForm');
      if (createPostForm) {
      createPostForm.addEventListener('submit', function (e) {
        e.preventDefault();
        console.log("Form submission triggered");

        // Basic validation
        const content = document.getElementById('postContent')?.value.trim();

        if (!content) {
        alert('Silakan masukkan konten posting');
        return;
        }

        // Create FormData object
        const formData = new FormData();

        // Add text fields
        formData.append('content', content);

        const tags = document.getElementById('postTags')?.value.trim();
        if (tags) {
        formData.append('tags', tags);
        }

        // Note: title is not sent to server as it's not in the database

        // Add CSRF token
        const csrfToken = document.querySelector('input[name="_token"]')?.value;
        if (csrfToken) {
        formData.append('_token', csrfToken);
        }

        // Add images
        window.mediaFiles.images.forEach((imageObj, index) => {
        formData.append('images[]', imageObj.file);
        });

        // Add videos
        window.mediaFiles.videos.forEach((videoObj, index) => {
        formData.append('videos[]', videoObj.file);
        });

        // Submit the form
        submitForm(formData);
      });

      console.log("Form submission handler initialized");
      } else {
      console.error("Create post form not found");
      }

      // Post after preview button
      const postAfterPreviewBtn = document.getElementById('postAfterPreviewBtn');
      if (postAfterPreviewBtn) {
      postAfterPreviewBtn.addEventListener('click', function (e) {
        e.preventDefault();
        console.log("Post after preview button clicked");

        // Hide preview modal
        try {
        const previewModal = document.getElementById('previewModal');
        if (previewModal) {
          const modal = bootstrap.Modal.getInstance(previewModal);
          if (modal) {
          modal.hide();
          } else {
          console.warn("Modal instance not found, trying DOM hide");
          previewModal.classList.remove('show');
          previewModal.style.display = 'none';
          document.body.classList.remove('modal-open');
          const backdrop = document.querySelector('.modal-backdrop');
          if (backdrop) backdrop.remove();
          }
        }
        } catch (e) {
        console.error("Error hiding preview modal:", e);
        }

        // Submit form
        const submitBtn = document.getElementById('submitPostBtn');
        if (submitBtn) submitBtn.click();
      });

      console.log("Post after preview button initialized");
      } else {
      console.error("Post after preview button not found");
      }

      console.log("Tambah page initialization complete");
    }

    // Call init on DOMContentLoaded if not already loaded
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initTambahPage);
    } else {
      // DOM already loaded, call init directly
      initTambahPage();
    }
    })();
  </script>

@endsection