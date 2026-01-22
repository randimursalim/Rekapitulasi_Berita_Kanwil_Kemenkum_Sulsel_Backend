document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('BSimpan');
    const form = document.getElementById('FormTambah');

    if (!btn || !form) return;

    btn.addEventListener('click', function () {
        const formData = new FormData(form);

        Swal.fire({
            title: 'Konfirmasi',
            text: 'Apakah data sudah benar?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, simpan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) return;

            fetch('index.php?page=store-tamu', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Terima kasih 🙏',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        form.reset();
                        document.getElementById('foto').value = '';
                        document.getElementById('ttd').value = '';
                        if (typeof clearTTD === 'function') clearTTD();
                        document.getElementById('previewFoto').style.display = 'none';
                    });
                } else {
                    Swal.fire('Gagal', data.message, 'error');
                }
            })
            .catch(() => {
                Swal.fire('Error', 'Terjadi kesalahan server', 'error');
            });
        });
    });
});


// Fungsi ttd
// Fungsi TTD (DESKTOP + HP)
document.addEventListener("DOMContentLoaded", function () {
    const sigBox = document.getElementById("sig");
    const inputTTD = document.getElementById("ttd");
    const clearBtn = document.getElementById("clear");

    if (!sigBox) return;

    const canvas = document.createElement("canvas");
    const ctx = canvas.getContext("2d");
    sigBox.appendChild(canvas);

    function resizeCanvas() {
        const ratio = window.devicePixelRatio || 1;
        canvas.width = sigBox.offsetWidth * ratio;
        canvas.height = 200 * ratio;
        canvas.style.width = sigBox.offsetWidth + "px";
        canvas.style.height = "200px";
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

    // Mouse (Desktop)
    canvas.addEventListener("mousedown", startDraw);
    canvas.addEventListener("mousemove", draw);
    canvas.addEventListener("mouseup", endDraw);
    canvas.addEventListener("mouseleave", endDraw);

    // Touch (HP)
    canvas.addEventListener("touchstart", startDraw, { passive: false });
    canvas.addEventListener("touchmove", draw, { passive: false });
    canvas.addEventListener("touchend", endDraw);

    // Clear
    window.clearTTD = function () {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        inputTTD.value = "";
    };

    clearBtn.addEventListener("click", window.clearTTD);
});

// Fungsi kamera
// === KAMERA UNIVERSAL (ADMIN & PUBLIC) ===
window.initCamera = function ({
    cameraBoxId,
    captureBtnId,
    fotoInputId,
    previewImgId
}) {
    const cameraBox = document.getElementById(cameraBoxId);
    const fotoInput = document.getElementById(fotoInputId);
    const preview = document.getElementById(previewImgId);
    const captureBtn = document.getElementById(captureBtnId);

    if (!cameraBox || !fotoInput || !captureBtn) return;

    let video = document.createElement('video');
    let stream = null;

    navigator.mediaDevices.getUserMedia({ video: true })
        .then(s => {
            stream = s;
            video.autoplay = true;
            video.playsInline = true;
            video.srcObject = stream;
            video.style.width = '100%';
            cameraBox.appendChild(video);
        })
        .catch(() => {
            Swal.fire('Kamera Tidak Aktif', 'Izin kamera ditolak', 'error');
        });

    captureBtn.addEventListener('click', function () {
        if (!video || !stream) return;

        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;

        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0);

        const base64 = canvas.toDataURL('image/jpeg');
        fotoInput.value = base64;

        if (preview) {
            preview.src = base64;
            preview.style.display = 'block';
        }

        stream.getTracks().forEach(track => track.stop());
        cameraBox.innerHTML = '';
    });
};

document.addEventListener('DOMContentLoaded', function () {
    initCamera({
        cameraBoxId: 'my_camera',
        captureBtnId: 'captureFoto',
        fotoInputId: 'foto',
        previewImgId: 'previewFoto'
    });
});

