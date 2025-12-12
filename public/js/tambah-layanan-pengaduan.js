// Tambah/Edit Layanan Pengaduan Form Management
document.addEventListener('DOMContentLoaded', function() {
    // Auto-generate nomor register untuk form tambah
    const noRegisterInput = document.getElementById('noRegisterPengaduan');
    if (noRegisterInput && !noRegisterInput.value) {
        // Hanya generate jika form tambah (tidak ada value)
        fetch('ajax/generate_no_register.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.no_register) {
                    noRegisterInput.value = data.no_register;
                }
            })
            .catch(error => {
                // Error generating no register - handled silently
            });
    }
    
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
                // Hanya clear value jika bukan edit mode (tidak ada value yang sudah diisi)
                if (!jenisTandaPengenalLainnyaInput.value) {
                    jenisTandaPengenalLainnyaInput.value = '';
                }
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
                // Hanya clear value jika bukan edit mode (tidak ada value yang sudah diisi)
                if (!jenisAduanLainnyaInput.value) {
                    jenisAduanLainnyaInput.value = '';
                }
            }
        }
    }
    
    // Toggle field "Jenis Tanda Pengenal Lainnya" saat change
    if (jenisTandaPengenalSelect) {
        jenisTandaPengenalSelect.addEventListener('change', toggleJenisTandaPengenalLainnya);
        // Check initial state saat halaman dimuat (untuk edit mode)
        toggleJenisTandaPengenalLainnya();
    }
    
    // Toggle field "Jenis Aduan Lainnya" saat change
    if (jenisAduanSelect) {
        jenisAduanSelect.addEventListener('change', toggleJenisAduanLainnya);
        // Check initial state saat halaman dimuat (untuk edit mode)
        toggleJenisAduanLainnya();
    }
    
    // Preview file keterangan
    const keteranganFileInput = document.getElementById('keteranganFile');
    const keteranganFilePreview = document.getElementById('keteranganFilePreview');
    const keteranganFileName = document.getElementById('keteranganFileName');
    
    if (keteranganFileInput && keteranganFilePreview && keteranganFileName) {
        keteranganFileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                keteranganFileName.textContent = file.name;
                keteranganFilePreview.style.display = 'block';
            } else {
                keteranganFilePreview.style.display = 'none';
            }
        });
    }
});

