<?php
require_once 'config.php';

// Telegram Bot API Functions
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
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

function sendPhoto($chat_id, $photo_url, $caption = '', $reply_markup = null) {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendPhoto";
    
    $data = [
        'chat_id' => $chat_id,
        'photo' => $photo_url,
        'caption' => $caption,
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
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

// Get webhook data
$content = file_get_contents("php://input");
$update = json_decode($content, true);

// Log for debugging
file_put_contents('webhook_log.txt', date('Y-m-d H:i:s') . " - " . $content . "\n", FILE_APPEND);

if (isset($update['message'])) {
    $chat_id = $update['message']['chat']['id'];
    $text = $update['message']['text'] ?? '';
    $user_first_name = $update['message']['from']['first_name'] ?? 'User';
    $telegram_id = $update['message']['from']['id'];
    $username = $update['message']['from']['username'] ?? '';
    
    // Handle /start command
    if ($text == '/start' || strpos($text, '/start') === 0) {
        
        // Check for short link parameter (format: /start s_CODE)
        if (preg_match('/\/start\s+s_([a-zA-Z0-9]+)/', $text, $matches)) {
            $short_code = $matches[1];
            handleShortLinkStart($chat_id, $telegram_id, $username, $user_first_name, $short_code);
            http_response_code(200);
            exit;
        }
        
        // Register user in database if not exists
        try {
            $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Check if user exists
            $stmt = $conn->prepare("SELECT id, balance FROM users WHERE telegram_id = ?");
            $stmt->execute([$telegram_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                // Insert new user with welcome bonus
                $welcome_bonus = 100; // 100 coins welcome bonus
                $stmt = $conn->prepare("INSERT INTO users (telegram_id, username, first_name, balance, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$telegram_id, $username, $user_first_name, $welcome_bonus]);
                
                $bonus_text = "\n\n🎁 <b>Welcome Bonus: {$welcome_bonus} coins!</b>";
            } else {
                $bonus_text = "\n\n💰 Your Balance: <b>{$user['balance']} coins</b>";
            }
            
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $bonus_text = "";
        }
        
        // Professional welcome message
        $welcome_message = "🎉 <b>Welcome to Tap &amp; Earn Bot, {$user_first_name}!</b>\n\n";
        $welcome_message .= "━━━━━━━━━━━━━━━━━━━\n\n";
        $welcome_message .= "💎 <b>START EARNING NOW!</b>\n\n";
        $welcome_message .= "🔹 <b>Tap to Earn</b> - Click and collect coins\n";
        $welcome_message .= "🔹 <b>Spin Wheel</b> - Win up to 500 coins\n";
        $welcome_message .= "🔹 <b>Complete Tasks</b> - Easy rewards\n";
        $welcome_message .= "🔹 <b>Play Games</b> - Earn while playing\n";
        $welcome_message .= "🔹 <b>Invite Friends</b> - Get referral bonus\n\n";
        $welcome_message .= "━━━━━━━━━━━━━━━━━━━\n";
        $welcome_message .= $bonus_text . "\n\n";
        $welcome_message .= "💵 <i>Minimum Withdrawal: 10 coins</i>\n";
        $welcome_message .= "🚀 <i>Instant Payouts Available!</i>\n\n";
        $welcome_message .= "👇 <b>Click the button below to start earning!</b>";
        
        // Professional inline keyboard with web app button
        $reply_markup = [
            'inline_keyboard' => [
                [
                    [
                        'text' => '🚀 Launch App',
                        'web_app' => ['url' => BASE_URL . '/index.html']
                    ]
                ],
                [
                    [
                        'text' => '📊 My Stats',
                        'callback_data' => 'stats'
                    ],
                    [
                        'text' => '👥 Invite Friends',
                        'callback_data' => 'invite'
                    ]
                ],
                [
                    [
                        'text' => '❓ Help',
                        'callback_data' => 'help'
                    ],
                    [
                        'text' => '📢 Channel',
                        'url' => 'https://t.me/yourchannel'
                    ]
                ]
            ]
        ];
        
        sendTelegramMessage($chat_id, $welcome_message, $reply_markup);
    }
    
    // Handle other commands
    elseif ($text == '/help') {
        $help_message = "❓ <b>HELP &amp; SUPPORT</b>\n\n";
        $help_message .= "🔸 <b>How to earn?</b>\n";
        $help_message .= "Tap the button, spin wheel, complete tasks, play games and invite friends.\n\n";
        $help_message .= "🔸 <b>How to withdraw?</b>\n";
        $help_message .= "Go to Wallet section, enter amount and payment details.\n\n";
        $help_message .= "🔸 <b>Minimum withdrawal?</b>\n";
        $help_message .= "Only 10 coins minimum!\n\n";
        $help_message .= "🔸 <b>Payment methods?</b>\n";
        $help_message .= "USDT, TON, PayPal, UPI, Bank Transfer\n\n";
        $help_message .= "📧 <b>Support:</b> @yoursupport";
        
        sendTelegramMessage($chat_id, $help_message);
    }
    
    elseif ($text == '/balance' || $text == '/stats') {
        try {
            $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $stmt = $conn->prepare("SELECT balance, created_at FROM users WHERE telegram_id = ?");
            $stmt->execute([$telegram_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                $days = floor((time() - strtotime($user['created_at'])) / 86400);
                $stats_message = "📊 <b>YOUR STATISTICS</b>\n\n";
                $stats_message .= "💰 Balance: <b>{$user['balance']} coins</b>\n";
                $stats_message .= "📅 Member Since: {$days} days\n\n";
                $stats_message .= "Keep earning! 🚀";
                
                sendTelegramMessage($chat_id, $stats_message);
            }
        } catch (PDOException $e) {
            sendTelegramMessage($chat_id, "Error fetching stats.");
        }
    }
}

// Handle callback queries (button clicks)
if (isset($update['callback_query'])) {
    $callback_id = $update['callback_query']['id'];
    $chat_id = $update['callback_query']['message']['chat']['id'];
    $data = $update['callback_query']['data'];
    $telegram_id = $update['callback_query']['from']['id'];
    
    if ($data == 'stats') {
        try {
            $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $stmt = $conn->prepare("SELECT balance, created_at FROM users WHERE telegram_id = ?");
            $stmt->execute([$telegram_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                $days = floor((time() - strtotime($user['created_at'])) / 86400);
                $stats_message = "📊 <b>YOUR STATISTICS</b>\n\n";
                $stats_message .= "💰 Balance: <b>{$user['balance']} coins</b>\n";
                $stats_message .= "📅 Member Since: {$days} days\n\n";
                $stats_message .= "Keep earning! 🚀";
                
                // Answer callback and send message
                file_get_contents("https://api.telegram.org/bot" . BOT_TOKEN . "/answerCallbackQuery?callback_query_id={$callback_id}");
                sendTelegramMessage($chat_id, $stats_message);
            }
        } catch (PDOException $e) {
            file_get_contents("https://api.telegram.org/bot" . BOT_TOKEN . "/answerCallbackQuery?callback_query_id={$callback_id}&text=Error");
        }
    }
    
    elseif ($data == 'invite') {
        $invite_link = "https://t.me/" . str_replace('@', '', BOT_USERNAME) . "?start=" . $telegram_id;
        $invite_message = "👥 <b>INVITE FRIENDS</b>\n\n";
        $invite_message .= "🎁 Earn 50 coins for each friend!\n\n";
        $invite_message .= "📎 Your Referral Link:\n";
        $invite_message .= "<code>{$invite_link}</code>\n\n";
        $invite_message .= "Share and start earning! 💰";
        
        file_get_contents("https://api.telegram.org/bot" . BOT_TOKEN . "/answerCallbackQuery?callback_query_id={$callback_id}");
        sendTelegramMessage($chat_id, $invite_message);
    }
    
    elseif ($data == 'help') {
        $help_message = "❓ <b>HELP &amp; SUPPORT</b>\n\n";
        $help_message .= "🔸 Tap the button to earn coins\n";
        $help_message .= "🔸 Spin wheel for big rewards\n";
        $help_message .= "🔸 Complete tasks daily\n";
        $help_message .= "🔸 Invite friends for bonus\n\n";
        $help_message .= "💵 Minimum withdrawal: 10 coins\n\n";
        $help_message .= "📧 Support: @yoursupport";
        
        file_get_contents("https://api.telegram.org/bot" . BOT_TOKEN . "/answerCallbackQuery?callback_query_id={$callback_id}");
        sendTelegramMessage($chat_id, $help_message);
    }
}

/**
 * Handle short link access via bot
 */
function handleShortLinkStart($chat_id, $telegram_id, $username, $user_first_name, $short_code) {
    // Register user if not exists
    try {
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Check if user exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE telegram_id = ?");
        $stmt->execute([$telegram_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (telegram_id, username, first_name, balance, created_at) VALUES (?, ?, ?, 100, NOW())");
            $stmt->execute([$telegram_id, $username, $user_first_name]);
        }
        
        // Get short link details from database
        $stmt = $conn->prepare("SELECT * FROM short_links WHERE short_code = ?");
        $stmt->execute([$short_code]);
        $link = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$link) {
            // Link not found - send regular welcome message
            $text = "❌ <b>Link Not Found</b>\n\n";
            $text .= "This short link doesn't exist or has been deleted.\n\n";
            $text .= "👇 Start earning with our bot instead!";
            
            $reply_markup = [
                'inline_keyboard' => [
                    [
                        [
                            'text' => '🚀 Launch App',
                            'web_app' => ['url' => BASE_URL . '/index.html']
                        ]
                    ]
                ]
            ];
            
            sendTelegramMessage($chat_id, $text, $reply_markup);
            return;
        }
        
        // Prepare redirect URL with user_id for tracking
        $redirectUrl = BASE_URL . '/s.php?code=' . urlencode($short_code) . '&user_id=' . $telegram_id;
        
        // Send message with web app button
        $text = "🔗 <b>Opening Short Link...</b>\n\n";
        $text .= "📺 Please watch a short ad to continue\n\n";
        $text .= "💰 <i>This helps us keep the bot free and rewarding!</i>\n\n";
        $text .= "👇 <b>Click the button below to continue:</b>";
        
        $reply_markup = [
            'inline_keyboard' => [
                [
                    [
                        'text' => '▶️ Watch Ad & Continue',
                        'web_app' => ['url' => $redirectUrl]
                    ]
                ],
                [
                    [
                        'text' => '🏠 Back to Main App',
                        'web_app' => ['url' => BASE_URL . '/index.html']
                    ]
                ]
            ]
        ];
        
        sendTelegramMessage($chat_id, $text, $reply_markup);
        
    } catch (PDOException $e) {
        error_log("Short link handler error: " . $e->getMessage());
        
        // Send error message
        $text = "❌ <b>Error</b>\n\nSomething went wrong. Please try again later.";
        $reply_markup = [
            'inline_keyboard' => [
                [
                    [
                        'text' => '🚀 Launch App',
                        'web_app' => ['url' => BASE_URL . '/index.html']
                    ]
                ]
            ]
        ];
        sendTelegramMessage($chat_id, $text, $reply_markup);
    }
}

http_response_code(200);
?>
