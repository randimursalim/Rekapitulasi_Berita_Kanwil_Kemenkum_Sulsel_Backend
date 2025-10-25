// Input Konten Form Management
document.addEventListener('DOMContentLoaded', function () {
  const jenisSelect = document.getElementById('jenis');
  const formBerita = document.getElementById('form-berita');
  const formMedsos = document.getElementById('form-medsos');
  const jenisBerita = document.getElementById('jenisBerita'); // penting

  function toggleForm() {
    if (!jenisSelect) return;

    if (jenisSelect.value === 'berita') {
      formBerita.style.display = 'block';
      formMedsos.style.display = 'none';

      // aktifkan required hanya untuk berita
      if (jenisBerita) jenisBerita.setAttribute('required', 'required');
      formMedsos.querySelectorAll('input, textarea, select').forEach(el => {
        el.removeAttribute('required');
      });

    } else if (jenisSelect.value !== '') {
      formBerita.style.display = 'none';
      formMedsos.style.display = 'block';

      // aktifkan required untuk medsos
      formMedsos.querySelectorAll('input, textarea, select').forEach(el => {
        if (el.name !== 'dokumentasi') el.setAttribute('required', 'required');
      });
      if (jenisBerita) jenisBerita.removeAttribute('required');

    } else {
      // jika tidak pilih apa-apa
      formBerita.style.display = 'none';
      formMedsos.style.display = 'none';
      if (jenisBerita) jenisBerita.removeAttribute('required');
    }
  }

  toggleForm();
  jenisSelect.addEventListener('change', toggleForm);
});
