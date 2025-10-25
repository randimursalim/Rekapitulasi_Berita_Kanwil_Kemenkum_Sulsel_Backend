// Form Tambah Kegiatan Helper Functions
function to24HourFormat(timeStr) {
    const [hour, minute] = timeStr.split(":");
    return hour.padStart(2, "0") + "." + minute.padStart(2, "0");
}
