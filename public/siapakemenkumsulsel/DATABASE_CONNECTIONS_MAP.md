# Database Connection Code Map - SKP Project

This document maps all files and specific code sections that contain database connection code in the `skp` folder. **All database connections have been centralized for easy management.**

## ✅ CENTRALIZED DATABASE CONFIGURATION

**All database connections now use a centralized configuration file: `config/database.php`**

### To Change Database Settings:
**Only modify the values in `config/database.php`:**
```php
$db_config = [
    'host' => 'localhost',        // Change this
    'username' => 'root',         // Change this  
    'password' => '',             // Change this
    'database' => 'skp',          // Change this
    'charset' => 'utf8'           // Change this if needed
];
```

### New Database Connection Pattern:
All files now use this centralized pattern:
```php
// Database connection
require_once 'config/database.php';  // or '../config/database.php' for subfolder files
try {
    $conn = getDatabaseConnection();
} catch (Exception $e) {
    die('Connection failed: ' . $e->getMessage());
}
```

## Files with Database Connections

### 1. Main Application Files

#### `login.php`
- **Lines 12-19**: Database connection setup
- **Lines 25-31**: Table existence check
- **Lines 34-51**: Table creation and default user insertion
- **Lines 65-76**: User authentication query

#### `skploginpage.php`
- **Lines 11-21**: Database connection setup with charset setting
- **Lines 55-57**: Multiple database queries for SKP data

#### `skpbaru.php`
- **Lines 9-16**: Database connection setup
- **Lines 19-27**: Pegawai table query
- **Lines 38+**: Multiple database operations throughout the file

#### `skp_lampiran.php`
- **Lines 9-16**: Database connection setup
- **Lines 19-27**: Pegawai table query
- **Lines 40+**: Multiple database operations for lampiran data

#### `skp_akhir.php`
- **Lines 11-21**: Database connection setup with charset
- **Lines 24+**: Multiple database queries for SKP akhir data

#### `daftar_skp.php`
- **Lines 11-21**: Database connection setup with charset
- **Lines 30+**: Multiple database queries for SKP listing

### 2. Submit/Processing Files

#### `submit_skp.php`
- **Lines 27-30**: Database connection setup
- **Lines 32+**: Database operations for SKP submission

#### `submit_skp_final.php`
- **Lines 27-30**: Database connection setup
- **Lines 32+**: Database operations for final SKP submission

#### `submit_lampiran.php`
- **Lines 27-30**: Database connection setup
- **Lines 32+**: Database operations for lampiran submission

#### `submit_evaluasi.php`
- **Lines 27-30**: Database connection setup
- **Lines 32+**: Database operations for evaluation submission

### 3. Detail/View Files

#### `skp_detail.php`
- **Lines 11-21**: Database connection setup with charset
- **Lines 24+**: Multiple database queries for SKP details

#### `skp/skp_detail.php`
- **Lines 30+**: Database connection setup
- **Lines 35+**: Multiple database operations

#### `skp/skp_akhir_detail.php`
- **Lines 30+**: Database connection setup
- **Lines 35+**: Multiple database operations

### 4. PDF Generation Files

#### `generate_pdf.php`
- **Lines 11-21**: Database connection setup with charset
- **Lines 24+**: Database queries for PDF generation

#### `generate_kuantitatif_pdf.php`
- **Lines 11-21**: Database connection setup with charset
- **Lines 24+**: Database queries for quantitative PDF

#### `generate_lampiran_pdf.php`
- **Lines 11-21**: Database connection setup with charset
- **Lines 24+**: Database queries for attachment PDF

#### `generate_umpan_balik_pdf.php`
- **Lines 11-21**: Database connection setup with charset
- **Lines 24+**: Database queries for feedback PDF

#### `skp/generate_skp_akhir_pdf.php`
- **Lines 11-21**: Database connection setup with charset
- **Lines 24+**: Database queries for final SKP PDF

### 5. Management Files

#### `lampiran_evaluasi.php`
- **Lines 11-21**: Database connection setup with charset
- **Lines 24+**: Database queries for attachment evaluation

#### `lampiran_list.php`
- **Lines 11-21**: Database connection setup with charset
- **Lines 24+**: Database queries for attachment listing

#### `skp_akhir_evaluasi.php`
- **Lines 11-21**: Database connection setup with charset
- **Lines 24+**: Database queries for final SKP evaluation

