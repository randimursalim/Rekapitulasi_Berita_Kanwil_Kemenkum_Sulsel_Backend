<?php
// config/database.php - Secure Database Connection
require_once __DIR__ . '/database_secure.php';

// Gunakan secure database configuration
$conn = DatabaseConfig::createConnection();
