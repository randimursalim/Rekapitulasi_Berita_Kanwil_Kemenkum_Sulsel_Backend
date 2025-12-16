/**
 * Landing Page JavaScript functions
 * SiCakap - Sistem Cerdas Arsip dan Rekapitulasi Konten Publikasi
 */

// Dashboard Statistics
async function loadDashboardStats() {
  // Add loading state
  document.getElementById('total-berita').classList.add('loading');
  document.getElementById('total-medsos').classList.add('loading');
  document.getElementById('total-arsip').classList.add('loading');
  
  try {
    const response = await fetch('ajax/dashboard_stats.php');
    const data = await response.json();
    
    if (data.success) {
      // Update stat numbers with animation
      updateStatNumber('total-berita', data.data.total_berita);
      updateStatNumber('total-medsos', data.data.total_medsos);
      updateStatNumber('total-arsip', data.data.total_arsip);
      } else {
        // Fallback to default values
        updateStatNumber('total-berita', 0);
        updateStatNumber('total-medsos', 0);
        updateStatNumber('total-arsip', 0);
      }
    } catch (error) {
      // Fallback to default values
      updateStatNumber('total-berita', 0);
      updateStatNumber('total-medsos', 0);
      updateStatNumber('total-arsip', 0);
    }
}

// Gallery Photos
async function loadGalleryPhotos() {
  const galleryGrid = document.getElementById('galleryGrid');
  if (!galleryGrid) return;
  
  try {
    const response = await fetch('ajax/gallery_photos.php');
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const result = await response.json();
    
    if (result.success && result.data && Array.isArray(result.data) && result.data.length > 0) {
      renderGalleryPhotos(result.data);
    } else {
      renderPlaceholderPhotos();
    }
  } catch (error) {
    renderPlaceholderPhotos();
  }
}

// Render photos from database
function renderGalleryPhotos(photos) {
  const galleryGrid = document.getElementById('galleryGrid');
  if (!galleryGrid) {
    return;
  }
  
  if (!photos || photos.length === 0) {
    renderPlaceholderPhotos();
    return;
  }
  
  // Additional deduplication on client side (backup)
  const seenImages = new Set();
  const uniquePhotos = [];
  
  photos.forEach((photo) => {
    // Normalize image path for comparison
    let imagePath = photo.image || '';
    
    if (!imagePath || imagePath.trim() === '') {
      return; // Skip photos without image path
    }
    
    // Extract filename (case-insensitive)
    const filename = imagePath.split('/').pop().split('\\').pop().toLowerCase();
    
    if (!filename || filename.trim() === '') {
      return; // Skip photos with empty filename
    }
    
    // Check if we've seen this filename before
    if (!seenImages.has(filename)) {
      seenImages.add(filename);
      uniquePhotos.push(photo);
    }
  });
  
  const photosHTML = uniquePhotos.map((photo, index) => {
    // Handle image path - fix path for storage/uploads
    let imageSrc = photo.image;
    if (!imageSrc.startsWith('http') && !imageSrc.startsWith('/')) {
      // If path starts with storage/uploads, use it directly
      if (imageSrc.startsWith('storage/uploads/')) {
        imageSrc = imageSrc; // Use as is
      } else {
        imageSrc = 'Images/' + imageSrc;
      }
    }
    
    // Resolve date from various possible API fields without defaulting to today
    const rawDate = 
      photo.tanggal_berita || photo.date ||
      photo.tanggal || photo.tanggal_upload || photo.tanggal_publikasi ||
      photo.created_at || photo.createdAt || photo.updated_at || photo.updatedAt ||
      photo.tanggal_input || photo.tgl || photo.tgl_upload || photo.tgl_publikasi || photo.publish_date ||
      photo.datetime || photo.time || photo.waktu;
    const dateHTML = rawDate ? `<span class="gallery-date">${formatDate(rawDate)}</span>` : '';
    
    return `
      <div class="gallery-item" data-index="${index}">
        <div class="gallery-image">
          <img src="${imageSrc}" alt="${photo.title}" loading="lazy" onerror="this.src='https://via.placeholder.com/300x200?text=Image+Not+Found'">
          <div class="gallery-overlay">
            <div class="gallery-info">
              <h3>${photo.title}</h3>
              <p>${photo.type === 'berita' ? 'Berita' : 'Media Sosial'}</p>
              ${dateHTML}
            </div>
          </div>
        </div>
      </div>
    `;
  }).join('');
  
  galleryGrid.innerHTML = photosHTML;
  initializeGalleryInteractions();
}

// Render placeholder photos if no database photos
function renderPlaceholderPhotos() {
  const galleryGrid = document.getElementById('galleryGrid');
  
  const placeholderPhotos = [
    { src: 'https://source.unsplash.com/random/800x800?nature&sig=1', title: 'Dokumentasi Kegiatan' },
    { src: 'https://source.unsplash.com/random/900x900?business&sig=2', title: 'Presentasi' },
    { src: 'https://source.unsplash.com/random/500x500?meeting&sig=3', title: 'Rapat' },
    { src: 'https://source.unsplash.com/random/801x801?office&sig=4', title: 'Kantor' },
    { src: 'https://source.unsplash.com/random/904x904?team&sig=5', title: 'Tim' },
    { src: 'https://source.unsplash.com/random/505x505?event&sig=6', title: 'Event' }
  ];
  
  const photosHTML = placeholderPhotos.map((photo, index) => `
    <div class="gallery-item" data-index="${index}">
      <div class="gallery-image">
        <img src="${photo.src}" alt="${photo.title}" loading="lazy">
        <div class="gallery-overlay">
          <div class="gallery-info">
            <h3>${photo.title}</h3>
            <p>Dokumentasi</p>
          </div>
        </div>
      </div>
    </div>
  `).join('');
  
  galleryGrid.innerHTML = photosHTML;
  initializeGalleryInteractions();
}

// Video Gallery
async function loadVideoGallery() {
  const videoGalleryGrid = document.getElementById('videoGalleryGrid');
  if (!videoGalleryGrid) return;
  
  try {
    // For now, use placeholder videos. Later we can fetch from database
    renderPlaceholderVideos();
  } catch (error) {
    renderPlaceholderVideos();
  }
}