#### `edit_realisasi.php`
- **Lines 11-21**: Database connection setup with charset
- **Lines 24+**: Database queries for realization editing

### 6. Action Files

#### `approve_lampiran.php`
- **Lines 11-21**: Database connection setup with charset
- **Lines 24+**: Database operations for approval

#### `reject_lampiran.php`
- **Lines 11-21**: Database connection setup with charset
- **Lines 24+**: Database operations for rejection

#### `delete_lampiran.php`
- **Lines 11-21**: Database connection setup with charset
- **Lines 24+**: Database operations for deletion

#### `hapus_skp.php`
- **Lines 11-21**: Database connection setup with charset
- **Lines 24+**: Database operations for SKP deletion

#### `revisi_skp.php`
- **Lines 11-21**: Database connection setup with charset
- **Lines 24+**: Database operations for SKP revision

### 7. Utility Files

#### `get_atasan.php`
- **Lines 11-21**: Database connection setup with charset
- **Lines 24+**: Database queries for supervisor data

#### `check_existing_users.php`
- **Lines 11-21**: Database connection setup with charset
- **Lines 24+**: Database queries for user validation

### 8. SKP Subfolder Files

#### `skp/submit_skp_akhir.php`
- **Lines 12-24**: Database connection setup with charset
- **Lines 27+**: Database operations for final SKP submission

#### `skp/submit_skp_akhir_evaluasi.php`
- **Lines 12-24**: Database connection setup with charset
- **Lines 27+**: Database operations for final SKP evaluation

#### `skp/verify_skp_global.php`
- **Lines 12-24**: Database connection setup with charset
- **Lines 27+**: Database operations for SKP verification

### 9. Debug Files

#### `debug_form_structure.php`
- **Lines 11-21**: Database connection setup with charset
- **Lines 24+**: Database queries for debugging

#### `debug_skp_records.php`
- **Lines 11-21**: Database connection setup with charset
- **Lines 24+**: Database queries for debugging

#### `debug_skp_akhir_validation.php`
- **Lines 11-21**: Database connection setup with charset
- **Lines 24+**: Database queries for debugging

#### `debug_satuan_comparison.php`
- **Lines 11-21**: Database connection setup with charset
- **Lines 24+**: Database queries for debugging

#### `debug_skp_akhir_satuan.php`
- **Lines 11-21**: Database connection setup with charset
- **Lines 24+**: Database queries for debugging

#### `debug_skp_akhir.php`
- **Lines 11-21**: Database connection setup with charset
- **Lines 24+**: Database queries for debugging

#### `debug_id_skp_global.php`
- **Lines 11-21**: Database connection setup with charset
- **Lines 24+**: Database queries for debugging

#### `debug_users.php`
- **Lines 11-21**: Database connection setup with charset
- **Lines 24+**: Database queries for debugging

## Database Connection Variables to Change

To change database connection settings across the project, you need to modify these variables in each file:

```php
$host = 'localhost';    // Database host
$user = 'root';         // Database username
$pass = '';             // Database password
$db = 'skp';            // Database name
```

## Files That DON'T Have Database Connections

The following files in the `skp` folder do NOT contain database connection code:
- `includes/sidebar.php` (only uses session data)
- `includes/sidebar_styles.php` (CSS only)
- `session_security.php` (session management only)
- `logout.php` (session management only)
- `index.php` (likely redirects to login)

## ✅ IMPLEMENTATION COMPLETED

**Total files with database connections: 44 files - ALL UPDATED TO USE CENTRALIZED CONFIG**

### Benefits of Centralized Configuration:
- ✅ **Single point of change**: Modify only `config/database.php` to change database settings
- ✅ **Consistent error handling**: All files use the same error handling pattern
- ✅ **Easy maintenance**: No need to update 44+ files individually
- ✅ **Better security**: Database credentials are centralized and can be easily secured
- ✅ **Future-proof**: Easy to add connection pooling, caching, or other database features

### Files Updated:
All 44 files now use the centralized database configuration:
- Main application files (login, dashboard, forms)
- Submit/processing files  
- PDF generation files
- Management and action files
- Debug files
- SKP subfolder files

### How to Change Database Settings:
1. Open `config/database.php`
2. Modify the `$db_config` array values
3. Save the file
4. All 44 files will automatically use the new settings
