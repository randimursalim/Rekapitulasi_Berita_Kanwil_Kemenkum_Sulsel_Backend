// Tambah Pengguna Form Management
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formTambahTamu');
    const fotoInput = document.getElementById('foto');
    const preview = document.getElementById('previewImage');

    // Validasi form
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const nama = document.getElementById('nama').value.trim();
        const telp = document.getElementById('telp').value.trim();
        const email = document.getElementById('email').value;
        const alamat = document.getElementById('alamat').value;
        const tujuan = document.getElementById('tujuan').value;

        // Validasi client-side
        if (!nama) {
            Swal.fire('Error!', 'Nama harus diisi', 'error');
            return;
        }

        if (!telp) {
            Swal.fire('Error!', 'No Telpon/WA harus diisi', 'error');
            return;
        }

        if (!email) {
            Swal.fire('Error!', 'Email harus diisi', 'error');
            return;
        }

        if (!alamat) {
            Swal.fire('Error!', 'Alamat harus diisi', 'error');
            return;
        }

        if (!tujuan) {
            Swal.fire('Error!', 'Maksud/Tujuan bertamu harus diisi', 'error');
            return;
        }

        // Submit form
        Swal.fire({
            title: 'Konfirmasi',
            text: 'Apakah Anda yakin ingin menambahkan tamu baru?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, tambahkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // AJAX submission
                const formData = new FormData(form);

                fetch('index.php?page=store-tamu', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.href = 'index.php?page=tamu';
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: data.message,
                                showConfirmButton: true
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Terjadi kesalahan saat menambahkan tamu',
                            showConfirmButton: true
                        });
                    });
            }
        });
    });
});

// Form Kamera
document.addEventListener("DOMContentLoaded", function () {
    const video = document.getElementById("video");
    const canvas = document.getElementById("canvas");
    const captureBtn = document.getElementById("captureFoto");
    const fotoInput = document.getElementById("foto");
    const preview = document.getElementById("previewFoto");

    if (!video) return;

    // Akses webcam
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => {
            console.log('Kamera aktif');
            video.srcObject = stream;
        })
        .catch(err => {
            console.error('Kamera error:', err);
            Swal.fire(
                'Kamera Tidak Aktif',
                'Browser menolak akses kamera. Pastikan HTTPS atau localhost.',
                'error'
            );
        });

    // Capture foto
    captureBtn.addEventListener("click", function () {
        const ctx = canvas.getContext("2d");
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        const base64Image = canvas.toDataURL("image/jpeg");

        // Simpan ke input hidden
        fotoInput.value = base64Image;

        // Preview
        preview.src = base64Image;
        preview.style.display = "block";
    });
});


// form tanda tangan
// form tanda tangan (DESKTOP + HP)
document.addEventListener("DOMContentLoaded", function () {
    const canvas = document.getElementById("signature-pad");
    const clearBtn = document.getElementById("clear-signature");
    const inputTTD = document.getElementById("ttd");

    if (!canvas) return;

    const ctx = canvas.getContext("2d");

    function resizeCanvas() {
        const ratio = window.devicePixelRatio || 1;
        const rect = canvas.getBoundingClientRect();
        canvas.width = rect.width * ratio;
        canvas.height = rect.height * ratio;
        ctx.setTransform(ratio, 0, 0, ratio, 0, 0);

        ctx.strokeStyle = "#000";
        ctx.lineWidth = 2;
        ctx.lineCap = "round";
    }

    resizeCanvas();
    window.addEventListener("resize", resizeCanvas);

    let drawing = false;

    function getPos(e) {
        const rect = canvas.getBoundingClientRect();
        if (e.touches) {
            return {
                x: e.touches[0].clientX - rect.left,
                y: e.touches[0].clientY - rect.top
            };
        }
        return {
            x: e.offsetX,
            y: e.offsetY
        };
    }

    function startDraw(e) {
        e.preventDefault();
        drawing = true;
        const pos = getPos(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
    }

    function draw(e) {
        if (!drawing) return;
        e.preventDefault();
        const pos = getPos(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
    }

    function endDraw() {
        drawing = false;
        ctx.beginPath();
        inputTTD.value = canvas.toDataURL("image/png");
    }

    // Mouse (Laptop)
    canvas.addEventListener("mousedown", startDraw);
    canvas.addEventListener("mousemove", draw);
    canvas.addEventListener("mouseup", endDraw);
    canvas.addEventListener("mouseleave", endDraw);

    // Touch (HP)
    canvas.addEventListener("touchstart", startDraw, { passive: false });
    canvas.addEventListener("touchmove", draw, { passive: false });
    canvas.addEventListener("touchend", endDraw);

    // Clear
    clearBtn.addEventListener("click", function () {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        inputTTD.value = "";
    });
});