// Render placeholder videos
function renderPlaceholderVideos() {
  const videoGalleryGrid = document.getElementById('videoGalleryGrid');
  
  const placeholderVideos = [
    {
      id: 'chtdZCzv_iI',
      title: 'Profil Layanan Kanwil Kemenkum Sulsel 2025',
      description: 'Video terkait layanan yang ada di kanwil kementerian hukum sulawesi selatan',
      thumbnail: 'https://img.youtube.com/vi/chtdZCzv_iI/maxresdefault.jpg'
    },
    {
      id: '55wGNoLPdjA',
      title: 'Video Profil Kanwil Kementerian Hukum Sulawesi Selatan 2025',
      description: 'Video Profil Kantor Wilayah Kementerian Hukum Sulawesi Selatan Menuju Wilayah Birokrasi Bersih dan Melayani 2025',
      thumbnail: 'https://img.youtube.com/vi/55wGNoLPdjA/maxresdefault.jpg'
    },
    {
      id: '_RK7k7cQAkI',
      title: 'Pelantikan Notaris Pengganti',
      description: 'Dua Notaris Pengganti untuk wilayah Kabupaten Gowa dan Maros resmi dilantik di Kanwil Kemenkum Sulsel',
      thumbnail: 'https://img.youtube.com/vi/_RK7k7cQAkI/maxresdefault.jpg'
    },
    {
      id: 'EPVjj441Wvg',
      title: 'Pembentukan Pengurus Koperasi di Kantor Wilayah Kementerian Hukum Sulawesi Selatan',
      description: 'Kemenkum Sulsel resmi membentuk pengurus Koperasi Kanwil Kemenkum Sulsel sebagai langkah nyata dalam mendorong kesejahteraan ASN melalui semangat kebersamaan!',
      thumbnail: 'https://img.youtube.com/vi/EPVjj441Wvg/maxresdefault.jpg'
    },
    {
      id: 'ZHh7ztD2-K8',
      title: 'Hari Jadi Sulsel ke-356',
      description: 'Kepala Kantor Wilayah Kementerian Hukum Sulsel Mewakili Menteri Hukum RI menghadiri Peringatan ke-356 Tahun Sulawesi Selatan.',
      thumbnail: 'https://img.youtube.com/vi/ZHh7ztD2-K8/maxresdefault.jpg'
    },
    {
      id: 'Jhu7XbartO8',
      title: 'Pembukaan Diklat Paralegal',
      description: 'Pada 6 Oktober kemarin, kami dengan bangga menggelar Pembukaan Diklat Paralegal yang menjadi tonggak penting dalam meningkatkan kapasitas paralegal di berbagai wilayah',
      thumbnail: 'https://img.youtube.com/vi/Jhu7XbartO8/maxresdefault.jpg'
    }
  ];
  
  const videosHTML = placeholderVideos.map((video, index) => `
    <div class="video-item" data-index="${index}" data-video-id="${video.id}">
      <div class="video-thumbnail">
        <img src="${video.thumbnail}" alt="${video.title}" loading="lazy">
        <div class="video-overlay">
          <div class="play-button">
            <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polygon points="5,3 19,12 5,21"></polygon>
            </svg>
          </div>
          <div class="video-info">
            <h3>${video.title}</h3>
            <p>${video.description}</p>
          </div>
        </div>
      </div>
    </div>
  `).join('');
  
  videoGalleryGrid.innerHTML = videosHTML;
  initializeVideoInteractions();
}

// Schedule Management
let isLoadingSchedule = false;

async function loadSchedule() {
  if (isLoadingSchedule) return;
  
  const scheduleTimeline = document.getElementById('scheduleTimeline');
  if (!scheduleTimeline) return;
  
  isLoadingSchedule = true;
  
  try {
    const response = await fetch('ajax/schedule_landing.php');
    const result = await response.json();
    
    if (result.success && result.data.length > 0) {
      renderScheduleFromDatabase(result.data);
    } else {
      // Jika tidak ada jadwal kegiatan 7 hari ke depan, tampilkan pesan
      renderEmptySchedule();
    }
    
  } catch (error) {
    renderEmptySchedule();
  } finally {
    isLoadingSchedule = false;
  }
}

// Render schedule from database
function renderScheduleFromDatabase(scheduleData) {
  const scheduleTimeline = document.getElementById('scheduleTimeline');
  if (!scheduleTimeline) return;
  
  // Group data by day of week (7 hari ke depan)
  const weeklySchedule = {
    'Senin': [],
    'Selasa': [],
    'Rabu': [],
    'Kamis': [],
    'Jumat': [],
    'Sabtu': [],
    'Minggu': []
  };
  
  // Map Indonesian day names
  const dayNames = {
    'Monday': 'Senin',
    'Tuesday': 'Selasa', 
    'Wednesday': 'Rabu',
    'Thursday': 'Kamis',
    'Friday': 'Jumat',
    'Saturday': 'Sabtu',
    'Sunday': 'Minggu'
  };
  
  // Group activities by day (7 hari ke depan) with deduplication
  // First, deduplicate activities by ID
  const uniqueActivities = [];
  const seenIds = new Set();
  
  scheduleData.forEach(activity => {
    if (!seenIds.has(activity.id)) {
      seenIds.add(activity.id);
      uniqueActivities.push(activity);
    }
  });
  
  uniqueActivities.forEach(activity => {
    const date = new Date(activity.date);
    const dayName = dayNames[date.toLocaleDateString('en-US', { weekday: 'long' })];
    
    if (weeklySchedule[dayName]) {
      weeklySchedule[dayName].push({
        id: activity.id,
        title: activity.title,
        time: activity.time,
        description: activity.description,
        status: activity.status,
        type: activity.type,
        color: activity.color
      });
    }
  });
  
  // Sort activities by time within each day
  Object.keys(weeklySchedule).forEach(day => {
    weeklySchedule[day].sort((a, b) => {
      const timeA = a.time.split(' - ')[0];
      const timeB = b.time.split(' - ')[0];
      return timeA.localeCompare(timeB);
    });
  });
  
  // Generate HTML for weekly grid
  const days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
  const timeSlots = ['07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'];
  
  let scheduleHTML = `
    <div class="schedule-grid">
      <div class="schedule-header">
        <div class="time-column">Waktu</div>
        ${days.map(day => `<div class="day-header">${day}</div>`).join('')}
      </div>
      <div class="schedule-body">
  `;
  
  // Generate time slots with better activity matching
  timeSlots.forEach(timeSlot => {
    scheduleHTML += `
      <div class="schedule-row">
        <div class="time-slot">${timeSlot}</div>
        ${days.map(day => {
          // Find activities that start at this time slot (exact matching)
          const activities = weeklySchedule[day].filter(activity => {
            const startTime = activity.time.split(' - ')[0];
            const slotHour = timeSlot.substring(0, 2);
            const activityHour = startTime.substring(0, 2);
            
            // Only match if activity starts exactly at this hour
            return activityHour === slotHour;
          });
          
            if (activities.length > 0) {
              // Display all activities for this time slot with additional deduplication
              // Additional deduplication by ID at rendering level
              const uniqueActivities = [];
              const seenIds = new Set();
              
              activities.forEach(activity => {
                if (!seenIds.has(activity.id)) {
                  seenIds.add(activity.id);
                  uniqueActivities.push(activity);
                }
              });
              
              const activitiesHTML = uniqueActivities.map(activity => `
                <div class="schedule-event" 
                     data-event-data='${JSON.stringify(activity)}'
                     style="background-color: ${activity.color}; border-left: 4px solid ${activity.color}">
                  <div class="event-title">${activity.title}</div>
                  <div class="event-time">${activity.time}</div>
                </div>
              `).join('');
              
              return `
                <div class="day-cell">
                  ${activitiesHTML}
                </div>
              `;
            } else {
              return '<div class="day-cell"></div>';
            }
        }).join('')}
      </div>
    `;
  });
  
  scheduleHTML += `
      </div>
    </div>
  `;
  
  scheduleTimeline.innerHTML = scheduleHTML;
  
  // Initialize interactions
  initializeScheduleInteractions();
}

