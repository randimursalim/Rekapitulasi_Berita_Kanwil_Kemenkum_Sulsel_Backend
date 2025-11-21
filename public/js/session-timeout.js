// Session Timeout Management
document.addEventListener('DOMContentLoaded', function() {
    let sessionTimeout = 15 * 60 * 1000; // 15 menit dalam milidetik
    let warningTime = 2 * 60 * 1000; // Warning 2 menit sebelum timeout
    let lastActivity = Date.now();
    let warningShown = false;
    let timeoutShown = false; // Flag untuk mencegah popup timeout muncul berulang kali
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
        
        // Get BASE_URL untuk path dinamis
        const baseUrl = (typeof window.BASE_URL !== 'undefined' && window.BASE_URL) ? window.BASE_URL : '';
        // Build URL: jika baseUrl ada, pastikan tidak ada double slash
        let updateUrl = 'index.php?page=update-activity';
        if (baseUrl) {
            updateUrl = baseUrl.replace(/\/$/, '') + '/index.php?page=update-activity';
        }
        
        // Kirim AJAX request untuk update session
        fetch(updateUrl, {
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
                    // Get BASE_URL untuk path dinamis
                    const baseUrl = (typeof window.BASE_URL !== 'undefined' && window.BASE_URL) ? window.BASE_URL : '';
                    let logoutUrl = 'index.php?page=logout';
                    if (baseUrl) {
                        logoutUrl = baseUrl.replace(/\/$/, '') + '/index.php?page=logout';
                    }
                    window.location.href = logoutUrl;
                }
            });
        }
        
        // Auto logout jika timeout
        if (timeUntilTimeout <= 0 && !timeoutShown) {
            timeoutShown = true; // Set flag untuk mencegah popup muncul berulang
            
            // Get BASE_URL untuk path dinamis
            // Coba beberapa cara untuk mendapatkan base URL
            let baseUrl = '';
            if (typeof window.BASE_URL !== 'undefined' && window.BASE_URL) {
                baseUrl = window.BASE_URL;
            } else {
                // Fallback: dapatkan base path dari window.location
                const path = window.location.pathname;
                const pathParts = path.split('/');
                if (pathParts.length > 2 && pathParts[1] === 'rekap-konten') {
                    baseUrl = '/rekap-konten/public';
                }
            }
            
            // Build logout URL
            let logoutUrl = 'index.php?page=logout&timeout=1';
            if (baseUrl) {
                logoutUrl = baseUrl.replace(/\/$/, '') + '/index.php?page=logout&timeout=1';
            }
            
            // Debug (dapat dihapus di production)
            console.debug('Session timeout - Redirecting to:', logoutUrl);
            
            // Tampilkan popup dan langsung setup redirect
            Swal.fire({
                title: 'Sesi Berakhir',
                text: 'Sesi Anda telah berakhir karena tidak ada aktivitas selama 15 menit. Anda akan diarahkan ke halaman login.',
                icon: 'info',
                confirmButtonText: 'Login Kembali',
                allowOutsideClick: false,
                allowEscapeKey: false,
                timer: 3000, // Auto close dalam 3 detik
                timerProgressBar: true,
                showConfirmButton: true
            }).then((result) => {
                // Redirect ke logout (yang akan destroy session) lalu redirect ke login
                // Pastikan redirect selalu terjadi, apapun hasilnya
                window.location.replace(logoutUrl);
            });
            
            // Backup: Pastikan redirect terjadi setelah 3 detik bahkan jika popup error
            setTimeout(function() {
                window.location.replace(logoutUrl);
            }, 3000);
        }
    }, 30000); // Cek setiap 30 detik
});
