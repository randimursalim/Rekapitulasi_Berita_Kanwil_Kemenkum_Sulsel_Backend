<?php
require_once __DIR__ . '/../app/controllers/KontenController.php';

// buat instance controller
$controller = new KontenController();

// jalankan method index
$controller->index();