// Render empty schedule message
function renderEmptySchedule() {
  const scheduleTimeline = document.getElementById('scheduleTimeline');
  if (!scheduleTimeline) return;
  
  scheduleTimeline.innerHTML = `
    <div class="schedule-empty-message">
      <div class="empty-message-icon">
        <i class="fas fa-calendar-times"></i>
      </div>
      <h3>Jadwal Kegiatan Belum Ada</h3>
      <p>Belum ada jadwal kegiatan untuk 7 hari ke depan (termasuk hari ini)</p>
    </div>
  `;
}

// Schedule Peminjaman Ruangan Management
let isLoadingSchedulePeminjaman = false;

async function loadSchedulePeminjaman() {
  if (isLoadingSchedulePeminjaman) return;
  
  const scheduleTimeline = document.getElementById('schedulePeminjamanTimeline');
  if (!scheduleTimeline) return;
  
  isLoadingSchedulePeminjaman = true;
  
  try {
    // Gunakan path relatif dari landing.php
    const response = await fetch('ajax/schedule_peminjaman_landing.php');
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const result = await response.json();
    
    if (result.success && result.data && result.data.length > 0) {
      renderSchedulePeminjamanFromDatabase(result.data);
    } else {
      // Jika tidak ada jadwal peminjaman ruangan 7 hari ke depan, tampilkan empty state
      renderEmptySchedulePeminjaman();
    }
    
  } catch (error) {
    // Tampilkan empty state jika error
    renderEmptySchedulePeminjaman();
  } finally {
    isLoadingSchedulePeminjaman = false;
  }
}

// Render schedule peminjaman ruangan from database
function renderSchedulePeminjamanFromDatabase(scheduleData) {
  const scheduleTimeline = document.getElementById('schedulePeminjamanTimeline');
  if (!scheduleTimeline) return;
  
  // Group data by day of week (7 hari ke depan)
  const weeklySchedule = {
    'Senin': [],
    'Selasa': [],
    'Rabu': [],
    'Kamis': [],
    'Jumat': [],
    'Sabtu': [],
    'Minggu': []
  };
  
  // Map Indonesian day names
  const dayNames = {
    'Monday': 'Senin',
    'Tuesday': 'Selasa', 
    'Wednesday': 'Rabu',
    'Thursday': 'Kamis',
    'Friday': 'Jumat',
    'Saturday': 'Sabtu',
    'Sunday': 'Minggu'
  };
  
  // Group activities by day (7 hari ke depan) with deduplication
  const uniqueActivities = [];
  const seenIds = new Set();
  
  scheduleData.forEach(activity => {
    if (!seenIds.has(activity.id)) {
      seenIds.add(activity.id);
      uniqueActivities.push(activity);
    }
  });
  
  uniqueActivities.forEach(activity => {
    const date = new Date(activity.date);
    const dayName = dayNames[date.toLocaleDateString('en-US', { weekday: 'long' })];
    
    if (weeklySchedule[dayName]) {
      weeklySchedule[dayName].push({
        id: activity.id,
        title: activity.title,
        date: activity.date,
        time: activity.time,
        description: activity.description,
        status: activity.status,
        type: activity.type,
        color: activity.color,
        ruangan: activity.ruangan || 'Tidak disebutkan',
        peminjam: activity.peminjam || 'Tidak disebutkan'
      });
    }
  });
  
  // Sort activities by time within each day
  Object.keys(weeklySchedule).forEach(day => {
    weeklySchedule[day].sort((a, b) => {
      const timeA = a.time.split(' - ')[0];
      const timeB = b.time.split(' - ')[0];
      return timeA.localeCompare(timeB);
    });
  });
  
  // Generate HTML for weekly grid
  const days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
  const timeSlots = ['07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'];
  
  let scheduleHTML = `
    <div class="schedule-grid">
      <div class="schedule-header">
        <div class="time-column">Waktu</div>
        ${days.map(day => `<div class="day-header">${day}</div>`).join('')}
      </div>
      <div class="schedule-body">
  `;
  
  // Generate time slots with better activity matching
  timeSlots.forEach(timeSlot => {
    scheduleHTML += `
      <div class="schedule-row">
        <div class="time-slot">${timeSlot}</div>
        ${days.map(day => {
          // Find activities that start at this time slot (exact matching)
          const activities = weeklySchedule[day].filter(activity => {
            const startTime = activity.time.split(' - ')[0];
            const slotHour = timeSlot.substring(0, 2);
            const activityHour = startTime.substring(0, 2);
            
            // Only match if activity starts exactly at this hour
            return activityHour === slotHour;
          });
          
            if (activities.length > 0) {
              // Display all activities for this time slot with additional deduplication
              const uniqueActivities = [];
              const seenIds = new Set();
              
              activities.forEach(activity => {
                if (!seenIds.has(activity.id)) {
                  seenIds.add(activity.id);
                  uniqueActivities.push(activity);
                }
              });
              
              const activitiesHTML = uniqueActivities.map(activity => `
                <div class="schedule-event" 
                     data-event-data='${JSON.stringify(activity)}'
                     style="background-color: ${activity.color}; border-left: 4px solid ${activity.color}">
                  <div class="event-title">${activity.title}</div>
                  <div class="event-time">${activity.time}</div>
                </div>
              `).join('');
              
              return `
                <div class="day-cell">
                  ${activitiesHTML}
                </div>
              `;
            } else {
              return '<div class="day-cell"></div>';
            }
        }).join('')}
      </div>
    `;
  });
  
  scheduleHTML += `
      </div>
    </div>
  `;
  
  scheduleTimeline.innerHTML = scheduleHTML;
  
  // Initialize interactions for peminjaman ruangan
  initializeSchedulePeminjamanInteractions();
}

// Render empty schedule peminjaman ruangan message
function renderEmptySchedulePeminjaman() {
  const scheduleTimeline = document.getElementById('schedulePeminjamanTimeline');
  if (!scheduleTimeline) return;
  
  scheduleTimeline.innerHTML = `
    <div class="schedule-empty-message">
      <div class="empty-message-icon">
        <i class="fas fa-calendar-times"></i>
      </div>
      <h3>Jadwal Peminjaman Ruangan Belum Ada</h3>
      <p>Belum ada jadwal peminjaman ruangan untuk 7 hari ke depan (termasuk hari ini)</p>
    </div>
  `;
}

