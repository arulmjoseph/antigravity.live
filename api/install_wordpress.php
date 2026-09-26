<?php
header('Content-Type: application/json');

$rootDir = dirname(__DIR__);
$wpConfigPath = $rootDir . '/wp-config.php';
$wpLoadPath   = $rootDir . '/wp-load.php';

$logs = [];

if (!file_exists($wpConfigPath)) {
    echo json_encode([
        'success' => false,
        'message' => 'wp-config.php not found. Please create wp-config.php from wp-config-sample.php.'
    ]);
    exit;
}

// Verify wp-load.php presence
if (!file_exists($wpLoadPath)) {
    echo json_encode([
        'success' => false,
        'message' => 'WordPress core files missing (wp-load.php not found).'
    ]);
    exit;
}

// Attempt to load WordPress environment
try {
    define('WP_USE_THEMES', false);
    require_once $wpLoadPath;
    
    global $wpdb;
    $dbName = DB_NAME;
    $tables = $wpdb->get_results("SHOW TABLES");
    $tableCount = count($tables);

    echo json_encode([
        'success' => true,
        'message' => "✓ Connected to WordPress Database ($dbName) with $tableCount existing tables."
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => "Database connection error: " . $e->getMessage()
    ]);
}
