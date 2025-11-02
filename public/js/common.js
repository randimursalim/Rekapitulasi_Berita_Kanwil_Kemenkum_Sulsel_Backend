/**
 * Common JavaScript functions used across the application
 * SiCakap - Sistem Cerdas Arsip dan Rekapitulasi Konten Publikasi
 */

// Smooth scrolling for navigation links
function initializeSmoothScrolling() {
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      
      // Close mobile menu if open
      const closeMenuCheckbox = document.getElementById('close-menu');
      if (closeMenuCheckbox) {
        closeMenuCheckbox.checked = false;
      }
      
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        // Add offset for fixed header
        const headerHeight = document.querySelector('.top-menu-space')?.offsetHeight || 0;
        const targetPosition = target.offsetTop - headerHeight;
        
        window.scrollTo({
          top: targetPosition,
          behavior: 'smooth'
        });
      }
    });
  });
}

// Scroll to top functionality
function initializeScrollToTop() {
  const scrollToTopBtn = document.querySelector('.back-to-top');
  if (!scrollToTopBtn) return;

  window.addEventListener('scroll', () => {
    if (window.pageYOffset > 300) {
      scrollToTopBtn.style.display = 'block';
    } else {
      scrollToTopBtn.style.display = 'none';
    }
  });

  scrollToTopBtn.addEventListener('click', (e) => {
    e.preventDefault();
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });
}

// Format date helper
function formatDate(dateString) {
  if (!dateString) return '';

  // Handle pure date 'YYYY-MM-DD' as local date (avoid timezone shift)
  if (/^\d{4}-\d{2}-\d{2}$/.test(dateString)) {
    const [year, month, day] = dateString.split('-').map(Number);
    const date = new Date(year, month - 1, day);
    return date.toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });
  }

  // Handle MySQL datetime 'YYYY-MM-DD HH:MM:SS' by using only the date part
  const mysqlMatch = dateString.match(/^(\d{4})-(\d{2})-(\d{2})/);
  if (mysqlMatch) {
    const year = Number(mysqlMatch[1]);
    const month = Number(mysqlMatch[2]);
    const day = Number(mysqlMatch[3]);
    const date = new Date(year, month - 1, day);
    return date.toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });
  }

  // Fallback for other formats
  const date = new Date(dateString);
  if (isNaN(date.getTime())) return '';
  return date.toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });
}

// Animate number counting
function updateStatNumber(elementId, targetValue) {
  const element = document.getElementById(elementId);
  if (!element) return;
  
  // Remove loading state
  element.classList.remove('loading');
  
  const startValue = 0;
  const duration = 2000; // 2 seconds
  const increment = targetValue / (duration / 16); // 60fps
  let currentValue = startValue;
  
  const timer = setInterval(() => {
    currentValue += increment;
    if (currentValue >= targetValue) {
      currentValue = targetValue;
      clearInterval(timer);
    }
    element.textContent = Math.floor(currentValue) + '+';
  }, 16);
}

// Initialize gallery interactions
function initializeGalleryInteractions() {
  const galleryItems = document.querySelectorAll('.gallery-item');
  
  galleryItems.forEach((item, index) => {
    // Add staggered animation delay
    item.style.animationDelay = `${index * 0.1}s`;
    
    // Add click event for modal
    item.addEventListener('click', function() {
      const img = this.querySelector('img');
      const title = this.querySelector('h3').textContent;
      showImageModal(img.src, title, index);
    });
    
    // Add hover effects
    item.addEventListener('mouseenter', function() {
      this.classList.add('hovered');
    });
    
    item.addEventListener('mouseleave', function() {
      this.classList.remove('hovered');
    });
  });
}

// Global variables for image modal navigation
let currentPhotoIndex = 0;
let allPhotos = [];

// Show image modal with navigation
function showImageModal(src, title, photoIndex = 0) {
  // Store current photo index
  currentPhotoIndex = photoIndex;
  
  // Get all photos for navigation
  allPhotos = Array.from(document.querySelectorAll('.gallery-item')).map(item => ({
    src: item.querySelector('img').src,
    title: item.querySelector('h3').textContent,
    type: item.querySelector('p').textContent
  }));
  
  // Create modal if it doesn't exist
  let modal = document.getElementById('imageModal');
  if (!modal) {
    modal = document.createElement('div');
    modal.id = 'imageModal';
    modal.className = 'image-modal';
    modal.innerHTML = `
      <div class="modal-content">
        <span class="modal-close">&times;</span>
        <button class="modal-nav modal-prev" id="modalPrev">‹</button>
        <button class="modal-nav modal-next" id="modalNext">›</button>
        <img class="modal-image" src="" alt="">
        <div class="modal-info">
          <h3 class="modal-title"></h3>
        </div>
      </div>
    `;
    document.body.appendChild(modal);
    
    // Add event listeners
    modal.querySelector('.modal-close').addEventListener('click', () => {
      modal.style.display = 'none';
    });
    
    modal.querySelector('.modal-prev').addEventListener('click', (e) => {
      e.stopPropagation();
      navigatePhoto(-1);
    });
    
    modal.querySelector('.modal-next').addEventListener('click', (e) => {
      e.stopPropagation();
      navigatePhoto(1);
    });
    
    modal.addEventListener('click', (e) => {
      if (e.target === modal) {
        modal.style.display = 'none';
      }
    });
    
    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
      if (modal.style.display === 'flex') {
        if (e.key === 'ArrowLeft') {
          navigatePhoto(-1);
        } else if (e.key === 'ArrowRight') {
          navigatePhoto(1);
        } else if (e.key === 'Escape') {
          modal.style.display = 'none';
        }
      }
    });
  }
  
  // Update modal content
  updateModalContent();
  modal.style.display = 'flex';
}

