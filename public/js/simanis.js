document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('BSimpan');
    const form = document.getElementById('FormTambah');
    const tlpInput = document.getElementById('inputTlp');

    if (!btn || !form) return;

    // Cek jika status SIMANIS ditutup (Kuota Full)
    const isClosed = window.SIMANIS_STATUS === '0';
    const kuotaFullMessage = 'mohon maaf KANTOR WILAYAH KEMENTERIAN HUKUM SULAWESI SELATAN saat ini belum bisa menerima pengajuan perizinan karena kuota sudah full, tetap pantau situs kami secara berkala';

    if (isClosed) {
        Swal.fire({
            icon: 'warning',
            title: 'Pengajuan Perizinan Ditutup',
            text: kuotaFullMessage,
            confirmButtonText: 'Saya Mengerti',
            confirmButtonColor: '#d33',
            allowOutsideClick: false
        });
    }

    function formatPhoneInput(input) {
        if (!input) return;
        let val = input.value.trim().replace(/\D/g, '');
        if (val.startsWith('0')) {
            val = '62' + val.substring(1);
        } else if (val.length > 0 && !val.startsWith('62')) {
            val = '62' + val;
        }
        input.value = val;
    }

    if (tlpInput) {
        tlpInput.addEventListener('blur', function () {
            formatPhoneInput(this);
        });
    }

    btn.addEventListener('click', async function () {
        if (window.SIMANIS_STATUS === '0') {
            Swal.fire({
                icon: 'warning',
                title: 'Pendaftaran Ditutup',
                text: kuotaFullMessage,
                confirmButtonText: 'Saya Mengerti',
                confirmButtonColor: '#d33'
            });
            return;
        }

        if (tlpInput) formatPhoneInput(tlpInput);
        const formData = new FormData(form);

        const result = await Swal.fire({
            title: 'Konfirmasi',
            text: 'Apakah data perizinan sudah benar?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, kirim',
            cancelButtonText: 'Batal'
        });

        if (!result.isConfirmed) return;

        try {
            const res = await fetch('index.php?page=store-izin', {
                method: 'POST',
                body: formData
            });

            const data = await res.json();

            if (!data.success) {
                Swal.fire('Gagal', data.message || 'Terjadi kesalahan', 'error');
                return;
            }

            await Swal.fire({
                icon: 'success',
                title: 'Pengajuan Berhasil 🎉',
                html: `
                    <p>${data.message}</p>
                    <div style="margin-top:10px;">
                        <strong>ID Pengajuan:</strong><br>
                        <input id="izinId" value="${data.id}" readonly
                            style="width:100%; padding:8px; text-align:center; font-weight:bold;">
                    </div>
                `,
                confirmButtonText: '📋 Salin ID',
            });

            const input = document.getElementById('izinId');
            if (input) {
                input.select();
                document.execCommand('copy');
            }

            Swal.fire({
                icon: 'info',
                title: 'ID Disalin',
                text: 'Simpan ID ini untuk tracking pengajuan.',
                timer: 2000,
                showConfirmButton: false
            });

            form.reset();

        } catch (err) {
            Swal.fire('Error', 'Terjadi kesalahan server', 'error');
        }
    });
});