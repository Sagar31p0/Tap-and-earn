<?php
require_once 'config.php';

// Telegram Bot API
function sendTelegramMessage($chat_id, $text, $reply_markup = null) {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage";
    
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML'
    ];
    
    if ($reply_markup) {
        $data['reply_markup'] = json_encode($reply_markup);
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

// Get webhook data
$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (isset($update['message'])) {
    $chat_id = $update['message']['chat']['id'];
    $text = $update['message']['text'] ?? '';
    $user_first_name = $update['message']['from']['first_name'] ?? 'User';
    
    // Handle /start command
    if ($text == '/start') {
        $welcome_message = "🎉 <b>Welcome to Tap & Earn Bot, {$user_first_name}!</b>\n\n";
        $welcome_message .= "💰 Start earning coins by:\n";
        $welcome_message .= "• 👆 Tapping to earn\n";
        $welcome_message .= "• 🎡 Spinning the wheel\n";
        $welcome_message .= "• ✅ Completing tasks\n";
        $welcome_message .= "• 🎮 Playing games\n";
        $welcome_message .= "• 👥 Inviting friends\n\n";
        $welcome_message .= "💵 Withdraw your earnings anytime!\n\n";
        $welcome_message .= "Click the Menu button below to start! 👇";
        
        // Add inline keyboard with web app button
        $reply_markup = [
            'inline_keyboard' => [
                [
                    ['text' => '🚀 Open App', 'web_app' => ['url' => 'https://reqa.antipiracyforce.org/test/']]
                ]
            ]
        ];
        
        sendTelegramMessage($chat_id, $welcome_message, $reply_markup);
        
        // Register user in database if not exists
        try {
            $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $telegram_id = $update['message']['from']['id'];
            $username = $update['message']['from']['username'] ?? '';
            
            // Check if user exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE telegram_id = ?");
            $stmt->execute([$telegram_id]);
            
            if ($stmt->rowCount() == 0) {
                // Insert new user
                $stmt = $conn->prepare("INSERT INTO users (telegram_id, username, first_name, balance, created_at) VALUES (?, ?, ?, 0, NOW())");
                $stmt->execute([$telegram_id, $username, $user_first_name]);
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
        }
    }
}
?>