// Navigate to previous/next photo
function navigatePhoto(direction) {
  currentPhotoIndex += direction;
  
  // Loop around
  if (currentPhotoIndex < 0) {
    currentPhotoIndex = allPhotos.length - 1;
  } else if (currentPhotoIndex >= allPhotos.length) {
    currentPhotoIndex = 0;
  }
  
  updateModalContent();
}

// Update modal content
function updateModalContent() {
  const modal = document.getElementById('imageModal');
  const photo = allPhotos[currentPhotoIndex];
  
  modal.querySelector('.modal-image').src = photo.src;
  modal.querySelector('.modal-title').textContent = photo.title;
}

// Initialize video interactions
function initializeVideoInteractions() {
  const videoItems = document.querySelectorAll('.video-item');
  
  videoItems.forEach((item, index) => {
    // Add staggered animation delay
    item.style.animationDelay = `${index * 0.1}s`;
    
    // Add click event for video modal
    item.addEventListener('click', function() {
      const videoId = this.getAttribute('data-video-id');
      const title = this.querySelector('h3').textContent;
      showVideoModal(videoId, title);
    });
    
    // Add hover effects
    item.addEventListener('mouseenter', function() {
      this.classList.add('hovered');
    });
    
    item.addEventListener('mouseleave', function() {
      this.classList.remove('hovered');
    });
  });
}

// Show video modal
function showVideoModal(videoId, title) {
  // Create modal if it doesn't exist
  let modal = document.getElementById('videoModal');
  if (!modal) {
    modal = document.createElement('div');
    modal.id = 'videoModal';
    modal.className = 'video-modal';
    modal.innerHTML = `
      <div class="modal-content">
        <span class="modal-close">&times;</span>
        <div class="video-container">
          <iframe class="video-iframe" src="" frameborder="0" allowfullscreen></iframe>
        </div>
        <div class="modal-info">
          <h3 class="modal-title"></h3>
        </div>
      </div>
    `;
    document.body.appendChild(modal);
    
    // Add event listeners
    modal.querySelector('.modal-close').addEventListener('click', () => {
      modal.style.display = 'none';
      // Stop video by clearing src
      const iframe = modal.querySelector('.video-iframe');
      iframe.src = '';
    });
    
    // Close modal when clicking outside
    modal.addEventListener('click', (e) => {
      if (e.target === modal) {
        modal.style.display = 'none';
        const iframe = modal.querySelector('.video-iframe');
        iframe.src = '';
      }
    });
  }
  
  // Set video content
  const iframe = modal.querySelector('.video-iframe');
  const modalTitle = modal.querySelector('.modal-title');
  
  iframe.src = `https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0`;
  modalTitle.textContent = title;
  modal.style.display = 'flex';
}

// Initialize schedule interactions
function initializeScheduleInteractions() {
  const scheduleEvents = document.querySelectorAll('.schedule-event');
  
  scheduleEvents.forEach((event, index) => {
    // Add staggered animation delay
    event.style.animationDelay = `${index * 0.1}s`;
    
    // Add hover effects
    event.addEventListener('mouseenter', function() {
      this.classList.add('hovered');
    });
    
    event.addEventListener('mouseleave', function() {
      this.classList.remove('hovered');
    });
    
    // Add click event for modal
    event.addEventListener('click', function() {
      const eventData = JSON.parse(this.dataset.eventData);
      showScheduleModal(eventData);
    });
  });
  
  // Modal event listeners
  const modal = document.getElementById('scheduleModal');
  const modalClose = document.getElementById('modalClose');
  const modalBackdrop = modal.querySelector('.modal-backdrop');
  
  // Close modal events
  modalClose.addEventListener('click', closeScheduleModal);
  modalBackdrop.addEventListener('click', closeScheduleModal);
  
  // Close modal with Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && modal.style.display === 'flex') {
      closeScheduleModal();
    }
  });
}

// Show schedule modal
function showScheduleModal(eventData) {
  const modal = document.getElementById('scheduleModal');
  const modalTitle = document.getElementById('modalTitle');
  const modalBody = document.getElementById('modalBody');
  
  // Set modal title
  modalTitle.textContent = eventData.title;
  
  // Process description to preserve line breaks
  const formattedDescription = eventData.description ? 
    eventData.description.replace(/\n/g, '<br>') : 
    'Tidak ada keterangan';
  
  // Create modal content - simplified to show only database content
  const modalContent = `
    <div class="modal-event-info">
      <div class="event-detail-header">
        <div class="event-time-detail">${eventData.time}</div>
        <div class="event-type-badge" style="background-color: ${eventData.color}">
          ${eventData.type.toUpperCase()}
        </div>
      </div>
      
      <div class="event-description">
        <h4>Keterangan</h4>
        <p>${formattedDescription}</p>
      </div>
    </div>
  `;
  
  modalBody.innerHTML = modalContent;
  modal.style.display = 'flex';
  
  // Add animation
  setTimeout(() => {
    modal.classList.add('show');
  }, 10);
}

// Close schedule modal
function closeScheduleModal() {
  const modal = document.getElementById('scheduleModal');
  modal.classList.remove('show');
  
  setTimeout(() => {
    modal.style.display = 'none';
  }, 300);
}

// Initialize all common functionality
function initializeCommon() {
  initializeSmoothScrolling();
  initializeScrollToTop();
}
