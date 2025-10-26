<?php
require_once '../config.php';

header('Content-Type: application/json');

$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get ad for placement
    $placement = $_GET['placement'] ?? null;
    $userId = $_GET['user_id'] ?? null;
    
    if (!$placement) {
        jsonResponse(['success' => false, 'error' => 'Placement required'], 400);
    }
    
    try {
        // Get placement config
        $stmt = $db->prepare("SELECT * FROM ad_placements WHERE placement_key = ?");
        $stmt->execute([$placement]);
        $placementConfig = $stmt->fetch();
        
        if (!$placementConfig) {
            jsonResponse(['success' => false, 'error' => 'Placement not found'], 404);
        }
        
        // Get primary ad unit
        $adUnit = null;
        $fallbackUnits = [];
        
        foreach (['primary_ad_unit_id', 'secondary_ad_unit_id', 'tertiary_ad_unit_id'] as $key) {
            if ($placementConfig[$key]) {
                $stmt = $db->prepare("
                    SELECT au.*, an.name as network_name, an.is_enabled 
                    FROM ad_units au
                    JOIN ad_networks an ON au.network_id = an.id
                    WHERE au.id = ? AND au.is_active = 1 AND an.is_enabled = 1
                ");
                $stmt->execute([$placementConfig[$key]]);
                $unit = $stmt->fetch();
                
                if ($unit) {
                    if (!$adUnit) {
                        $adUnit = $unit;
                    } else {
                        $fallbackUnits[] = [
                            'network' => $unit['network_name'],
                            'ad_unit' => [
                                'id' => $unit['unit_code'],
                                'type' => $unit['unit_type']
                            ]
                        ];
                    }
                }
            }
        }
        
        if (!$adUnit) {
            jsonResponse(['success' => false, 'error' => 'No active ad unit found'], 404);
        }
        
        // Log impression if user_id provided
        if ($userId) {
            logAdEvent($userId, $placement, $adUnit['id'], 'impression');
        }
        
        jsonResponse([
            'success' => true,
            'placement' => $placement,
            'network' => $adUnit['network_name'],
            'ad_unit' => [
                'id' => $adUnit['unit_code'],
                'type' => $adUnit['unit_type'],
                'meta' => []
            ],
            'fallback' => $fallbackUnits,
            'frequency' => (int)$placementConfig['frequency']
        ]);
        
    } catch (Exception $e) {
        error_log("Ads Get Error: " . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'Failed to fetch ad'], 500);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Report ad event
    $data = json_decode(file_get_contents('php://input'), true);
    
    $userId = $data['user_id'] ?? null;
    $placement = $data['placement'] ?? null;
    $adUnitId = $data['ad_unit_id'] ?? null;
    $event = $data['event'] ?? null;
    
    if (!$userId || !$placement || !$event) {
        jsonResponse(['success' => false, 'error' => 'User ID, placement, and event required'], 400);
    }
    
    try {
        // Find ad unit by code
        $stmt = $db->prepare("SELECT id FROM ad_units WHERE unit_code = ?");
        $stmt->execute([$adUnitId]);
        $unit = $stmt->fetch();
        $unitDbId = $unit ? $unit['id'] : null;
        
        // Log event
        logAdEvent($userId, $placement, $unitDbId, $event);
        
        // If completed, update stats
        if ($event === 'complete' || $event === 'reward') {
            $db->beginTransaction();
            
            $stmt = $db->prepare("UPDATE user_stats SET ads_watched = ads_watched + 1 WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            // If it's energy recharge ad
            if ($placement === 'energy_recharge') {
                $energyRecharge = (int)getSetting('watch_ad_energy', 5);
                $stmt = $db->prepare("UPDATE users SET energy = LEAST(energy + ?, 100) WHERE id = ?");
                $stmt->execute([$energyRecharge, $userId]);
            }
            
            $db->commit();
        }
        
        jsonResponse([
            'success' => true,
            'message' => 'Event logged'
        ]);
        
    } catch (Exception $e) {
        if (isset($db) && $db->inTransaction()) {
            $db->rollBack();
        }
        error_log("Ads Event Error: " . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'Failed to log event'], 500);
    }
} else {
    jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
}
?>
