</div> <!-- end dash-content -->
</section> <!-- end dashboard -->

<script src="/rekap-konten/public/js/script.js"></script>
<script src="/rekap-konten/public/js/rekap.js"></script>
<script src="/rekap-konten/public/js/modal-dashboard.js"></script>
<script src="/rekap-konten/public/js/filter-activity.js"></script>
<script src="/rekap-konten/public/js/form-kegiatan.js"></script>

<script>
// Data dari PHP - Pass to global scope
window.detailBerita = <?= json_encode($detailBerita ?? []) ?>;
window.detailMedsos = <?= json_encode($detailMedsos ?? []) ?>;
</script>
