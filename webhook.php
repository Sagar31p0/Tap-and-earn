<?php
/**
 * Telegram Bot Webhook Setup
 * 
 * This file helps you set up and manage the Telegram bot webhook
 * 
 * Usage:
 * 1. Set webhook: https://yourdomain.com/webhook.php?action=set
 * 2. Get webhook info: https://yourdomain.com/webhook.php?action=info
 * 3. Delete webhook: https://yourdomain.com/webhook.php?action=delete
 */

require_once 'config.php';

$action = $_GET['action'] ?? 'info';

// Set webhook URL
$webhook_url = BASE_URL . '/bot.php';

switch ($action) {
    case 'set':
        setWebhook($webhook_url);
        break;
        
    case 'delete':
        deleteWebhook();
        break;
        
    case 'info':
        getWebhookInfo();
        break;
        
    default:
        showMenu();
}

/**
 * Set webhook
 */
function setWebhook($url) {
    $result = sendTelegramRequest('setWebhook', [
        'url' => $url,
        'allowed_updates' => json_encode(['message', 'callback_query'])
    ]);
    
    if ($result['ok']) {
        echo json_encode([
            'success' => true,
            'message' => 'Webhook set successfully!',
            'webhook_url' => $url,
            'result' => $result
        ], JSON_PRETTY_PRINT);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to set webhook',
            'error' => $result['description'] ?? 'Unknown error'
        ], JSON_PRETTY_PRINT);
    }
}

/**
 * Delete webhook
 */
function deleteWebhook() {
    $result = sendTelegramRequest('deleteWebhook', []);
    
    if ($result['ok']) {
        echo json_encode([
            'success' => true,
            'message' => 'Webhook deleted successfully!',
            'result' => $result
        ], JSON_PRETTY_PRINT);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete webhook',
            'error' => $result['description'] ?? 'Unknown error'
        ], JSON_PRETTY_PRINT);
    }
}

/**
 * Get webhook info
 */
function getWebhookInfo() {
    $result = sendTelegramRequest('getWebhookInfo', []);
    
    if ($result['ok']) {
        echo "<h1>Telegram Bot Webhook Status</h1>";
        echo "<pre>" . json_encode($result['result'], JSON_PRETTY_PRINT) . "</pre>";
        
        if ($result['result']['url']) {
            echo "<p style='color: green;'><b>✅ Webhook is set!</b></p>";
            echo "<p>URL: <code>" . htmlspecialchars($result['result']['url']) . "</code></p>";
            
            if (isset($result['result']['pending_update_count'])) {
                echo "<p>Pending updates: " . $result['result']['pending_update_count'] . "</p>";
            }
            
            if (isset($result['result']['last_error_message'])) {
                echo "<p style='color: red;'><b>Last error:</b> " . htmlspecialchars($result['result']['last_error_message']) . "</p>";
                echo "<p>Last error date: " . date('Y-m-d H:i:s', $result['result']['last_error_date']) . "</p>";
            }
        } else {
            echo "<p style='color: orange;'><b>⚠️ No webhook set</b></p>";
            echo "<p><a href='?action=set'>Click here to set webhook</a></p>";
        }
        
        echo "<hr>";
        echo "<h3>Actions:</h3>";
        echo "<ul>";
        echo "<li><a href='?action=set'>Set Webhook</a></li>";
        echo "<li><a href='?action=delete'>Delete Webhook</a></li>";
        echo "<li><a href='?action=info'>Refresh Info</a></li>";
        echo "</ul>";
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to get webhook info',
            'error' => $result['description'] ?? 'Unknown error'
        ], JSON_PRETTY_PRINT);
    }
}

/**
 * Show menu
 */
function showMenu() {
    echo "<h1>Telegram Bot Webhook Manager</h1>";
    echo "<h3>Available Actions:</h3>";
    echo "<ul>";
    echo "<li><a href='?action=info'>Get Webhook Info</a></li>";
    echo "<li><a href='?action=set'>Set Webhook</a></li>";
    echo "<li><a href='?action=delete'>Delete Webhook</a></li>";
    echo "</ul>";
    echo "<hr>";
    echo "<h3>Configuration:</h3>";
    echo "<ul>";
    echo "<li>Bot Token: " . substr(BOT_TOKEN, 0, 10) . "..." . substr(BOT_TOKEN, -5) . "</li>";
    echo "<li>Bot Username: " . BOT_USERNAME . "</li>";
    echo "<li>Webhook URL: " . BASE_URL . "/bot.php</li>";
    echo "</ul>";
}

/**
 * Send request to Telegram API
 */
function sendTelegramRequest($method, $data) {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/" . $method;
    
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    return json_decode($result, true);
}

?>
