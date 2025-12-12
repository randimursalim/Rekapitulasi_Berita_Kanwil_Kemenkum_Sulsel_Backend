// Tambah Layanan Pengaduan Masyarakat Form Management
document.addEventListener('DOMContentLoaded', function() {
    const jenisTandaPengenalSelect = document.getElementById('jenisTandaPengenal');
    const jenisTandaPengenalLainnyaGroup = document.getElementById('jenisTandaPengenalLainnyaGroup');
    const jenisTandaPengenalLainnyaInput = document.getElementById('jenisTandaPengenalLainnya');
    
    const jenisAduanSelect = document.getElementById('jenisAduan');
    const jenisAduanLainnyaGroup = document.getElementById('jenisAduanLainnyaGroup');
    const jenisAduanLainnyaInput = document.getElementById('jenisAduanLainnya');
    
    // Fungsi untuk toggle field "Jenis Tanda Pengenal Lainnya"
    function toggleJenisTandaPengenalLainnya() {
        if (jenisTandaPengenalSelect && jenisTandaPengenalLainnyaGroup && jenisTandaPengenalLainnyaInput) {
            if (jenisTandaPengenalSelect.value === 'LAINNYA') {
                jenisTandaPengenalLainnyaGroup.style.display = 'block';
                jenisTandaPengenalLainnyaInput.setAttribute('required', 'required');
            } else {
                jenisTandaPengenalLainnyaGroup.style.display = 'none';
                jenisTandaPengenalLainnyaInput.removeAttribute('required');
                jenisTandaPengenalLainnyaInput.value = '';
            }
        }
    }
    
    // Fungsi untuk toggle field "Jenis Aduan Lainnya"
    function toggleJenisAduanLainnya() {
        if (jenisAduanSelect && jenisAduanLainnyaGroup && jenisAduanLainnyaInput) {
            if (jenisAduanSelect.value === 'Lainnya') {
                jenisAduanLainnyaGroup.style.display = 'block';
                jenisAduanLainnyaInput.setAttribute('required', 'required');
            } else {
                jenisAduanLainnyaGroup.style.display = 'none';
                jenisAduanLainnyaInput.removeAttribute('required');
                jenisAduanLainnyaInput.value = '';
            }
        }
    }
    
    // Toggle field "Jenis Tanda Pengenal Lainnya" saat change
    if (jenisTandaPengenalSelect) {
        jenisTandaPengenalSelect.addEventListener('change', toggleJenisTandaPengenalLainnya);
        toggleJenisTandaPengenalLainnya();
    }
    
    // Toggle field "Jenis Aduan Lainnya" saat change
    if (jenisAduanSelect) {
        jenisAduanSelect.addEventListener('change', toggleJenisAduanLainnya);
        toggleJenisAduanLainnya();
    }
});

