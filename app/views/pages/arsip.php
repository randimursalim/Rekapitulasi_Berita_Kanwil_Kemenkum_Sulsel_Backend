<?php
// app/views/pages/arsip.php
?>

<div class="overview arsip-page">
  <div class="title">
    <i class="uil uil-archive"></i>
    <span class="text">Arsip Konten</span>
  </div>

  <!-- 🔹 Filter -->
  <div class="filters" style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
    <label for="startDate">Tanggal:</label>
    <input type="date" id="startDate">
    <span>-</span>
    <input type="date" id="endDate">
    <button id="filterBtn">Terapkan</button>
    <button id="resetBtn">Reset</button>

    <label for="filterJenis">Jenis Konten:</label>
    <select id="filterJenis" class="filter-select">
      <option value="all">Semua</option>
      <option value="berita">Berita</option>
      <option value="sosial_media">Sosial Media</option>
    </select>

    <label for="filterKategori">Kategori/Platform:</label>
    <select id="filterKategori" class="filter-select">
      <option value="all">Semua</option>
      <option value="media_online">Media Online</option>
      <option value="surat_kabar">Surat Kabar</option>
      <option value="website_kanwil">Website Kanwil</option>
      <option value="facebook">Facebook</option>
      <option value="instagram">Instagram</option>
      <option value="tiktok">TikTok</option>
      <option value="twitter">X (Twitter)</option>
      <option value="youtube">YouTube</option>
    </select>
  </div>

  <!-- 🔹 Arsip -->
  <div class="activity-wrapper" style="margin-top:20px;">
    <div class="activity">
      <div class="activity-data" id="searchResults">
        <?php
        $allKonten = array_merge($detailBerita ?? [], $detailMedsos ?? []);
        $no = 1;
        ?>

        <!-- Tabel Data -->
        <div class="data no">
          <span class="data-title">No</span>
          <?php foreach ($allKonten as $konten): ?>
            <span class="data-list"><?= $no++; ?></span>
          <?php endforeach; ?>
        </div>

        <div class="data title-news">
          <span class="data-title">Judul</span>
          <?php foreach ($allKonten as $konten): ?>
            <span class="data-list"><?= htmlspecialchars($konten['judul']); ?></span>
          <?php endforeach; ?>
        </div>

        <div class="data jenis">
          <span class="data-title">Jenis</span>
          <?php foreach ($allKonten as $konten): ?>
            <span class="data-list"><?= $konten['jenis'] === 'berita' ? 'Berita' : 'Sosial Media'; ?></span>
          <?php endforeach; ?>
        </div>

        <div class="data kategori">
          <span class="data-title">Kategori/Platform</span>
          <?php foreach ($allKonten as $konten): ?>
            <span class="data-list">
              <?= $konten['jenis'] === 'berita' ? ucwords(str_replace('_', ' ', $konten['jenis_berita'] ?? '-')) : ($konten['jenis'] ?? '-'); ?>
            </span>
          <?php endforeach; ?>
        </div>

        <div class="data date">
          <span class="data-title">Tanggal</span>
          <?php foreach ($allKonten as $konten): ?>
            <span class="data-list"><?= $konten['jenis'] === 'berita' ? $konten['tanggal_berita'] : $konten['tanggal_post']; ?></span>
          <?php endforeach; ?>
        </div>

        <div class="data dokumentasi">
          <span class="data-title">Dokumentasi</span>
          <?php foreach ($allKonten as $konten): ?>
            <span class="data-list">
              <?php if (!empty($konten['dokumentasi'])): ?>
                <img src="<?= $konten['dokumentasi']; ?>" alt="Foto" class="preview-img" style="width:60px; cursor:pointer;">
              <?php else: ?>
                -
              <?php endif; ?>
            </span>
          <?php endforeach; ?>
        </div>

        <div class="data actions">
          <span class="data-title">Aksi</span>
          <?php foreach ($allKonten as $konten): ?>
            <span class="data-list">
              <button class="btn-action-aksi view" onclick="window.open('<?= $konten['jenis']==='berita'? $konten['link_berita'] : $konten['link_post']; ?>','_blank')"><i class="uil uil-eye"></i></button>
              <button class="btn-action-aksi edit" onclick="window.location.href='index.php?page=edit-konten&id=<?= $konten['id_konten']; ?>'"><i class="uil uil-edit"></i></button>
              <button class="btn-action-aksi delete" data-id="<?= $konten['id_konten']; ?>"><i class="uil uil-trash-alt"></i></button>
            </span>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- 🔹 Pagination -->
  <div class="pagination">
    <button class="active">1</button>
    <button>2</button>
    <button>3</button>
    <button>Next</button>
  </div>
