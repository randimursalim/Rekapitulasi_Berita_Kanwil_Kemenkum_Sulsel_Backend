document.addEventListener('DOMContentLoaded', function () {

    const btn = document.getElementById('btnCariTracking');
    const input = document.getElementById('trackingId');
    const result = document.getElementById('trackingResult');

    if (!btn || !input || !result) return;

    input.focus();

    /* klik tombol */
    btn.addEventListener('click', cariTracking);

    /* enter key */
    input.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            cariTracking();
        }
    });


    function cariTracking() {

        const id = input.value.trim();

        if (!id) {
            alert("Masukkan ID terlebih dahulu");
            return;
        }

        result.innerHTML = `<p style="margin-top:20px;text-align:center;">Mencari data...</p>`;

        fetch(`index.php?page=tracking-surat-api&id=${encodeURIComponent(id)}`)
            .then(res => res.json())
            .then(data => {

                if (!data.success) {

                    result.innerHTML = `
                    <div class="tracking-error">
                        <strong>Maaf!</strong><br>
                        ID yang anda masukkan Salah!<br>
                        ID: <b>${id}</b> tidak ditemukan
                    </div>
                    `;

                    return;
                }

                const t = data.data;

                /* STATUS TIMELINE */
                const statusText = {
                    1: "Diterima oleh Pengelola Surat Masuk",
                    2: "Ditolak karena tidak memenuhi persyaratan",
                    3: "Diterima oleh Kakanwil",
                    4: "Ditolak oleh Pimpinan",
                    5: "Diterima oleh Kabag TU & Umum",
                    6: "Surat Balasan akan dikirim melalui WhatsApp"
                };

                let timeline = '';

                for (let i = 1; i <= 6; i++) {

                    timeline += `
                    <div class="timeline-item ${t.status >= i ? 'active' : ''}">
                        <div class="timeline-circle">${i}</div>
                        <div class="timeline-text">${statusText[i]}</div>
                    </div>
                    `;
                }

                result.innerHTML = `

                <div style="text-align:center;margin-bottom:20px;color:#6b7280">
                    Surat <b>Ditemukan</b>, Detail Dibawah:
                </div>

                <div class="tracking-detail">

                <h2>Keterangan:</h2>

                <table class="tracking-table">

                <tr>
                    <td>ID Pengajuan</td>
                    <td>:</td>
                    <td>${t.id}</td>
                </tr>

                <tr>
                    <td>Nama Pengaju</td>
                    <td>:</td>
                    <td>${t.nama}</td>
                </tr>

                <tr>
                    <td>NIK</td>
                    <td>:</td>
                    <td>${t.nik}</td>
                </tr>

                <tr>
                    <td>No Hp</td>
                    <td>:</td>
                    <td>${t.tlp}</td>
                </tr>

                <tr>
                    <td>Jenis Surat</td>
                    <td>:</td>
                    <td>${t.jenis_surat}</td>
                </tr>

                <tr>
                    <td>File Lampiran</td>
                    <td>:</td>
                    <td>
                        <button onclick="previewPdf('${t.file}')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td>Keterangan</td>
                    <td>:</td>
                    <td>${t.keterangan}</td>
                </tr>

                </table>

                <div class="tracking-timeline">
                    ${timeline}
                </div>

                </div>
                `;
            })
            .catch(err => {

                console.error(err);

                result.innerHTML = `
                <div class="tracking-error">
                    Terjadi kesalahan server.
                </div>
                `;
            });

    }

});


function previewPdf(file) {
    const url = window.APP_BASE + "/pdf-viewer.php?file=" + encodeURIComponent(file);
    window.open(url, "_blank");
}