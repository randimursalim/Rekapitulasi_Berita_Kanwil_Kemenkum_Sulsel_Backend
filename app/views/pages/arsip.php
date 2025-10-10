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
  <div class="activity" style="margin-top:20px;">
    <div class="activity-data">
      <?php
      // Gabungkan data berita & medsos
      $allKonten = array_merge($detailBerita ?? [], $detailMedsos ?? []);
      $no = 1;
      ?>

      <!-- No -->
      <div class="data no">
        <span class="data-title">No</span>
        <?php foreach ($allKonten as $konten) : ?>
          <span class="data-list"><?= $no++; ?></span>
        <?php endforeach; ?>
      </div>

      <!-- Judul -->
      <div class="data title-news">
        <span class="data-title">Judul</span>
        <?php foreach ($allKonten as $konten) : ?>
          <span class="data-list"><?= htmlspecialchars($konten['judul']); ?></span>
        <?php endforeach; ?>
      </div>

      <!-- Jenis -->
      <div class="data jenis">
        <span class="data-title">Jenis</span>
        <?php foreach ($allKonten as $konten) : ?>
          <span class="data-list">
            <?= $konten['jenis'] === 'berita' ? 'Berita' : 'Sosial Media'; ?>
          </span>
        <?php endforeach; ?>
      </div>

      <!-- Kategori/Platform -->
      <div class="data kategori">
        <span class="data-title">Kategori/Platform</span>
        <?php foreach ($allKonten as $konten) : ?>
          <span class="data-list">
            <?php
            if ($konten['jenis'] === 'berita') {
                echo ucwords(str_replace('_', ' ', $konten['jenis_berita'] ?? '-'));
            } else {
                echo ucfirst($konten['jenis'] ?? '-'); // medsos platform
            }
            ?>
          </span>
        <?php endforeach; ?>
      </div>

      <!-- Tanggal -->
      <div class="data date">
        <span class="data-title">Tanggal</span>
        <?php foreach ($allKonten as $konten) : ?>
          <span class="data-list"><?= $konten['jenis'] === 'berita' ? $konten['tanggal_berita'] : $konten['tanggal_post']; ?></span>
        <?php endforeach; ?>
      </div>

      <!-- Dokumentasi -->
      <div class="data dokumentasi">
        <span class="data-title">Dokumentasi</span>
        <?php foreach ($allKonten as $konten) : ?>
          <span class="data-list">
            <?php if (!empty($konten['dokumentasi'])) : ?>
              <img src="<?= $konten['dokumentasi']; ?>" alt="Foto" class="preview-img" style="width:60px;cursor:pointer;">
            <?php else : ?>
              -
            <?php endif; ?>
          </span>
        <?php endforeach; ?>
      </div>

      <!-- Aksi -->
      <div class="data actions">
        <span class="data-title">Aksi</span>
        <?php foreach ($allKonten as $konten) : ?>
          <span class="data-list">
            <button class="btn-action-aksi view"
                    onclick="window.open('<?= $konten['jenis'] === 'berita' ? $konten['link_berita'] : $konten['link_post']; ?>', '_blank')">
              <i class="uil uil-eye"></i>
            </button>
            <button class="btn-action-aksi edit"
                    onclick="window.location.href='index.php?page=edit-konten&id=<?= $konten['id_konten']; ?>'">
              <i class="uil uil-edit"></i>
            </button>
            <button class="btn-action-aksi delete" data-id="<?= $konten['id_konten']; ?>">
              <i class="uil uil-trash-alt"></i>
            </button>
          </span>
        <?php endforeach; ?>
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
  // 🔹 Preview Gambar
  document.querySelectorAll('.preview-img').forEach(img => {
    img.addEventListener('click', () => {
      document.getElementById('modalImage').src = img.src;
      document.getElementById('imgModal').style.display = 'block';
    });
  });

  document.getElementById('imgModal').addEventListener('click', () => {
    document.getElementById('imgModal').style.display = 'none';
  });

  // 🔹 Hapus Konten dengan SweetAlert2
  document.querySelectorAll('.btn-action-aksi.delete').forEach(btn => {
    btn.addEventListener('click', function() {
      Swal.fire({
        title: 'Hapus Konten?',
        text: "Data yang dihapus tidak bisa dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = 'index.php?page=delete-konten&id=' + btn.dataset.id;
        }
      });
    });
  });
</script>