</div>

<!-- 🔹 Modal Preview -->
<div class="modal-img" id="imgModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); text-align:center; padding-top:5%;">
  <img id="modalImage" src="" alt="Preview" style="max-width:90%; max-height:80%;">
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

  const searchInput = document.querySelector('.live-search');
  const container = document.getElementById('searchResults');
  const originalHTML = container ? container.innerHTML : '';

  // 🔹 Event delegation untuk tombol delete
  container.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-action-aksi.delete');
    if (!btn) return;

    Swal.fire({
      title: 'Hapus Konten?',
      text: "Data yang dihapus tidak bisa dikembalikan!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Ya, hapus!'
    }).then(result => {
      if(result.isConfirmed){
        window.location.href = 'index.php?page=delete-konten&id=' + btn.dataset.id;
      }
    });
  });

  // 🔹 Event preview image
  container.addEventListener('click', (e) => {
    const img = e.target.closest('.preview-img');
    if (!img) return;
    const modal = document.getElementById('imgModal');
    modal.style.display = 'block';
    document.getElementById('modalImage').src = img.src;
  });

  // 🔹 Live search
  if (searchInput && container) {
    searchInput.addEventListener('input', () => {
      const query = searchInput.value.trim().toLowerCase();

      if(query==='') {
        container.innerHTML = originalHTML;
        return;
      }

      const dataColumns = container.querySelectorAll('.data');
      const rowCount = dataColumns[0].querySelectorAll('.data-list').length;

      for(let i=0;i<rowCount;i++){
        let match = false;

        dataColumns.forEach(col=>{
          const cell = col.querySelectorAll('.data-list')[i];
          const text = cell.textContent || '';
          if(text.toLowerCase().includes(query)) match = true;
        });

        dataColumns.forEach(col=>{
          const cell = col.querySelectorAll('.data-list')[i];
          cell.style.display = match ? '' : 'none';
        });
      }
    });
  }

});

// Tombol Filter
document.addEventListener('DOMContentLoaded', () => {
  const filterBtn = document.getElementById('filterBtn');
  const resetBtn = document.getElementById('resetBtn');
  const filterJenis = document.getElementById('filterJenis');
  const filterKategori = document.getElementById('filterKategori');
  const startDate = document.getElementById('startDate');
  const endDate = document.getElementById('endDate');
  const container = document.getElementById('searchResults');

  // Normalisasi teks kategori: spasi → underscore, huruf kecil
  function normalizeKategori(str) {
    return str.toLowerCase().replace(/\s+/g, '_');
  }

  function applyFilter() {
    const jenis = filterJenis.value;       // 'all', 'berita', 'sosial_media'
    const kategori = filterKategori.value; // 'all', 'media_online', 'facebook', dll
    const start = startDate.value;
    const end = endDate.value;

    const dataColumns = container.querySelectorAll('.data');
    const rowCount = dataColumns[0].querySelectorAll('.data-list').length;

    for (let i = 0; i < rowCount; i++) {
      let show = true;

      const jenisText = dataColumns[2].querySelectorAll('.data-list')[i].textContent.trim().toLowerCase();
      const kategoriText = dataColumns[3].querySelectorAll('.data-list')[i].textContent.trim();
      const tanggalText = dataColumns[4].querySelectorAll('.data-list')[i].textContent.trim();

      // Filter jenis
      if (jenis !== 'all' && jenisText !== (jenis === 'berita' ? 'berita' : 'sosial media')) {
        show = false;
      }

      // Filter kategori
      if (kategori !== 'all' && normalizeKategori(kategoriText) !== kategori) {
        show = false;
      }

      // Filter tanggal
      if (start && tanggalText < start) show = false;
      if (end && tanggalText > end) show = false;

      // Tampilkan atau sembunyikan semua kolom baris ini
      dataColumns.forEach(col => {
        const cell = col.querySelectorAll('.data-list')[i];
        cell.style.display = show ? '' : 'none';
      });
    }
  }

  filterBtn.addEventListener('click', applyFilter);

  resetBtn.addEventListener('click', () => {
    filterJenis.value = 'all';
    filterKategori.value = 'all';
    startDate.value = '';
    endDate.value = '';

    const dataColumns = container.querySelectorAll('.data');
    const rowCount = dataColumns[0].querySelectorAll('.data-list').length;
    for (let i = 0; i < rowCount; i++) {
      dataColumns.forEach(col => {
        const cell = col.querySelectorAll('.data-list')[i];
        cell.style.display = '';
      });
    }
  });
});

