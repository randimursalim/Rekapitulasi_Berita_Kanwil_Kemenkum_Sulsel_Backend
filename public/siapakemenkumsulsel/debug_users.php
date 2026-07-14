<?php
// Database connection
require_once 'config/database.php';
try {
    $conn = getDatabaseConnection();
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

echo "<h2>Debug Users Table</h2>";

// Check if users table exists
$table_check = $conn->query("SHOW TABLES LIKE 'users'");
if ($table_check->num_rows == 0) {
    echo "<p style='color: red;'>❌ Users table does not exist!</p>";
    
    // Create users table
    echo "<p>Creating users table...</p>";
    $create_table_sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        nama VARCHAR(100) NOT NULL,
        nip VARCHAR(20) NOT NULL,
        jabatan VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($create_table_sql)) {
        echo "<p style='color: green;'>✅ Users table created successfully!</p>";
        
        // Insert default admin user
        $default_password = password_hash('admin123', PASSWORD_DEFAULT);
        $insert_admin = "INSERT INTO users (username, password, nama, nip, jabatan) VALUES ('admin', '$default_password', 'Administrator', '123456789', 'Admin Sistem')";
        
        if ($conn->query($insert_admin)) {
            echo "<p style='color: green;'>✅ Default admin user created successfully!</p>";
        } else {
            echo "<p style='color: red;'>❌ Error creating admin user: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Error creating users table: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ Users table exists!</p>";
}

// Show all users in the table
echo "<h3>Users in Database:</h3>";
$users_query = $conn->query("SELECT id, username, nama, nip, jabatan, created_at FROM users");
if ($users_query && $users_query->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Username</th><th>Nama</th><th>NIP</th><th>Jabatan</th><th>Created</th></tr>";
    while ($row = $users_query->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
        echo "<td>" . htmlspecialchars($row['nip']) . "</td>";
        echo "<td>" . htmlspecialchars($row['jabatan']) . "</td>";
        echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ No users found in the table!</p>";
}

// Test login query
echo "<h3>Testing Login Query:</h3>";
$test_username = 'admin';
$stmt = $conn->prepare("SELECT id, username, password, nama, nip, jabatan FROM users WHERE username = ?");
if ($stmt) {
    $stmt->bind_param('s', $test_username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        echo "<p style='color: green;'>✅ User 'admin' found!</p>";
        echo "<p>User ID: " . $user['id'] . "</p>";
        echo "<p>Username: " . htmlspecialchars($user['username']) . "</p>";
        echo "<p>Nama: " . htmlspecialchars($user['nama']) . "</p>";
        echo "<p>NIP: " . htmlspecialchars($user['nip']) . "</p>";
        echo "<p>Jabatan: " . htmlspecialchars($user['jabatan']) . "</p>";
        
        // Test password verification
        $test_password = 'admin123';
        if (password_verify($test_password, $user['password'])) {
            echo "<p style='color: green;'>✅ Password verification successful!</p>";
        } else {
            echo "<p style='color: red;'>❌ Password verification failed!</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ User 'admin' not found!</p>";
    }
    $stmt->close();
} else {
    echo "<p style='color: red;'>❌ Error preparing statement: " . $conn->error . "</p>";
}

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>
