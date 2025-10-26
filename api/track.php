<?php
require_once '../config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$linkId = $_GET['link_id'] ?? 0;
$userId = $_GET['user_id'] ?? 0;

if ($action === 'conversion' && $linkId) {
    $db = Database::getInstance()->getConnection();
    
    // Update conversion count
    $stmt = $db->prepare("UPDATE short_links SET conversions = conversions + 1 WHERE id = ?");
    $stmt->execute([$linkId]);
    
    // Log the event if user is provided
    if ($userId) {
        logAdEvent($userId, 'shortlink', null, 'complete');
    }
    
    jsonResponse(['success' => true, 'message' => 'Conversion recorded']);
} else {
    jsonResponse(['success' => false, 'error' => 'Invalid request'], 400);
}
?>