// Pagination
// document.addEventListener('DOMContentLoaded', () => {
//   const container = document.getElementById('searchResults');
//   const pagination = document.querySelector('.pagination');
//   const rowsPerPage = 10;

//   // 🔹 Hitung baris yang visible
//   function getVisibleRows() {
//     const dataColumns = container.querySelectorAll('.data');
//     const rowCount = dataColumns[0].querySelectorAll('.data-list').length;
//     const visibleRows = [];
//     for (let i = 0; i < rowCount; i++) {
//       if (dataColumns[0].querySelectorAll('.data-list')[i].style.display !== 'none') {
//         visibleRows.push(i);
//       }
//     }
//     return visibleRows;
//   }

//   // 🔹 Tampilkan halaman tertentu
//   function showPage(page = 1) {
//     const visibleRows = getVisibleRows();
//     const totalPages = Math.ceil(visibleRows.length / rowsPerPage);
//     if(totalPages===0) {
//       pagination.innerHTML = '';
//       return;
//     }

//     const dataColumns = container.querySelectorAll('.data');
//     // sembunyikan semua dulu
//     dataColumns.forEach(col => col.querySelectorAll('.data-list').forEach(cell => cell.style.display = 'none'));

//     const start = (page - 1) * rowsPerPage;
//     const end = start + rowsPerPage;
//     const rowsToShow = visibleRows.slice(start, end);

//     dataColumns.forEach(col => {
//       rowsToShow.forEach(idx => {
//         col.querySelectorAll('.data-list')[idx].style.display = '';
//       });
//     });

//     renderPagination(page, totalPages);
//   }

//   // 🔹 Render tombol pagination
//   function renderPagination(currentPage, totalPages) {
//     pagination.innerHTML = '';

//     const createBtn = (text, page) => {
//       const btn = document.createElement('button');
//       btn.textContent = text;
//       if (page === currentPage) btn.classList.add('active');
//       btn.addEventListener('click', () => showPage(page));
//       return btn;
//     }

//     if (currentPage > 1) {
//       pagination.appendChild(createBtn('First', 1));
//       pagination.appendChild(createBtn('Prev', currentPage - 1));
//     }

//     for (let p = 1; p <= totalPages; p++) {
//       if (p === 1 || p === totalPages || (p >= currentPage - 1 && p <= currentPage + 1)) {
//         pagination.appendChild(createBtn(p, p));
//       } else if (p === 2 && currentPage > 3) {
//         const span = document.createElement('span'); span.textContent = '...'; pagination.appendChild(span);
//       } else if (p === totalPages - 1 && currentPage < totalPages - 2) {
//         const span = document.createElement('span'); span.textContent = '...'; pagination.appendChild(span);
//       }
//     }

//     if (currentPage < totalPages) {
//       pagination.appendChild(createBtn('Next', currentPage + 1));
//       pagination.appendChild(createBtn('Last', totalPages));
//     }
//   }

//   // 🔹 Refresh pagination saat filter / reset / search
//   function refreshPagination() {
//     showPage(1);
//   }

//   // event listener untuk filter, reset, live search
//   document.querySelectorAll('#filterBtn, #resetBtn, .live-search').forEach(el => {
//     el.addEventListener('click', refreshPagination);
//     el.addEventListener('input', refreshPagination);
//   });

//   // Inisialisasi halaman pertama
//   showPage(1);
// });

</script>
