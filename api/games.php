<?php
require_once '../config.php';

header('Content-Type: application/json');

$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get games list
    $userId = $_GET['user_id'] ?? null;
    
    if (!$userId) {
        jsonResponse(['success' => false, 'error' => 'User ID required'], 400);
    }
    
    try {
        // Get all active games
        $stmt = $db->prepare("SELECT * FROM games WHERE is_active = 1 ORDER BY id DESC");
        $stmt->execute();
        $games = $stmt->fetchAll();
        
        $gamesWithStatus = [];
        $today = date('Y-m-d');
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        
        foreach ($games as $game) {
            // Get user game plays
            $stmt = $db->prepare("SELECT * FROM user_games WHERE user_id = ? AND game_id = ?");
            $stmt->execute([$userId, $game['id']]);
            $userGame = $stmt->fetch();
            
            $playsRemaining = -1; // unlimited
            $canPlay = true;
            
            if ($game['play_limit_type'] !== 'unlimited' && $game['play_limit'] > 0) {
                if ($userGame) {
                    // Check if needs reset
                    if ($game['play_limit_type'] === 'daily' && $userGame['last_reset_daily'] !== $today) {
                        $stmt = $db->prepare("UPDATE user_games SET plays_today = 0, last_reset_daily = ? WHERE id = ?");
                        $stmt->execute([$today, $userGame['id']]);
                        $userGame['plays_today'] = 0;
                    } elseif ($game['play_limit_type'] === 'weekly' && $userGame['last_reset_weekly'] !== $weekStart) {
                        $stmt = $db->prepare("UPDATE user_games SET plays_this_week = 0, last_reset_weekly = ? WHERE id = ?");
                        $stmt->execute([$weekStart, $userGame['id']]);
                        $userGame['plays_this_week'] = 0;
                    }
                    
                    $plays = $game['play_limit_type'] === 'daily' ? $userGame['plays_today'] : $userGame['plays_this_week'];
                    $playsRemaining = max(0, $game['play_limit'] - $plays);
                    $canPlay = $playsRemaining > 0;
                } else {
                    $playsRemaining = $game['play_limit'];
                }
            }
            
            $gamesWithStatus[] = [
                'id' => $game['id'],
                'name' => $game['name'],
                'icon' => $game['icon'],
                'game_url' => $game['game_url'],
                'reward' => (float)$game['reward'],
                'play_limit_type' => $game['play_limit_type'],
                'plays_remaining' => $playsRemaining,
                'can_play' => $canPlay,
                'ad_network' => $game['ad_network'],
                'ad_unit_id' => $game['ad_unit_id']
            ];
        }
        
        jsonResponse([
            'success' => true,
            'games' => $gamesWithStatus
        ]);
        
    } catch (Exception $e) {
        error_log("Games Get Error: " . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'Failed to fetch games'], 500);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Complete game play
    $data = json_decode(file_get_contents('php://input'), true);
    
    $userId = $data['user_id'] ?? null;
    $gameId = $data['game_id'] ?? null;
    
    if (!$userId || !$gameId) {
        jsonResponse(['success' => false, 'error' => 'User ID and Game ID required'], 400);
    }
    
    try {
        $db->beginTransaction();
        
        // Get game
        $stmt = $db->prepare("SELECT * FROM games WHERE id = ? AND is_active = 1");
        $stmt->execute([$gameId]);
        $game = $stmt->fetch();
        
        if (!$game) {
            $db->rollBack();
            jsonResponse(['success' => false, 'error' => 'Game not found'], 404);
        }
        
        $today = date('Y-m-d');
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        
        // Get or create user game record
        $stmt = $db->prepare("SELECT * FROM user_games WHERE user_id = ? AND game_id = ?");
        $stmt->execute([$userId, $gameId]);
        $userGame = $stmt->fetch();
        
        if (!$userGame) {
            $stmt = $db->prepare("INSERT INTO user_games (user_id, game_id, last_reset_daily, last_reset_weekly) VALUES (?, ?, ?, ?)");
            $stmt->execute([$userId, $gameId, $today, $weekStart]);
            $userGame = [
                'plays_today' => 0,
                'plays_this_week' => 0,
                'last_reset_daily' => $today,
                'last_reset_weekly' => $weekStart
            ];
        }
        
        // Check play limits
        if ($game['play_limit_type'] !== 'unlimited' && $game['play_limit'] > 0) {
            $plays = $game['play_limit_type'] === 'daily' ? $userGame['plays_today'] : $userGame['plays_this_week'];
            
            if ($plays >= $game['play_limit']) {
                $db->rollBack();
                jsonResponse(['success' => false, 'error' => 'Play limit reached'], 400);
            }
        }
        
        // Update play count
        if ($game['play_limit_type'] === 'daily') {
            $stmt = $db->prepare("UPDATE user_games SET plays_today = plays_today + 1, last_played = NOW() WHERE user_id = ? AND game_id = ?");
        } elseif ($game['play_limit_type'] === 'weekly') {
            $stmt = $db->prepare("UPDATE user_games SET plays_this_week = plays_this_week + 1, last_played = NOW() WHERE user_id = ? AND game_id = ?");
        } else {
            $stmt = $db->prepare("UPDATE user_games SET last_played = NOW() WHERE user_id = ? AND game_id = ?");
        }
        $stmt->execute([$userId, $gameId]);
        
        // Award coins
        updateUserCoins($userId, $game['reward'], true);
        
        // Update stats
        $stmt = $db->prepare("UPDATE user_stats SET games_played = games_played + 1 WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        // Add transaction
        addTransaction($userId, 'game', $game['reward'], "Game played: {$game['name']}");
        
        // Get updated coins
        $stmt = $db->prepare("SELECT coins FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $totalCoins = $stmt->fetchColumn();
        
        $db->commit();
        
        jsonResponse([
            'success' => true,
            'reward' => (float)$game['reward'],
            'total_coins' => (float)$totalCoins
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        error_log("Games Error: " . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'Failed to process game'], 500);
    }
} else {
    jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
}
?>