// Render placeholder schedule peminjaman ruangan (contoh data)
function renderPlaceholderSchedulePeminjaman() {
  const scheduleTimeline = document.getElementById('schedulePeminjamanTimeline');
  if (!scheduleTimeline) return;
  
  // Hitung tanggal untuk 7 hari ke depan
  const today = new Date();
  const dayNames = {
    'Monday': 'Senin',
    'Tuesday': 'Selasa', 
    'Wednesday': 'Rabu',
    'Thursday': 'Kamis',
    'Friday': 'Jumat',
    'Saturday': 'Sabtu',
    'Sunday': 'Minggu'
  };
  
  // Fungsi untuk mendapatkan tanggal berdasarkan hari
  function getDateForDay(dayName) {
    const days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
    const dayIndex = days.indexOf(dayName);
    if (dayIndex === -1) return null;
    
    const currentDay = today.getDay();
    const targetDay = dayIndex === 0 ? 1 : (dayIndex === 6 ? 0 : dayIndex + 1); // Convert to JS day (0=Sunday)
    let diff = targetDay - currentDay;
    if (diff <= 0) diff += 7; // Next week if already passed
    
    const targetDate = new Date(today);
    targetDate.setDate(today.getDate() + diff);
    return targetDate.toISOString().split('T')[0];
  }
  
  // Weekly schedule data dengan contoh peminjaman ruangan
  const weeklySchedule = {
    'Senin': [
      { 
        id: 'placeholder-1',
        date: getDateForDay('Senin'),
        time: '09:00 - 11:00', 
        title: 'Rapat Koordinasi Bulanan', 
        type: 'meeting', 
        color: '#3b82f6',
        description: 'Rapat koordinasi bulanan untuk membahas program kerja dan evaluasi kinerja seluruh unit kerja.',
        ruangan: 'Ruang Rapat Baharuddin Lopa (Kakanwil)',
        peminjam: 'Budi Santoso'
      },
      { 
        id: 'placeholder-2',
        date: getDateForDay('Senin'),
        time: '14:00 - 16:00', 
        title: 'Seminar Hukum dan HAM', 
        type: 'seminar', 
        color: '#8b5cf6',
        description: 'Seminar tentang perkembangan hukum dan hak asasi manusia terkini dengan narasumber ahli.',
        ruangan: 'Aula Pancasila (Lantai 3)',
        peminjam: 'Siti Nurhaliza'
      }
    ],
    'Selasa': [
      { 
        id: 'placeholder-3',
        date: getDateForDay('Selasa'),
        time: '10:00 - 12:00', 
        title: 'Pelatihan Digitalisasi Dokumen', 
        type: 'training', 
        color: '#10b981',
        description: 'Pelatihan penggunaan sistem digitalisasi dokumen untuk meningkatkan efisiensi kerja.',
        ruangan: 'Ruang Rapat Andi Mattalatta (Lantai 1)',
        peminjam: 'Ahmad Fauzi'
      }
    ],
    'Rabu': [
      { 
        id: 'placeholder-4',
        date: getDateForDay('Rabu'),
        time: '09:00 - 11:00', 
        title: 'Evaluasi Program Tahunan', 
        type: 'evaluation', 
        color: '#6366f1',
        description: 'Evaluasi program kerja tahunan dan perencanaan strategis tahun depan.',
        ruangan: 'Ruang Rapat Hamid Awaluddin (Lantai 2)',
        peminjam: 'Dewi Sartika'
      },
      { 
        id: 'placeholder-5',
        date: getDateForDay('Rabu'),
        time: '13:00 - 15:00', 
        title: 'Workshop Penyusunan Laporan', 
        type: 'workshop', 
        color: '#ef4444',
        description: 'Workshop penyusunan laporan kinerja dan evaluasi program kerja.',
        ruangan: 'Ruang Rapat Bhinneka Tunggal Ika (Lantai 3)',
        peminjam: 'Rudi Hartono'
      }
    ],
    'Kamis': [
      { 
        id: 'placeholder-6',
        date: getDateForDay('Kamis'),
        time: '09:30 - 11:30', 
        title: 'Training Pegawai Baru', 
        type: 'training', 
        color: '#10b981',
        description: 'Pelatihan orientasi dan pengenalan sistem kerja untuk pegawai baru.',
        ruangan: 'Aula Pancasila (Lantai 3)',
        peminjam: 'Maya Sari'
      }
    ],
    'Jumat': [
      { 
        id: 'placeholder-7',
        date: getDateForDay('Jumat'),
        time: '10:00 - 12:00', 
        title: 'Rapat Tim Humas', 
        type: 'meeting', 
        color: '#3b82f6',
        description: 'Rapat koordinasi tim humas untuk membahas strategi komunikasi dan publikasi.',
        ruangan: 'Ruang Rapat Baharuddin Lopa (Kakanwil)',
        peminjam: 'Indra Gunawan'
      }
    ],
    'Sabtu': [],
    'Minggu': []
  };
  
  // Generate HTML for weekly grid (sama seperti renderSchedulePeminjamanFromDatabase)
  const days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
  const timeSlots = ['07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'];
  
  let scheduleHTML = `
    <div class="schedule-grid">
      <div class="schedule-header">
        <div class="time-column">Waktu</div>
        ${days.map(day => `<div class="day-header">${day}</div>`).join('')}
      </div>
      <div class="schedule-body">
  `;
  
  // Generate time slots
  timeSlots.forEach(timeSlot => {
    scheduleHTML += `
      <div class="schedule-row">
        <div class="time-slot">${timeSlot}</div>
        ${days.map(day => {
          // Find activities that start at this time slot
          const activities = weeklySchedule[day].filter(activity => {
            const startTime = activity.time.split(' - ')[0];
            const slotHour = timeSlot.substring(0, 2);
            const activityHour = startTime.substring(0, 2);
            return activityHour === slotHour;
          });
          
          if (activities.length > 0) {
            const activitiesHTML = activities.map(activity => `
              <div class="schedule-event" 
                   data-event-data='${JSON.stringify(activity)}'
                   style="background-color: ${activity.color}; border-left: 4px solid ${activity.color}">
                <div class="event-title">${activity.title}</div>
                <div class="event-time">${activity.time}</div>
              </div>
            `).join('');
            
            return `
              <div class="day-cell">
                ${activitiesHTML}
              </div>
            `;
          } else {
            return '<div class="day-cell"></div>';
          }
        }).join('')}
      </div>
    `;
  });
  
  scheduleHTML += `
      </div>
    </div>
  `;
  
  scheduleTimeline.innerHTML = scheduleHTML;
  
  // Initialize interactions
  initializeSchedulePeminjamanInteractions();
}

// Initialize schedule peminjaman ruangan interactions
function initializeSchedulePeminjamanInteractions() {
  const scheduleEvents = document.querySelectorAll('#schedulePeminjamanTimeline .schedule-event');
  const modal = document.getElementById('schedulePeminjamanModal');
  const modalTitle = document.getElementById('modalPeminjamanTitle');
  const modalBody = document.getElementById('modalPeminjamanBody');
  const modalClose = document.getElementById('modalPeminjamanClose');
  const modalBackdrop = modal?.querySelector('.modal-backdrop');
  
  scheduleEvents.forEach(event => {
    event.addEventListener('click', function() {
      const eventData = JSON.parse(this.getAttribute('data-event-data'));
      showSchedulePeminjamanModal(eventData);
    });
  });
  
  // Close modal handlers
  if (modalClose) {
    modalClose.addEventListener('click', () => {
      closeSchedulePeminjamanModal();
    });
  }
  
  if (modalBackdrop) {
    modalBackdrop.addEventListener('click', () => {
      closeSchedulePeminjamanModal();
    });
  }
  
  // Close modal with Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && modal && modal.style.display === 'flex') {
      closeSchedulePeminjamanModal();
    }
  });
}

