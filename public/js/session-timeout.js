// Session Timeout Management
document.addEventListener('DOMContentLoaded', function() {
    let sessionTimeout = 15 * 60 * 1000; // 15 menit dalam milidetik
    let warningTime = 2 * 60 * 1000; // Warning 2 menit sebelum timeout
    let lastActivity = Date.now();
    let warningShown = false;
    let lastServerUpdate = 0; // Waktu terakhir update ke server
    let updateThrottle = 60 * 1000; // Update ke server maksimal sekali setiap 60 detik (1 menit)
    let pendingUpdate = false; // Flag untuk menandakan ada update yang pending
    
    // Update aktivitas lokal (tanpa request ke server)
    function updateActivityLocal() {
        lastActivity = Date.now();
        warningShown = false;
        pendingUpdate = true; // Mark bahwa ada update yang perlu dikirim
    }
    
    // Kirim update ke server (dengan throttling)
    function updateActivityServer() {
        const now = Date.now();
        
        // Hanya kirim request jika sudah lebih dari updateThrottle sejak request terakhir
        // Atau jika ada pending update yang perlu dikirim
        if (now - lastServerUpdate < updateThrottle) {
            if (!pendingUpdate) {
                return; // Skip jika masih dalam throttle period dan tidak ada pending update
            }
            // Jika ada pending update tapi masih dalam throttle period,
            // tunggu sampai throttle period habis (akan di-handle oleh interval)
            return;
        }
        
        // Clear pending flag dan update timestamp
        lastServerUpdate = now;
        pendingUpdate = false;
        
        // Kirim AJAX request untuk update session
        fetch('index.php?page=update-activity', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            credentials: 'same-origin', // Include cookies/session
            body: 'update_activity=1'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            // Activity updated successfully
        })
        .catch(error => {
            // Activity update failed - handled silently
            console.debug('Activity update failed:', error);
        });
    }
    
    // Event listeners untuk aktivitas user (tanpa mousemove yang terlalu sering)
    // Hanya track event yang signifikan: click, keypress, scroll, touch
    const activityEvents = ['mousedown', 'keypress', 'scroll', 'touchstart', 'click'];
    activityEvents.forEach(event => {
        document.addEventListener(event, updateActivityLocal, true);
    });
    
    // Update activity ke server secara berkala (setiap 60 detik)
    // Ini memastikan session tetap aktif meskipun user tidak melakukan aktivitas
    setInterval(function() {
        if (pendingUpdate || (Date.now() - lastServerUpdate >= updateThrottle)) {
            updateActivityServer();
        }
    }, updateThrottle); // Check setiap 60 detik
    
    // Update awal saat halaman dimuat
    updateActivityServer();
    
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
                    updateActivityLocal();
                    updateActivityServer(); // Force immediate update when user confirms
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
