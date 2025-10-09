<?php
$host = 'localhost';
$dbname = 'rekap_konten';
$username = 'root';
$password = '';

try {
    // Koneksi PDO dengan charset utf8mb4 dan persistent connection opsional
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,        // Error handling
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,   // Default fetch associative array
            PDO::ATTR_PERSISTENT => false                        // Persistent connection (ubah true jika mau)
        ]
    );
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
