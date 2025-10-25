// Modal Dashboard Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Data dari PHP
    const detailBerita = window.detailBerita || [];
    const detailMedsos = window.detailMedsos || [];

    // === Modal Dashboard ===
    const modalDashboard = document.getElementById("detailModal");
    const modalTitle = document.getElementById("modalTitle");
    const modalList = document.getElementById("modalList");

    // Cek apakah modal elements ada sebelum mengaksesnya
    if (modalDashboard && modalTitle && modalList) {
        const closeBtnDashboard = modalDashboard.querySelector(".close");

        function showDetail(type) {
            modalList.innerHTML = '';
            let data = [];

            if (type === 'berita') {
                modalTitle.textContent = "Rincian Total Berita";
                data = detailBerita;
            } else if (type === 'medsos') {
                modalTitle.textContent = "Rincian Postingan Medsos";
                data = detailMedsos;
            }

            data.forEach(item => {
                const li = document.createElement("li");
                li.textContent = `${item.name}: ${item.value}`;
                modalList.appendChild(li);
            });

            modalDashboard.style.display = "block";
        }

        function closeModalDashboard() {
            modalDashboard.style.display = "none";
        }

        // Event listeners hanya dipasang jika closeBtnDashboard ada
        if (closeBtnDashboard) {
            closeBtnDashboard.addEventListener("click", closeModalDashboard);
        }
        
        window.addEventListener("click", function(e) {
            if (e.target === modalDashboard) closeModalDashboard();
        });

        // Expose function globally untuk digunakan di halaman lain
        window.showDetail = showDetail;
        window.closeModalDashboard = closeModalDashboard;
    }

    // Event listener box dashboard (hanya jika modal ada)
    if (modalDashboard && modalTitle && modalList) {
        document.querySelectorAll(".boxes .box[data-type]").forEach(box => {
            box.addEventListener("click", () => {
                const type = box.getAttribute("data-type");
                if (window.showDetail) {
                    window.showDetail(type);
                }
            });
        });
    }
});
