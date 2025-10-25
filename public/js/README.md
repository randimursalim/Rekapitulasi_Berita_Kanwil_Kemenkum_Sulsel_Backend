# Struktur JavaScript SiCakap

## Overview
File JavaScript telah dipisahkan dari `landing.php` untuk meningkatkan organisasi kode, performa, dan maintainability.

## Struktur File

```
public/
├── js/
│   ├── common.js          # Fungsi-fungsi umum yang digunakan di seluruh aplikasi
│   └── landing.js         # Fungsi-fungsi khusus untuk landing page
├── css/
│   └── landing.css
└── landing.php            # File HTML utama (tanpa script inline)
```

## File JavaScript

### 1. `common.js`
Berisi fungsi-fungsi umum yang dapat digunakan kembali:

- **Smooth Scrolling**: `initializeSmoothScrolling()`
- **Scroll to Top**: `initializeScrollToTop()`
- **Date Formatting**: `formatDate()`
- **Number Animation**: `updateStatNumber()`
- **Gallery Interactions**: `initializeGalleryInteractions()`, `showImageModal()`, `navigatePhoto()`
- **Video Interactions**: `initializeVideoInteractions()`, `showVideoModal()`
- **Schedule Interactions**: `initializeScheduleInteractions()`, `showScheduleModal()`, `closeScheduleModal()`
- **Common Initialization**: `initializeCommon()`

### 2. `landing.js`
Berisi fungsi-fungsi khusus untuk landing page:

- **Dashboard Statistics**: `loadDashboardStats()`
- **Gallery Photos**: `loadGalleryPhotos()`, `renderGalleryPhotos()`, `renderPlaceholderPhotos()`
- **Video Gallery**: `loadVideoGallery()`, `renderPlaceholderVideos()`
- **Schedule Management**: `loadSchedule()`, `renderScheduleFromDatabase()`, `renderPlaceholderSchedule()`
- **News Portal**: `loadNewsPortal()`, `renderNewsPortal()`, `renderPlaceholderNews()`
- **Auto-scroll**: `scrollNews()`, `startAutoScroll()`, `stopAutoScroll()`, `resumeAutoScroll()`
- **Navigation**: `updateNavButtons()`
- **Landing Page Initialization**: `initializeLandingPage()`

## Keuntungan Pemisahan

### 1. **Organisasi Kode**
- File HTML lebih bersih dan mudah dibaca
- Script JavaScript terorganisir dengan baik
- Mudah untuk maintenance dan debugging

### 2. **Performance**
- Browser dapat melakukan caching pada file JavaScript terpisah
- File HTML lebih kecil sehingga loading lebih cepat
- JavaScript dapat di-minify dan di-compress secara terpisah

### 3. **Reusability**
- Script dapat digunakan kembali di halaman lain
- Mudah untuk sharing kode antar developer
- Dapat dijadikan module yang dapat di-import

### 4. **Development Experience**
- Syntax highlighting yang lebih baik di editor
- IntelliSense dan autocomplete yang lebih optimal
- Error detection yang lebih akurat

## Cara Penggunaan

### Di `landing.php`:
```html
<!-- JavaScript files -->
<script src="js/common.js"></script>
<script src="js/landing.js"></script>
```

### Urutan Loading:
1. `common.js` - Dimuat terlebih dahulu karena berisi fungsi-fungsi dasar
2. `landing.js` - Dimuat setelah `common.js` karena menggunakan fungsi dari `common.js`

## Inisialisasi

Semua fungsi diinisialisasi secara otomatis ketika DOM selesai dimuat:

```javascript
// Di common.js
function initializeCommon() {
  initializeSmoothScrolling();
  initializeScrollToTop();
}

// Di landing.js
function initializeLandingPage() {
  loadDashboardStats();
  loadGalleryPhotos();
  loadVideoGallery();
  loadSchedule();
  loadNewsPortal();
  // ... fungsi lainnya
}

// Event listener
document.addEventListener('DOMContentLoaded', function() {
  initializeCommon();
  initializeLandingPage();
});
```

## Maintenance

### Menambah Fungsi Baru:
1. **Fungsi Umum**: Tambahkan ke `common.js`
2. **Fungsi Khusus Landing**: Tambahkan ke `landing.js`

### Mengubah Fungsi Existing:
1. Edit file JavaScript yang sesuai
2. Test fungsi yang diubah
3. Pastikan tidak ada breaking changes

### Debugging:
1. Gunakan browser developer tools
2. Check console untuk error
3. Pastikan file JavaScript dimuat dengan benar

## Best Practices

1. **Naming Convention**: Gunakan camelCase untuk nama fungsi
2. **Comments**: Berikan komentar yang jelas untuk fungsi kompleks
3. **Error Handling**: Selalu handle error dengan try-catch
4. **Performance**: Hindari DOM query berulang, gunakan caching
5. **Modularity**: Pisahkan fungsi berdasarkan tanggung jawabnya

## Future Enhancements

1. **Module System**: Implementasi ES6 modules
2. **Build Process**: Setup webpack atau bundler lainnya
3. **TypeScript**: Migrasi ke TypeScript untuk type safety
4. **Testing**: Implementasi unit testing untuk JavaScript
5. **Minification**: Setup minification untuk production
