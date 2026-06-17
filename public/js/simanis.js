document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('BSimpan');
    const form = document.getElementById('FormTambah');

    if (!btn || !form) return;

    btn.addEventListener('click', function () {
        const formData = new FormData(form);

        Swal.fire({
            title: 'Konfirmasi',
            text: 'Apakah data perizinan sudah benar?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, kirim',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) return;

            fetch('index.php?page=store-izin', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {

                    if (!data.success) {
                        Swal.fire('Gagal', data.message || 'Terjadi kesalahan', 'error');
                        return;
                    }

                    Swal.fire({
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
                    }).then(() => {
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
                    });
                })
                .catch(() => {
                    Swal.fire('Error', 'Terjadi kesalahan server', 'error');
                });
        });
    });
});