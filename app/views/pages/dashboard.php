<!-- Statistik -->
<div class="overview">
  <div class="title">
    <i class="fas fa-tachometer-alt"></i>
    <span class="text">Dashboard</span>
  </div>

  <div class="boxes">
    <div class="box box1" data-type="berita" data-tooltip="Klik untuk lihat rincian">
      <i class="fas fa-newspaper"></i>
      <span class="text">Total Berita</span>
      <span class="number"><?= $statistik['total_berita'] ?></span>
    </div>

    <div class="box box2" data-type="medsos" data-tooltip="Klik untuk lihat rincian">
      <i class="fas fa-share-alt"></i>
      <span class="text">Postingan Medsos</span>
      <span class="number"><?= $statistik['total_medsos'] ?></span>
    </div>

    <div class="box box3">
      <i class="fas fa-archive"></i>
      <span class="text">Total Arsip</span>
      <span class="number"><?= $statistik['total_arsip'] ?></span>
    </div>
  </div>

  <!-- Modal Detail -->
  <div id="detailModal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <h3 id="modalTitle">Detail</h3>
      <ul id="modalList"></ul>
    </div>
  </div>
</div>

<!-- Log Aktivitas -->
<div class="activity-wrapper">
  <div class="activity">
    <div class="title">
      <i class="fas fa-history"></i>
      <span class="text">Log Aktivitas</span>
    </div>

    <div class="activity-data">
    <div class="data activity-log">
      <span class="data-title">Aktivitas</span>
      <?php foreach ($logAktivitas as $log): ?>
        <span class="data-list"><?= $log['aktivitas'] ?></span>
      <?php endforeach; ?>
    </div>

    <div class="data date">
      <span class="data-title">Tanggal</span>
      <?php foreach ($logAktivitas as $log): ?>
        <span class="data-list"><?= $log['tanggal'] ?></span>
      <?php endforeach; ?>
    </div>

    <div class="data time">
      <span class="data-title">Waktu</span>
      <?php foreach ($logAktivitas as $log): ?>
        <span class="data-list"><?= $log['waktu'] ?></span>
      <?php endforeach; ?>
    </div>

    <div class="data user">
      <span class="data-title">User</span>
      <?php foreach ($logAktivitas as $log): ?>
        <span class="data-list"><?= $log['user'] ?></span>
      <?php endforeach; ?>
    </div>

    <div class="data status">
      <span class="data-title">Status</span>
      <?php foreach ($logAktivitas as $log): ?>
        <span class="data-list status-<?= strtolower($log['status']) ?>">
          <?= $log['status'] ?>
        </span>
      <?php endforeach; ?>
    </div>
  </div>
</div>
