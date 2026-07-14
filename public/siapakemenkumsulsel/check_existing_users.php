<?php
// Database connection
require_once 'config/database.php';
try {
    $conn = getDatabaseConnection();
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

echo "<h2>Checking Existing User Table</h2>";

// Check what tables exist
echo "<h3>All Tables in Database:</h3>";
$tables_query = $conn->query("SHOW TABLES");
if ($tables_query && $tables_query->num_rows > 0) {
    echo "<ul>";
    while ($row = $tables_query->fetch_array()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
}

// Check if there's a user table (case insensitive)
$user_tables = ['user', 'users', 'User', 'Users'];
$found_table = null;

foreach ($user_tables as $table_name) {
    $check_table = $conn->query("SHOW TABLES LIKE '$table_name'");
    if ($check_table && $check_table->num_rows > 0) {
        $found_table = $table_name;
        break;
    }
}

if ($found_table) {
    echo "<h3>Found User Table: '$found_table'</h3>";
    
    // Show table structure
    echo "<h4>Table Structure:</h4>";
    $structure_query = $conn->query("DESCRIBE $found_table");
    if ($structure_query && $structure_query->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $structure_query->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Show table data
    echo "<h4>Table Data:</h4>";
    $data_query = $conn->query("SELECT * FROM $found_table");
    if ($data_query && $data_query->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        $first_row = true;
        while ($row = $data_query->fetch_assoc()) {
            if ($first_row) {
                echo "<tr>";
                foreach (array_keys($row) as $column) {
                    echo "<th>" . htmlspecialchars($column) . "</th>";
                }
                echo "</tr>";
                $first_row = false;
            }
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No data found in table.</p>";
    }
} else {
    echo "<p style='color: red;'>No user table found!</p>";
}

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>
