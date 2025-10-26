<?php
header('Content-Type: application/json');
require_once '../config.php';

$db = Database::getInstance()->getConnection();

// Handle GET request - fetch link data
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['code'])) {
        echo json_encode(['success' => false, 'message' => 'Short code required']);
        exit;
    }
    
    $code = $_GET['code'];
    
    try {
        $stmt = $db->prepare("
            SELECT 
                sl.*,
                t.title as task_title,
                t.description as task_description,
                t.url as task_url,
                u.unit_code as ad_unit_code,
                n.name as ad_network
            FROM short_links sl
            LEFT JOIN tasks t ON sl.task_id = t.id
            LEFT JOIN ad_units u ON sl.ad_unit_id = u.id
            LEFT JOIN ad_networks n ON u.network_id = n.id
            WHERE sl.short_code = ? AND sl.is_active = 1
        ");
        $stmt->execute([$code]);
        $link = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$link) {
            echo json_encode(['success' => false, 'message' => 'Link not found or inactive']);
            exit;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $link
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    exit;
}

// Handle POST request - track clicks/conversions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['action']) || !isset($input['code'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit;
    }
    
    $code = $input['code'];
    $action = $input['action'];
    
    try {
        if ($action === 'click') {
            // Increment click count
            $stmt = $db->prepare("UPDATE short_links SET click_count = click_count + 1, last_used = NOW() WHERE short_code = ?");
            $stmt->execute([$code]);
            
        } elseif ($action === 'convert') {
            // Increment conversion count
            $stmt = $db->prepare("UPDATE short_links SET conversion_count = conversion_count + 1 WHERE short_code = ?");
            $stmt->execute([$code]);
        }
        
        echo json_encode(['success' => true]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request method']);
