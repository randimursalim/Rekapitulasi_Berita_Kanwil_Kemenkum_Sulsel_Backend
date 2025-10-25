// Filter Log Aktivitas Functionality
document.addEventListener('DOMContentLoaded', function() {
    const filterBtn = document.getElementById("filterBtn");
    const resetBtn = document.getElementById("resetBtn");
    const startDateInput = document.getElementById("startDate");
    const endDateInput = document.getElementById("endDate");
    const filterJenis = document.getElementById("filterJenis");
    const filterKategori = document.getElementById("filterKategori");

    function applyFilters() {
        const startDate = startDateInput.value ? new Date(startDateInput.value) : null;
        const endDate = endDateInput.value ? new Date(endDateInput.value) : null;
        const jenis = filterJenis.value.toLowerCase();
        const kategori = filterKategori.value.toLowerCase();

        const totalRows = document.querySelectorAll(".activity-data .data.no .data-list").length;

        for (let i = 0; i < totalRows; i++) {
            const dateText = document.querySelectorAll(".activity-data .data.date .data-list")[i]?.innerText || '';
            const jenisText = document.querySelectorAll(".activity-data .data.jenis .data-list")[i]?.innerText.toLowerCase() || '';
            const kategoriText = document.querySelectorAll(".activity-data .data.kategori .data-list")[i]?.innerText.toLowerCase() || '';
            const dateVal = new Date(dateText);

            const matchDate = (!startDate || !endDate) || (dateVal >= startDate && dateVal <= endDate);
            const matchJenis = (jenis === "all" || jenisText.includes(jenis));
            const matchKategori = (kategori === "all" || kategoriText.includes(kategori));

            const visible = matchDate && matchJenis && matchKategori;

            document.querySelectorAll(".activity-data .data").forEach(col => {
                if (col.children[i + 1]) col.children[i + 1].style.display = visible ? "" : "none";
            });
        }
    }

    if (filterBtn) filterBtn.addEventListener("click", applyFilters);
    if (filterJenis) filterJenis.addEventListener("change", applyFilters);
    if (filterKategori) filterKategori.addEventListener("change", applyFilters);

    if (resetBtn) {
        resetBtn.addEventListener("click", () => {
            startDateInput.value = "";
            endDateInput.value = "";
            filterJenis.value = "all";
            filterKategori.value = "all";
            document.querySelectorAll(".activity-data .data .data-list").forEach(el => el.style.display = "");
        });
    }
});
