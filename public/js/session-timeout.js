// Session Timeout Management
document.addEventListener('DOMContentLoaded', function() {
    let sessionTimeout = 15 * 60 * 1000; // 15 menit dalam milidetik
    let warningTime = 2 * 60 * 1000; // Warning 2 menit sebelum timeout
    let lastActivity = Date.now();
    let warningShown = false;
    
    // Update aktivitas saat user melakukan aksi
    function updateActivity() {
        lastActivity = Date.now();
        warningShown = false;
        
        // Kirim AJAX request untuk update session
        fetch('index.php?page=update-activity', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'update_activity=1'
        }).catch(error => {
            // Activity update failed - handled silently
        });
    }
    
    // Event listeners untuk aktivitas user
    const activityEvents = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
    activityEvents.forEach(event => {
        document.addEventListener(event, updateActivity, true);
    });
    
    // Cek timeout setiap 30 detik
    setInterval(function() {
        const now = Date.now();
        const timeSinceActivity = now - lastActivity;
        const timeUntilTimeout = sessionTimeout - timeSinceActivity;
        
        // Warning 2 menit sebelum timeout
        if (timeUntilTimeout <= warningTime && timeUntilTimeout > 0 && !warningShown) {
            warningShown = true;
            const minutesLeft = Math.ceil(timeUntilTimeout / 60000);
            
            Swal.fire({
                title: 'Peringatan Sesi',
                html: `Sesi Anda akan berakhir dalam <strong>${minutesLeft} menit</strong> karena tidak ada aktivitas.<br><br>Klik "Perpanjang Sesi" untuk melanjutkan.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Perpanjang Sesi',
                cancelButtonText: 'Logout',
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                timer: warningTime,
                timerProgressBar: true,
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then((result) => {
                if (result.isConfirmed) {
                    updateActivity();
                    Swal.fire({
                        title: 'Sesi Diperpanjang!',
                        text: 'Sesi Anda telah diperpanjang.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    window.location.href = 'index.php?page=logout';
                }
            });
        }
        
        // Auto logout jika timeout
        if (timeUntilTimeout <= 0) {
            Swal.fire({
                title: 'Sesi Berakhir',
                text: 'Sesi Anda telah berakhir karena tidak ada aktivitas selama 15 menit.',
                icon: 'info',
                confirmButtonText: 'Login Kembali',
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then(() => {
                window.location.href = 'index.php?page=login&timeout=1';
            });
        }
    }, 30000); // Cek setiap 30 detik
});
