<?php
require_once '../config.php';
requireAdmin();

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'cleared' => []
];

try {
    // Use the centralized cache clearing function
    $clearSessions = isset($_POST['clear_sessions']) && $_POST['clear_sessions'] === 'true';
    $response['cleared'] = clearAllCache($clearSessions);
    
    // Clear application-specific cache directories
    $cacheDirectories = [
        __DIR__ . '/../cache',
        __DIR__ . '/../tmp',
        __DIR__ . '/../var/cache'
    ];
    
    foreach ($cacheDirectories as $dir) {
        if (is_dir($dir)) {
            $filesCleared = 0;
            $files = glob($dir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                    $filesCleared++;
                }
            }
            if ($filesCleared > 0) {
                $response['cleared'][] = basename($dir) . " directory ($filesCleared files)";
            }
        }
    }
    
    // Log the cache clear action (if admin_logs table exists)
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO admin_logs (admin_id, action, details, ip_address) VALUES (?, 'cache_clear', ?, ?)");
        $stmt->execute([
            $_SESSION['admin_id'],
            json_encode(['cleared' => $response['cleared'], 'timestamp' => date('Y-m-d H:i:s')]),
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    } catch (Exception $logError) {
        // Table doesn't exist, just log to error log instead
        error_log("Cache cleared by admin ID: " . $_SESSION['admin_id'] . " from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . " - " . json_encode($response['cleared']));
    }
    
    $response['success'] = true;
    $response['message'] = 'Cache cleared successfully!';
    
    if (empty($response['cleared'])) {
        $response['message'] = 'No cache systems found to clear.';
        $response['cleared'][] = 'None (no cache systems available)';
    }
    
} catch (Exception $e) {
    $response['message'] = 'Error clearing cache: ' . $e->getMessage();
    error_log("Cache clear error: " . $e->getMessage());
}

echo json_encode($response);
exit;
?>
