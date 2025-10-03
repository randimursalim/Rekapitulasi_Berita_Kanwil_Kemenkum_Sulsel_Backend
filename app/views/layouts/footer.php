  </div> <!-- end dash-content -->
</section> <!-- end dashboard -->

<script src="/rekap-konten/public/js/script.js"></script>
<script>
  const detailBerita = <?= json_encode($detailBerita ?? []) ?>;
  const detailMedsos = <?= json_encode($detailMedsos ?? []) ?>;

  function showDetail(type) {
    
    const modal = document.getElementById("detailModal");
    const title = document.getElementById("modalTitle");
    const list = document.getElementById("modalList");

    list.innerHTML = ''; // reset isi

    let data = [];
    if (type === 'berita') {
      title.textContent = "Rincian Total Berita";
      data = detailBerita;
    } else if (type === 'medsos') {
      title.textContent = "Rincian Postingan Medsos";
      data = detailMedsos;
    }

    data.forEach(item => {
      const li = document.createElement("li");
      li.textContent = `${item.name}: ${item.value}`;
      list.appendChild(li);
    });

    modal.style.display = "block";
  }

  function closeModal() {
    document.getElementById("detailModal").style.display = "none";
  }

  window.onclick = function(e) {
    const modal = document.getElementById("detailModal");
    if (e.target === modal) modal.style.display = "none";
  };
</script>

</body>
</html>