// Show schedule peminjaman ruangan modal
function showSchedulePeminjamanModal(eventData) {
  const modal = document.getElementById('schedulePeminjamanModal');
  const modalTitle = document.getElementById('modalPeminjamanTitle');
  const modalBody = document.getElementById('modalPeminjamanBody');
  
  if (!modal || !modalTitle || !modalBody) return;
  
  // Set modal title
  modalTitle.textContent = eventData.title;
  
  // Format tanggal
  const tanggalFormatted = eventData.date ? 
    new Date(eventData.date).toLocaleDateString('id-ID', { 
      weekday: 'long', 
      year: 'numeric', 
      month: 'long', 
      day: 'numeric' 
    }) : 
    'Tidak disebutkan';
  
  // Create modal content dengan format sama seperti jadwal kegiatan (tanpa keterangan)
  const modalContent = `
    <div class="modal-event-info">
      <div class="event-detail-header">
        <div class="event-time-detail">${eventData.time || 'Tidak disebutkan'}</div>
        <div class="event-type-badge" style="background-color: ${eventData.color || '#3b82f6'}">
          ${(eventData.type || 'room-booking').toUpperCase()}
        </div>
      </div>
      
      <div class="event-details">
        <div class="detail-row">
          <div class="detail-label">Tanggal:</div>
          <div class="detail-value">${tanggalFormatted}</div>
        </div>
        <div class="detail-row">
          <div class="detail-label">Ruangan:</div>
          <div class="detail-value">${eventData.ruangan || 'Tidak disebutkan'}</div>
        </div>
        <div class="detail-row">
          <div class="detail-label">Peminjam:</div>
          <div class="detail-value">${eventData.peminjam || 'Tidak disebutkan'}</div>
        </div>
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

// Close schedule peminjaman ruangan modal
function closeSchedulePeminjamanModal() {
  const modal = document.getElementById('schedulePeminjamanModal');
  if (!modal) return;
  
  modal.classList.remove('show');
  
  setTimeout(() => {
    modal.style.display = 'none';
  }, 300);
}

// Render placeholder schedule (backup - not used if empty schedule detected)
function renderPlaceholderSchedule() {
  const scheduleTimeline = document.getElementById('scheduleTimeline');
  
  // Weekly schedule data with detailed information
  const weeklySchedule = {
    'Senin': [
      { 
        time: '09:30 - 10:30', 
        title: 'Rapat Koordinasi Bulanan', 
        type: 'meeting', 
        color: '#3b82f6',
        description: 'Rapat koordinasi bulanan untuk membahas program kerja dan evaluasi kinerja seluruh unit kerja.',
        location: 'Ruang Rapat Utama',
        participants: 'Seluruh Kepala Bagian dan Kepala Seksi',
        agenda: ['Evaluasi kinerja bulan lalu', 'Perencanaan program bulan depan', 'Koordinasi antar unit']
      },
      { 
        time: '11:00 - 12:30', 
        title: 'Seminar Hukum dan HAM', 
        type: 'seminar', 
        color: '#8b5cf6',
        description: 'Seminar tentang perkembangan hukum dan hak asasi manusia terkini dengan narasumber ahli.',
        location: 'Aula Pancasila',
        participants: 'Pegawai dan masyarakat umum',
        agenda: ['Update regulasi terbaru', 'Kasus-kasus HAM terkini', 'Q&A session']
      }
    ],
    'Selasa': [
      { 
        time: '10:00 - 11:00', 
        title: 'Pelatihan Digitalisasi Dokumen', 
        type: 'training', 
        color: '#10b981',
        description: 'Pelatihan penggunaan sistem digitalisasi dokumen untuk meningkatkan efisiensi kerja.',
        location: 'Lab Komputer',
        participants: 'Pegawai administrasi',
        agenda: ['Pengenalan sistem', 'Praktik penggunaan', 'Troubleshooting']
      },
      { 
        time: '11:30 - 13:00', 
        title: 'Workshop Penyusunan Laporan', 
        type: 'workshop', 
        color: '#ef4444',
        description: 'Workshop penyusunan laporan kinerja dan evaluasi program kerja.',
        location: 'Ruang Meeting 2',
        participants: 'Tim evaluasi dan pelaporan',
        agenda: ['Format laporan standar', 'Indikator kinerja', 'Presentasi hasil']
      }
    ],
    'Rabu': [
      { 
        time: '09:00 - 10:15', 
        title: 'Evaluasi Program Tahunan', 
        type: 'evaluation', 
        color: '#6366f1',
        description: 'Evaluasi program kerja tahunan dan perencanaan strategis tahun depan.',
        location: 'Ruang Rapat Utama',
        participants: 'Tim perencanaan strategis',
        agenda: ['Review pencapaian target', 'Analisis gap', 'Perencanaan tahun depan']
      },
      { 
        time: '10:45 - 11:45', 
        title: 'Kunjungan Kerja Gubernur', 
        type: 'visit', 
        color: '#f59e0b',
        description: 'Kunjungan kerja Gubernur Sulawesi Selatan ke Kantor Wilayah Kemenkum Sulsel.',
        location: 'Kantor Wilayah Kemenkum Sulsel',
        participants: 'Gubernur dan rombongan',
        agenda: ['Presentasi program', 'Kunjungan fasilitas', 'Diskusi strategis']
      },
      { 
        time: '12:00 - 13:45', 
        title: 'Rapat Tim Humas', 
        type: 'meeting', 
        color: '#3b82f6',
        description: 'Rapat koordinasi tim humas untuk membahas strategi komunikasi dan publikasi.',
        location: 'Ruang Humas',
        participants: 'Tim humas dan publikasi',
        agenda: ['Review publikasi', 'Strategi komunikasi', 'Koordinasi media']
      }
    ],
    'Kamis': [
      { 
        time: '09:30 - 10:30', 
        title: 'Training Pegawai Baru', 
        type: 'training', 
        color: '#10b981',
        description: 'Pelatihan orientasi dan pengenalan sistem kerja untuk pegawai baru.',
        location: 'Aula Training',
        participants: 'Pegawai baru',
        agenda: ['Pengenalan organisasi', 'Sistem kerja', 'Budaya kerja']
      },
      { 
        time: '12:00 - 13:45', 
        title: 'Seminar HAM dan Demokrasi', 
        type: 'seminar', 
        color: '#8b5cf6',
        description: 'Seminar tentang hak asasi manusia dan demokrasi dalam konteks pembangunan daerah.',
        location: 'Aula Pancasila',
        participants: 'Pegawai dan stakeholder',
        agenda: ['Prinsip-prinsip HAM', 'Demokrasi lokal', 'Best practices']
      }
    ],
    'Jumat': [
      { 
        time: '10:00 - 11:00', 
        title: 'Workshop Digital Transformation', 
        type: 'workshop', 
        color: '#ef4444',
        description: 'Workshop transformasi digital untuk meningkatkan pelayanan publik.',
        location: 'Lab Komputer',
        participants: 'Tim IT dan pelayanan',
        agenda: ['Digitalisasi layanan', 'Platform digital', 'Implementasi']
      },
      { 
        time: '12:30 - 14:00', 
        title: 'Rapat Evaluasi Mingguan', 
        type: 'evaluation', 
        color: '#6366f1',
        description: 'Evaluasi pencapaian target mingguan dan perencanaan minggu depan.',
        location: 'Ruang Rapat Utama',
        participants: 'Manajemen dan koordinator',
        agenda: ['Review mingguan', 'Target minggu depan', 'Koordinasi']
      }
    ],
    'Sabtu': [
      { 
        time: '09:30 - 10:30', 
        title: 'Pelatihan Sistem Informasi', 
        type: 'training', 
        color: '#10b981',
        description: 'Pelatihan penggunaan sistem informasi manajemen untuk pegawai.',
        location: 'Lab Komputer',
        participants: 'Pegawai operasional',
        agenda: ['Pengenalan SIM', 'Praktik penggunaan', 'Maintenance']
      },
      { 
        time: '11:00 - 12:30', 
        title: 'Kunjungan Resmi DPRD', 
        type: 'visit', 
        color: '#f59e0b',
        description: 'Kunjungan resmi anggota DPRD Sulawesi Selatan untuk koordinasi program.',
        location: 'Kantor Wilayah Kemenkum Sulsel',
        participants: 'Anggota DPRD dan tim',
        agenda: ['Presentasi program', 'Diskusi legislasi', 'Koordinasi']
      }
    ],
    'Minggu': [
      { 
        time: '09:30 - 10:30', 
        title: 'Rapat Mingguan Manajemen', 
        type: 'meeting', 
        color: '#3b82f6',
        description: 'Rapat mingguan manajemen untuk koordinasi dan evaluasi kinerja.',
        location: 'Ruang Rapat Utama',
        participants: 'Manajemen senior',
        agenda: ['Review kinerja', 'Koordinasi program', 'Pengambilan keputusan']
      },
      { 
        time: '11:00 - 12:30', 
        title: 'Seminar Publik Hukum', 
        type: 'seminar', 
        color: '#8b5cf6',
        description: 'Seminar publik tentang hukum dan keadilan untuk masyarakat umum.',
        location: 'Aula Pancasila',
        participants: 'Masyarakat umum',
        agenda: ['Edukasi hukum', 'Konsultasi hukum', 'Q&A']
      }
    ]
  };
  
  // Time slots
  const timeSlots = ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00'];
  const days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
  
  // Create grid header
  let scheduleHTML = `
    <div class="schedule-grid">
      <div class="schedule-header">
        <div class="time-column"></div>
        ${days.map(day => `<div class="day-header">${day}</div>`).join('')}
      </div>
      <div class="schedule-body">
  `;
  
  // Create time slots
  timeSlots.forEach(timeSlot => {
    scheduleHTML += `
      <div class="schedule-row">
        <div class="time-slot">${timeSlot}</div>
        ${days.map(day => `<div class="day-cell" data-day="${day}" data-time="${timeSlot}"></div>`).join('')}
      </div>
    `;
  });
  
  scheduleHTML += `
      </div>
    </div>
  `;
  
  scheduleTimeline.innerHTML = scheduleHTML;
  
  // Add events to grid
  days.forEach(day => {
    if (weeklySchedule[day]) {
      weeklySchedule[day].forEach(event => {
        const startTime = event.time.split(' - ')[0];
        const endTime = event.time.split(' - ')[1];
        
        // Find the appropriate cell(s) and add event
        const startHour = parseInt(startTime.split(':')[0]);
        const endHour = parseInt(endTime.split(':')[0]);
        
        // Create event element
        const eventElement = document.createElement('div');
        eventElement.className = 'schedule-event';
        eventElement.style.backgroundColor = event.color;
        eventElement.innerHTML = `
          <div class="event-time">${event.time}</div>
          <div class="event-title">${event.title}</div>
        `;
        
        // Store event data for modal
        eventElement.dataset.eventData = JSON.stringify(event);
        
        // Find and append to appropriate cell
        const dayCells = document.querySelectorAll(`[data-day="${day}"]`);
        const targetCell = Array.from(dayCells).find(cell => {
          const cellTime = parseInt(cell.dataset.time.split(':')[0]);
          return cellTime === startHour;
        });
        
        if (targetCell) {
          targetCell.appendChild(eventElement);
        }
      });
    }
  });
  
  initializeScheduleInteractions();
}

// News Portal Management
let isLoadingNews = false;

async function loadNewsPortal() {
  if (isLoadingNews) return;
  
  const newsGrid = document.getElementById('newsGrid');
  if (!newsGrid) return;
  
  isLoadingNews = true;
  
  try {
    const response = await fetch('ajax/news_portal.php');
    
    // Check if response is ok
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    // Get response text first to debug
    const responseText = await response.text();
    
    // Try to parse as JSON
    let result;
    try {
      result = JSON.parse(responseText);
    } catch (parseError) {
      throw new Error('Invalid JSON response');
    }
    
    if (result.success && result.data.length > 0) {
      renderNewsPortal(result.data);
    } else {
      renderPlaceholderNews();
    }
  } catch (error) {
    renderPlaceholderNews();
  } finally {
    isLoadingNews = false;
  }
}

// Render news from database
let isRenderingNews = false;

function renderNewsPortal(news) {
  if (isRenderingNews) return;
  
  const newsGrid = document.getElementById('newsGrid');
  if (!newsGrid) return;
  
  isRenderingNews = true;
  
  const newsHTML = news.map((item, index) => {
    // Handle image path
    let imageSrc = item.dokumentasi || 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzUwIiBoZWlnaHQ9IjIyMCIgdmlld0JveD0iMCAwIDM1MCAyMjAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzNTAiIGhlaWdodD0iMjIwIiBmaWxsPSIjNjM2NmYxIi8+Cjx0ZXh0IHg9IjE3NSIgeT0iMTEwIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjQiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj7wn5KwIE5ld3MgSW1hZ2U8L3RleHQ+Cjwvc3ZnPg==';
    if (!imageSrc.startsWith('http') && !imageSrc.startsWith('/') && !imageSrc.startsWith('data:')) {
      if (imageSrc.startsWith('storage/uploads/')) {
        imageSrc = imageSrc;
      } else {
        imageSrc = 'Images/' + imageSrc;
      }
    }
    
    // Truncate content for preview
    const truncatedContent = item.isi ? item.isi.substring(0, 150) + '...' : 'Tidak ada konten tersedia';
    
    return `
      <article class="news-card" data-index="${index}">
        <div class="news-image">
          <img src="${imageSrc}" alt="${item.judul}" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
          <div class="news-image-placeholder" style="display:none;">
            <span>📰 No Image</span>
          </div>
          <div class="news-category">${item.jenis_berita === 'website_kanwil' ? 'WEBSITE KANWIL' : 'BERITA'}</div>
        </div>
        <div class="news-content">
          <h3>${item.judul}</h3>
          <p>${truncatedContent}</p>
          <div class="news-meta">
            <span class="news-date">${formatDate(item.tanggal || new Date().toISOString())}</span>
            <button class="btn-read-more" onclick="window.open('${item.link || '#'}', '_blank')">
              Baca Selengkapnya
            </button>
          </div>
        </div>
      </article>
    `;
  }).join('');
  
  newsGrid.innerHTML = newsHTML;
  
  // Start auto-scrolling after rendering
  startAutoScroll();
  
  isRenderingNews = false;
}

// Render placeholder news
function renderPlaceholderNews() {
  const newsGrid = document.getElementById('newsGrid');
  
  const placeholderNews = [
    {
      judul: "Kegiatan Rutin Humas Kanwil Sulsel",
      isi: "Humas Kanwil Sulsel terus berkomitmen untuk memberikan informasi terbaik kepada masyarakat melalui berbagai kegiatan dan publikasi berkualitas.",
      jenis: "berita",
      dokumentasi: "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzUwIiBoZWlnaHQ9IjIyMCIgdmlld0JveD0iMCAwIDM1MCAyMjAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzNTAiIGhlaWdodD0iMjIwIiBmaWxsPSIjNjM2NmYxIi8+Cjx0ZXh0IHg9IjE3NSIgeT0iMTEwIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjQiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj7wn5KwIE5ld3MgSW1hZ2U8L3RleHQ+Cjwvc3ZnPg==",
      jenis_berita: "website_kanwil"
    },
    {
      judul: "Update Terbaru dari Website Kanwil",
      isi: "Ikuti perkembangan terbaru dari website resmi Kantor Wilayah Kemenkum Sulsel untuk mendapatkan informasi terkini dan terpercaya.",
      jenis: "berita",
      dokumentasi: "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzUwIiBoZWlnaHQ9IjIyMCIgdmlld0JveD0iMCAwIDM1MCAyMjAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzNTAiIGhlaWdodD0iMjIwIiBmaWxsPSIjMDA2NmNjIi8+Cjx0ZXh0IHg9IjE3NSIgeT0iMTEwIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjAiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj7wn5KwIE9mZmljaWFsPC90ZXh0Pgo8L3N2Zz4=",
      jenis_berita: "website_kanwil"
    },
    {
      judul: "Dokumentasi Kegiatan Terbaru",
      isi: "Lihat dokumentasi lengkap dari berbagai kegiatan yang telah dilaksanakan oleh Humas Kanwil Sulsel dalam memberikan pelayanan terbaik.",
      jenis: "berita",
      dokumentasi: "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzUwIiBoZWlnaHQ9IjIyMCIgdmlld0JveD0iMCAwIDM1MCAyMjAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzNTAiIGhlaWdodD0iMjIwIiBmaWxsPSIjNjM2NmYxIi8+Cjx0ZXh0IHg9IjE3NSIgeT0iMTEwIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjQiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj7wn5KwIE5ld3MgSW1hZ2U8L3RleHQ+Cjwvc3ZnPg==",
      jenis_berita: "website_kanwil"
    },
    {
      judul: "Pelayanan Publik yang Optimal",
      isi: "Kantor Wilayah Kemenkum Sulsel terus meningkatkan kualitas pelayanan publik dengan berbagai inovasi dan perbaikan sistem.",
      jenis: "berita",
      dokumentasi: "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzUwIiBoZWlnaHQ9IjIyMCIgdmlld0JveD0iMCAwIDM1MCAyMjAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzNTAiIGhlaWdodD0iMjIwIiBmaWxsPSIjMjhhNzQ1Ii8+Cjx0ZXh0IHg9IjE3NSIgeT0iMTEwIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjAiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj7wn5KwIFNlcnZpY2U8L3RleHQ+Cjwvc3ZnPg==",
      jenis_berita: "website_kanwil"
    },
    {
      judul: "Program Kerja Tahunan 2024",
      isi: "Mengawali tahun 2024 dengan berbagai program kerja yang telah disusun untuk memberikan pelayanan terbaik kepada masyarakat Sulawesi Selatan.",
      jenis: "berita",
      dokumentasi: "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzUwIiBoZWlnaHQ9IjIyMCIgdmlld0JveD0iMCAwIDM1MCAyMjAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzNTAiIGhlaWdodD0iMjIwIiBmaWxsPSIjZGY0NDQ0Ii8+Cjx0ZXh0IHg9IjE3NSIgeT0iMTEwIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjAiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj7wn5KwIFByb2dyYW08L3RleHQ+Cjwvc3ZnPg==",
      jenis_berita: "website_kanwil"
    },
    {
      judul: "Koordinasi dengan Pemerintah Daerah",
      isi: "Menjalin kerjasama yang erat dengan pemerintah daerah dalam rangka mendukung pembangunan hukum di Sulawesi Selatan.",
      jenis: "berita",
      dokumentasi: "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzUwIiBoZWlnaHQ9IjIyMCIgdmlld0JveD0iMCAwIDM1MCAyMjAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzNTAiIGhlaWdodD0iMjIwIiBmaWxsPSIjOWMzM2Y3Ii8+Cjx0ZXh0IHg9IjE3NSIgeT0iMTEwIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjAiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj7wn5KwIENvb3JkPC90ZXh0Pgo8L3N2Zz4=",
      jenis_berita: "website_kanwil"
    },
    {
      judul: "Peningkatan SDM Aparatur",
      isi: "Melakukan berbagai pelatihan dan pengembangan kompetensi untuk meningkatkan kualitas sumber daya manusia aparatur di lingkungan Kanwil.",
      jenis: "berita",
      dokumentasi: "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzUwIiBoZWlnaHQ9IjIyMCIgdmlld0JveD0iMCAwIDM1MCAyMjAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzNTAiIGhlaWdodD0iMjIwIiBmaWxsPSIjZmY5ODAwIi8+Cjx0ZXh0IHg9IjE3NSIgeT0iMTEwIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjAiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj7wn5KwIFNESzwvdGV4dD4KPC9zdmc+",
      jenis_berita: "website_kanwil"
    },
    {
      judul: "Transparansi dan Akuntabilitas",
      isi: "Menjunjung tinggi prinsip transparansi dan akuntabilitas dalam setiap kegiatan dan program yang dilaksanakan oleh Kanwil Kemenkum Sulsel.",
      jenis: "berita",
      dokumentasi: "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzUwIiBoZWlnaHQ9IjIyMCIgdmlld0JveD0iMCAwIDM1MCAyMjAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzNTAiIGhlaWdodD0iMjIwIiBmaWxsPSIjMTAhOTc0Ii8+Cjx0ZXh0IHg9IjE3NSIgeT0iMTEwIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjAiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj7wn5KwIFRyYW5zcDwvdGV4dD4KPC9zdmc+",
      jenis_berita: "website_kanwil"
    },
    {
      judul: "Inovasi Digital dalam Pelayanan",
      isi: "Mengimplementasikan berbagai inovasi digital untuk mempermudah akses masyarakat terhadap pelayanan hukum yang disediakan.",
      jenis: "berita",
      dokumentasi: "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzUwIiBoZWlnaHQ9IjIyMCIgdmlld0JveD0iMCAwIDM1MCAyMjAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzNTAiIGhlaWdodD0iMjIwIiBmaWxsPSIjNjM2NmYxIi8+Cjx0ZXh0IHg9IjE3NSIgeT0iMTEwIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjAiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj7wn5KwIERpZ2l0YWw8L3RleHQ+Cjwvc3ZnPg==",
      jenis_berita: "website_kanwil"
    },
    {
      judul: "Komitmen Pelayanan Prima",
      isi: "Bertekad memberikan pelayanan prima kepada masyarakat dengan standar kualitas yang tinggi dan proses yang efisien.",
      jenis: "berita",
      dokumentasi: "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzUwIiBoZWlnaHQ9IjIyMCIgdmlld0JveD0iMCAwIDM1MCAyMjAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzNTAiIGhlaWdodD0iMjIwIiBmaWxsPSIjZGY0NDQ0Ii8+Cjx0ZXh0IHg9IjE3NSIgeT0iMTEwIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjAiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj7wn5KwIENvbW1pdDwvdGV4dD4KPC9zdmc+",
      jenis_berita: "website_kanwil"
    }
  ];
  
  renderNewsPortal(placeholderNews);
}

// News Auto-scroll functionality
let autoScrollInterval;
let isAutoScrolling = true;

function scrollNews(direction) {
  const newsGrid = document.getElementById('newsGrid');
  if (!newsGrid) return;
  
  // Stop auto-scroll when user clicks navigation buttons
  stopAutoScroll();
  
  // Responsive scroll amount based on viewport width
  let scrollAmount;
  if (window.innerWidth <= 320) {
    scrollAmount = window.innerWidth * 0.92 + 8; // 92vw + gap
  } else if (window.innerWidth <= 360) {
    scrollAmount = window.innerWidth * 0.90 + 8; // 90vw + gap
  } else if (window.innerWidth <= 480) {
    scrollAmount = window.innerWidth * 0.85 + 8; // 85vw + gap
  } else if (window.innerWidth <= 768) {
    scrollAmount = 290; // Tablet (280px card + 10px gap)
  } else {
    scrollAmount = 370; // Desktop (350px card + 20px gap)
  }
  const currentScroll = newsGrid.scrollLeft;
  
  if (direction === 'left') {
    newsGrid.scrollTo({
      left: currentScroll - scrollAmount,
      behavior: 'smooth'
    });
  } else {
    newsGrid.scrollTo({
      left: currentScroll + scrollAmount,
      behavior: 'smooth'
    });
  }
  
  // Update button states
  updateNavButtons();
  
  // Resume auto-scroll after 5 seconds
  setTimeout(() => {
    resumeAutoScroll();
  }, 5000);
}

function startAutoScroll() {
  const newsGrid = document.getElementById('newsGrid');
  if (!newsGrid) return;
  
  // Clear existing interval
  if (autoScrollInterval) {
    clearInterval(autoScrollInterval);
  }
  
  // Start auto-scroll every 3 seconds
  autoScrollInterval = setInterval(() => {
    if (isAutoScrolling) {
      // Responsive scroll amount based on viewport width
      let scrollAmount;
      if (window.innerWidth <= 320) {
        scrollAmount = window.innerWidth * 0.92 + 8; // 92vw + gap
      } else if (window.innerWidth <= 360) {
        scrollAmount = window.innerWidth * 0.90 + 8; // 90vw + gap
      } else if (window.innerWidth <= 480) {
        scrollAmount = window.innerWidth * 0.85 + 8; // 85vw + gap
      } else if (window.innerWidth <= 768) {
        scrollAmount = 290; // Tablet (280px card + 10px gap)
      } else {
        scrollAmount = 370; // Desktop (350px card + 20px gap)
      }
      const currentScroll = newsGrid.scrollLeft;
      const maxScroll = newsGrid.scrollWidth - newsGrid.clientWidth;
      
      // If at the end, scroll back to beginning
      if (currentScroll >= maxScroll - 10) {
        newsGrid.scrollTo({
          left: 0,
          behavior: 'smooth'
        });
      } else {
        newsGrid.scrollTo({
          left: currentScroll + scrollAmount,
          behavior: 'smooth'
        });
      }
      
      updateNavButtons();
    }
  }, 3000);
}

function stopAutoScroll() {
  isAutoScrolling = false;
  if (autoScrollInterval) {
    clearInterval(autoScrollInterval);
  }
}

function resumeAutoScroll() {
  isAutoScrolling = true;
  startAutoScroll();
}

// Update navigation button states with debouncing
let updateNavButtonsTimeout;

function updateNavButtons() {
  clearTimeout(updateNavButtonsTimeout);
  updateNavButtonsTimeout = setTimeout(() => {
    const newsGrid = document.getElementById('newsGrid');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    
    if (!newsGrid || !prevBtn || !nextBtn) return;
    
    const isAtStart = newsGrid.scrollLeft <= 0;
    const isAtEnd = newsGrid.scrollLeft >= (newsGrid.scrollWidth - newsGrid.clientWidth);
    
    prevBtn.disabled = isAtStart;
    nextBtn.disabled = isAtEnd;
  }, 100);
}

// Initialize landing page functionality
function initializeLandingPage() {
  // Load dashboard statistics
  loadDashboardStats();
  
  // Load gallery photos
  loadGalleryPhotos();
  
  // Load video gallery
  loadVideoGallery();
  
  // Load schedule
  loadSchedule();
  
  // Load schedule peminjaman ruangan
  loadSchedulePeminjaman();
  
  // Load news portal
  loadNewsPortal();
  
  // Add scroll event listener to news grid
  const newsGrid = document.getElementById('newsGrid');
  if (newsGrid) {
    newsGrid.addEventListener('scroll', () => {
      updateNavButtons();
      // Stop auto-scroll when user manually scrolls
      stopAutoScroll();
      // Resume auto-scroll after 5 seconds of inactivity
      setTimeout(() => {
        resumeAutoScroll();
      }, 5000);
    });
    
    // Stop auto-scroll on hover, resume on mouse leave
    newsGrid.addEventListener('mouseenter', stopAutoScroll);
    newsGrid.addEventListener('mouseleave', resumeAutoScroll);
  }
  
  // Ensure smooth scrolling works after page load
  setTimeout(() => {
    // Re-initialize smooth scrolling for any dynamically added elements
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      if (!anchor.hasAttribute('data-smooth-scroll')) {
        anchor.setAttribute('data-smooth-scroll', 'true');
      }
    });
  }, 100);
}

// Dropdown Menu Toggle for Mobile
function initializeDropdownMenu() {
  const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
  
  dropdownToggles.forEach(toggle => {
    toggle.addEventListener('click', function(e) {
      // Only prevent default on mobile/tablet
      if (window.innerWidth <= 800) {
        e.preventDefault();
        e.stopPropagation(); // Prevent event from bubbling up
        const dropdown = this.closest('.dropdown');
        dropdown.classList.toggle('active');
      }
    });
  });
  
  // Handle clicks on dropdown submenu items
  const dropdownMenuLinks = document.querySelectorAll('.dropdown-menu a');
  dropdownMenuLinks.forEach(link => {
    link.addEventListener('click', function(e) {
      // On mobile, close menu after clicking submenu item
      if (window.innerWidth <= 800) {
        const closeMenuCheckbox = document.getElementById('close-menu');
        if (closeMenuCheckbox) {
          // Small delay to allow smooth scroll to start
          setTimeout(() => {
            closeMenuCheckbox.checked = false;
          }, 100);
        }
      }
    });
  });
  
  // Close dropdown when clicking outside
  document.addEventListener('click', function(e) {
    if (window.innerWidth <= 800) {
      const dropdowns = document.querySelectorAll('.dropdown');
      dropdowns.forEach(dropdown => {
        // Don't close if clicking on dropdown toggle or inside dropdown menu
        if (!dropdown.contains(e.target) && !e.target.closest('.dropdown-toggle')) {
          dropdown.classList.remove('active');
        }
      });
    }
  });
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
  initializeCommon();
  initializeLandingPage();
  initializeDropdownMenu();
});
