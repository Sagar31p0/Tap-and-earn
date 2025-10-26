<?php
require_once '../config.php';

header('Content-Type: application/json');

$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get tasks list
    $userId = $_GET['user_id'] ?? null;
    
    if (!$userId) {
        jsonResponse(['success' => false, 'error' => 'User ID required'], 400);
    }
    
    try {
        // Get all active tasks
        $stmt = $db->prepare("SELECT * FROM tasks WHERE is_active = 1 ORDER BY sort_order ASC, id DESC");
        $stmt->execute();
        $tasks = $stmt->fetchAll();
        
        $tasksWithStatus = [];
        $today = date('Y-m-d');
        
        foreach ($tasks as $task) {
            // Get user task status
            $stmt = $db->prepare("SELECT * FROM user_tasks WHERE user_id = ? AND task_id = ?");
            $stmt->execute([$userId, $task['id']]);
            $userTask = $stmt->fetch();
            
            $status = 'available';
            $canClaim = false;
            
            if ($userTask) {
                if ($task['type'] === 'daily') {
                    // Check if needs reset
                    if ($userTask['last_reset'] !== $today) {
                        $stmt = $db->prepare("UPDATE user_tasks SET status = 'pending', last_reset = ? WHERE id = ?");
                        $stmt->execute([$today, $userTask['id']]);
                        $status = 'available';
                    } else {
                        $status = $userTask['status'];
                        if ($status === 'completed') {
                            $status = 'claimed';
                        }
                    }
                } else {
                    $status = $userTask['status'];
                    if ($status === 'completed') {
                        $status = 'claimed';
                    }
                }
            }
            
            $tasksWithStatus[] = [
                'id' => $task['id'],
                'title' => $task['title'],
                'description' => $task['description'],
                'url' => $task['url'],
                'reward' => (float)$task['reward'],
                'icon' => $task['icon'],
                'type' => $task['type'],
                'status' => $status,
                'ad_network' => $task['ad_network']
            ];
        }
        
        jsonResponse([
            'success' => true,
            'tasks' => $tasksWithStatus
        ]);
        
    } catch (Exception $e) {
        error_log("Tasks Get Error: " . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'Failed to fetch tasks'], 500);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Start or verify task
    $data = json_decode(file_get_contents('php://input'), true);
    
    $userId = $data['user_id'] ?? null;
    $taskId = $data['task_id'] ?? null;
    $action = $data['action'] ?? 'start'; // start or verify
    
    if (!$userId || !$taskId) {
        jsonResponse(['success' => false, 'error' => 'User ID and Task ID required'], 400);
    }
    
    try {
        $db->beginTransaction();
        
        // Get task
        $stmt = $db->prepare("SELECT * FROM tasks WHERE id = ? AND is_active = 1");
        $stmt->execute([$taskId]);
        $task = $stmt->fetch();
        
        if (!$task) {
            $db->rollBack();
            jsonResponse(['success' => false, 'error' => 'Task not found'], 404);
        }
        
        if ($action === 'start') {
            // Mark task as started
            $stmt = $db->prepare("INSERT INTO user_tasks (user_id, task_id, status, last_reset) 
                                 VALUES (?, ?, 'pending', ?) 
                                 ON DUPLICATE KEY UPDATE status = 'pending'");
            $stmt->execute([$userId, $taskId, date('Y-m-d')]);
            
            $db->commit();
            
            jsonResponse([
                'success' => true,
                'message' => 'Task started',
                'url' => $task['url']
            ]);
            
        } elseif ($action === 'verify') {
            // Verify and complete task
            $stmt = $db->prepare("SELECT * FROM user_tasks WHERE user_id = ? AND task_id = ?");
            $stmt->execute([$userId, $taskId]);
            $userTask = $stmt->fetch();
            
            if (!$userTask) {
                $db->rollBack();
                jsonResponse(['success' => false, 'error' => 'Task not started'], 400);
            }
            
            // Check if already completed (for one-time tasks)
            if ($task['type'] === 'one_time' && $userTask['status'] === 'completed') {
                $db->rollBack();
                jsonResponse(['success' => false, 'error' => 'Task already completed'], 400);
            }
            
            // Mark as completed
            $stmt = $db->prepare("UPDATE user_tasks SET status = 'completed', completed_at = NOW() WHERE id = ?");
            $stmt->execute([$userTask['id']]);
            
            // Award coins
            updateUserCoins($userId, $task['reward'], true);
            
            // Update stats
            $stmt = $db->prepare("UPDATE user_stats SET tasks_completed = tasks_completed + 1 WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            // Add transaction
            addTransaction($userId, 'task', $task['reward'], "Task completed: {$task['title']}");
            
            // Check if this unlocks referral reward
            $stmt = $db->prepare("SELECT * FROM referrals WHERE referred_id = ? AND status = 'pending'");
            $stmt->execute([$userId]);
            $referral = $stmt->fetch();
            
            if ($referral) {
                $unlockTasks = (int)getSetting('referral_unlock_tasks', 1);
                $stmt = $db->prepare("SELECT COUNT(*) FROM user_tasks WHERE user_id = ? AND status = 'completed'");
                $stmt->execute([$userId]);
                $completedTasks = $stmt->fetchColumn();
                
                if ($completedTasks >= $unlockTasks && !$referral['reward_given']) {
                    // Approve referral and give reward
                    $referralReward = (float)getSetting('referral_reward', 100);
                    
                    $stmt = $db->prepare("UPDATE referrals SET status = 'approved', reward_given = 1, tasks_completed = ? WHERE id = ?");
                    $stmt->execute([$completedTasks, $referral['id']]);
                    
                    updateUserCoins($referral['referrer_id'], $referralReward, true);
                    addTransaction($referral['referrer_id'], 'referral', $referralReward, "Referral reward from user #{$userId}");
                    
                    // Update referrer stats
                    $stmt = $db->prepare("UPDATE user_stats SET referrals_count = referrals_count + 1 WHERE user_id = ?");
                    $stmt->execute([$referral['referrer_id']]);
                }
            }
            
            // Get updated coins
            $stmt = $db->prepare("SELECT coins FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $totalCoins = $stmt->fetchColumn();
            
            $db->commit();
            
            jsonResponse([
                'success' => true,
                'message' => 'Task completed',
                'reward' => (float)$task['reward'],
                'total_coins' => (float)$totalCoins
            ]);
        } else {
            $db->rollBack();
            jsonResponse(['success' => false, 'error' => 'Invalid action'], 400);
        }
        
    } catch (Exception $e) {
        $db->rollBack();
        error_log("Tasks Error: " . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'Failed to process task'], 500);
    }
} else {
    jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
}
?>
