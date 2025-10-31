<?php
/**
 * Telegram Bot Handler
 * Handles all Telegram bot commands and callbacks
 * 
 * This file should be set as your Telegram bot webhook endpoint
 * Use: https://yourdomain.com/bot.php
 */

require_once 'config.php';

// Get incoming update from Telegram
$content = file_get_contents("php://input");
$update = json_decode($content, true);

// Log incoming updates for debugging
error_log("Bot Update Received: " . $content);

// Validate update
if (!$update) {
    error_log("Bot Error: Invalid JSON in update");
    http_response_code(200); // Always return 200 to Telegram
    exit;
}

// Extract message or callback query
$message = $update['message'] ?? null;
$callback_query = $update['callback_query'] ?? null;
$chat_id = $message['chat']['id'] ?? $callback_query['message']['chat']['id'] ?? null;
$user_id = $message['from']['id'] ?? $callback_query['from']['id'] ?? null;
$username = $message['from']['username'] ?? $callback_query['from']['username'] ?? null;
$first_name = $message['from']['first_name'] ?? $callback_query['from']['first_name'] ?? 'User';

if (!$chat_id) {
    error_log("Bot Error: No chat_id found in update");
    http_response_code(200);
    exit;
}

// Process message commands
if ($message) {
    $text = $message['text'] ?? '';
    $message_id = $message['message_id'];
    
    // Handle /start command
    if (strpos($text, '/start') === 0) {
        handleStartCommand($chat_id, $user_id, $username, $first_name, $text);
    }
    // Handle /help command
    elseif ($text === '/help') {
        handleHelpCommand($chat_id);
    }
    // Handle /balance command
    elseif ($text === '/balance') {
        handleBalanceCommand($chat_id, $user_id);
    }
    // Handle /spin command - Open web app directly
    elseif ($text === '/spin') {
        handleSpinCommand($chat_id);
    }
    // Handle /tasks command
    elseif ($text === '/tasks') {
        handleTasksCommand($chat_id);
    }
    // Handle /wallet command
    elseif ($text === '/wallet') {
        handleWalletCommand($chat_id);
    }
    // Handle /games command
    elseif ($text === '/games') {
        handleGamesCommand($chat_id);
    }
}

// Process callback queries (inline button clicks)
if ($callback_query) {
    $callback_data = $callback_query['data'];
    $callback_query_id = $callback_query['id'];
    
    handleCallbackQuery($chat_id, $user_id, $callback_data, $callback_query_id);
}

/**
 * Handle /start command
 */
function handleStartCommand($chat_id, $user_id, $username, $first_name, $text) {
    // Check for short link parameter (format: s_CODE)
    if (preg_match('/\/start\s+s_([a-zA-Z0-9]+)/', $text, $matches)) {
        $short_code = $matches[1];
        handleShortLinkAccess($chat_id, $user_id, $username, $first_name, $short_code);
        return;
    }
    
    // Check for referral code
    $referral_code = null;
    if (preg_match('/\/start\s+(.+)/', $text, $matches)) {
        $referral_code = $matches[1];
    }
    
    // Register or update user in database
    registerUser($user_id, $username, $first_name, $referral_code);
    
    // Welcome message
    $welcome_text = "🎉 <b>Welcome to Earn Bot!</b>\n\n";
    $welcome_text .= "💰 <b>Tap to Earn:</b> Tap the coin to earn rewards\n";
    $welcome_text .= "🎡 <b>Spin Wheel:</b> Spin daily for bonus coins\n";
    $welcome_text .= "✅ <b>Complete Tasks:</b> Earn more by completing tasks\n";
    $welcome_text .= "🎮 <b>Play Games:</b> Play and earn\n";
    $welcome_text .= "👥 <b>Invite Friends:</b> Get referral bonuses\n";
    $welcome_text .= "💸 <b>Withdraw:</b> Cash out your earnings\n\n";
    $welcome_text .= "👇 <b>Tap the button below to start earning!</b>";
    
    // Inline keyboard with web app button
    $keyboard = [
        'inline_keyboard' => [
            [
                ['text' => '🚀 Open Earn Bot App', 'web_app' => ['url' => BASE_URL . '/index.html']]
            ],
            [
                ['text' => '📊 My Balance', 'callback_data' => 'balance'],
                ['text' => '👥 Invite Friends', 'callback_data' => 'referral']
            ],
            [
                ['text' => '❓ Help', 'callback_data' => 'help']
            ]
        ]
    ];
    
    sendMessage($chat_id, $welcome_text, $keyboard);
}

/**
 * Handle /help command
 */
function handleHelpCommand($chat_id) {
    $help_text = "📖 <b>How to Use Earn Bot</b>\n\n";
    $help_text .= "🏠 <b>/start</b> - Start the bot and open app\n";
    $help_text .= "💰 <b>/balance</b> - Check your coin balance\n";
    $help_text .= "🎡 <b>/spin</b> - Open spin wheel\n";
    $help_text .= "✅ <b>/tasks</b> - View available tasks\n";
    $help_text .= "💸 <b>/wallet</b> - Manage withdrawals\n";
    $help_text .= "🎮 <b>/games</b> - Play and earn games\n";
    $help_text .= "❓ <b>/help</b> - Show this help message\n\n";
    $help_text .= "<b>Earning Methods:</b>\n";
    $help_text .= "• Tap the coin to earn\n";
    $help_text .= "• Complete daily tasks\n";
    $help_text .= "• Spin the wheel\n";
    $help_text .= "• Play games\n";
    $help_text .= "• Invite friends\n";
    $help_text .= "• Watch ads for bonus\n\n";
    $help_text .= "💡 <i>Minimum withdrawal: 10 coins</i>";
    
    $keyboard = [
        'inline_keyboard' => [
            [
                ['text' => '🚀 Open App', 'web_app' => ['url' => BASE_URL . '/index.html']]
            ]
        ]
    ];
    
    sendMessage($chat_id, $help_text, $keyboard);
}

/**
 * Handle /balance command
 */
function handleBalanceCommand($chat_id, $user_id) {
    $user = getUserData($user_id);
    
    if ($user) {
        $balance = number_format($user['coins'], 2);
        $usd_value = number_format($user['coins'] * 0.001, 4);
        
        $text = "💰 <b>Your Balance</b>\n\n";
        $text .= "🪙 Coins: <b>{$balance}</b>\n";
        $text .= "💵 ≈ \${$usd_value} USD\n\n";
        $text .= "🎯 Keep earning to withdraw!";
        
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '🚀 Open App', 'web_app' => ['url' => BASE_URL . '/index.html']]
                ],
                [
                    ['text' => '💸 Withdraw', 'web_app' => ['url' => BASE_URL . '/index.html#wallet']]
                ]
            ]
        ];
        
        sendMessage($chat_id, $text, $keyboard);
    } else {
        sendMessage($chat_id, "❌ User not found. Please use /start first.");
    }
}

/**
 * Handle /spin command - FIXED: No more "coming soon" message
 */
function handleSpinCommand($chat_id) {
    $text = "🎡 <b>Spin the Wheel!</b>\n\n";
    $text .= "Spin daily to win:\n";
    $text .= "• 10-1000 coins\n";
    $text .= "• 💎 JACKPOT prize\n";
    $text .= "• Watch ads to double your reward!\n\n";
    $text .= "👇 Open the app to spin now!";
    
    $keyboard = [
        'inline_keyboard' => [
            [
                ['text' => '🎡 Spin Now', 'web_app' => ['url' => BASE_URL . '/index.html#spin']]
            ]
        ]
    ];
    
    sendMessage($chat_id, $text, $keyboard);
}

/**
 * Handle /tasks command
 */
function handleTasksCommand($chat_id) {
    $text = "✅ <b>Complete Tasks & Earn!</b>\n\n";
    $text .= "Available task types:\n";
    $text .= "• Daily tasks (reset every 24h)\n";
    $text .= "• One-time tasks\n";
    $text .= "• Social media tasks\n";
    $text .= "• Ad watching tasks\n\n";
    $text .= "💡 Complete tasks to earn bonus coins!";
    
    $keyboard = [
        'inline_keyboard' => [
            [
                ['text' => '✅ View Tasks', 'web_app' => ['url' => BASE_URL . '/index.html#tasks']]
            ]
        ]
    ];
    
    sendMessage($chat_id, $text, $keyboard);
}

/**
 * Handle /wallet command
 */
function handleWalletCommand($chat_id) {
    $text = "💸 <b>Wallet & Withdrawals</b>\n\n";
    $text .= "Supported payment methods:\n";
    $text .= "• PayPal\n";
    $text .= "• Bank Transfer\n";
    $text .= "• UPI (India)\n";
    $text .= "• Cryptocurrency (USDT, BTC, ETH, BNB, etc.)\n\n";
    $text .= "📋 Minimum withdrawal: <b>10 coins</b>\n";
    $text .= "⚡ Processing time: 24-48 hours";
    
    $keyboard = [
        'inline_keyboard' => [
            [
                ['text' => '💸 Manage Wallet', 'web_app' => ['url' => BASE_URL . '/index.html#wallet']]
            ]
        ]
    ];
    
    sendMessage($chat_id, $text, $keyboard);
}

/**
 * Handle /games command
 */
function handleGamesCommand($chat_id) {
    $text = "🎮 <b>Play & Earn Games</b>\n\n";
    $text .= "Play games to earn bonus coins!\n";
    $text .= "New games added regularly.\n\n";
    $text .= "🎯 Have fun and earn at the same time!";
    
    $keyboard = [
        'inline_keyboard' => [
            [
                ['text' => '🎮 Play Games', 'web_app' => ['url' => BASE_URL . '/index.html#games']]
            ]
        ]
    ];
    
    sendMessage($chat_id, $text, $keyboard);
}

/**
 * Handle callback queries (inline button clicks)
 */
function handleCallbackQuery($chat_id, $user_id, $callback_data, $callback_query_id) {
    switch ($callback_data) {
        case 'balance':
            answerCallbackQuery($callback_query_id, 'Loading balance...');
            handleBalanceCommand($chat_id, $user_id);
            break;
            
        case 'referral':
            answerCallbackQuery($callback_query_id, 'Loading referral info...');
            handleReferralCallback($chat_id, $user_id);
            break;
            
        case 'help':
            answerCallbackQuery($callback_query_id);
            handleHelpCommand($chat_id);
            break;
            
        case 'spin':
            // FIXED: No more "coming soon" message - direct to app
            answerCallbackQuery($callback_query_id, 'Opening spin wheel...');
            handleSpinCommand($chat_id);
            break;
            
        default:
            answerCallbackQuery($callback_query_id);
    }
}

/**
 * Handle short link access via bot
 */
function handleShortLinkAccess($chat_id, $user_id, $username, $first_name, $short_code) {
    // Register user if not exists
    registerUser($user_id, $username, $first_name, null);
    
    // Get short link details from database
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM short_links WHERE short_code = ?");
    $stmt->execute([$short_code]);
    $link = $stmt->fetch();
    
    if (!$link) {
        // Link not found
        $text = "❌ <b>Link Not Found</b>\n\n";
        $text .= "This short link doesn't exist or has been deleted.\n\n";
        $text .= "👇 Start earning with our bot instead!";
        
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '🚀 Open Earn Bot App', 'web_app' => ['url' => BASE_URL . '/index.html']]
                ]
            ]
        ];
        
        sendMessage($chat_id, $text, $keyboard);
        return;
    }
    
    // Prepare redirect URL with user_id for tracking
    // IMPORTANT: Add the code as a query parameter so s.php can process it properly
    $redirectUrl = BASE_URL . '/s.php?code=' . urlencode($short_code) . '&user_id=' . $user_id;
    
    // Send message with web app button pointing to the shortener page
    $text = "🔗 <b>Opening Short Link...</b>\n\n";
    $text .= "📺 Please watch a short ad to continue\n\n";
    $text .= "💰 <i>This helps us keep the bot free and rewarding!</i>\n\n";
    $text .= "👇 <b>Click the button below to continue:</b>";
    
    $keyboard = [
        'inline_keyboard' => [
            [
                ['text' => '▶️ Watch Ad & Continue', 'web_app' => ['url' => $redirectUrl]]
            ],
            [
                ['text' => '🏠 Back to Main App', 'web_app' => ['url' => BASE_URL . '/index.html']]
            ]
        ]
    ];
    
    sendMessage($chat_id, $text, $keyboard);
}

/**
 * Handle referral callback
 */
function handleReferralCallback($chat_id, $user_id) {
    $user = getUserData($user_id);
    
    if ($user) {
        $botUsername = str_replace('@', '', BOT_USERNAME);
        $referral_link = "https://t.me/{$botUsername}?start=" . $user['referral_code'];
        $shareMessage = "🎁 Join CoinTap Pro & Start Earning!\n\n";
        
        $text = "👥 <b>Invite Friends & Earn!</b>\n\n";
        $text .= "🎁 Your referral link:\n";
        $text .= "<code>{$referral_link}</code>\n\n";
        $text .= "📊 Your stats:\n";
        $text .= "• Total referrals: <b>{$user['total_referrals']}</b>\n";
        $text .= "• Total earned: <b>{$user['referral_earnings']} coins</b>\n\n";
        $text .= "💡 Earn 10% of your friends' earnings!";
        
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '📤 Share Link', 'url' => "https://t.me/share/url?url=" . urlencode($referral_link) . "&text=" . urlencode($shareMessage)]
                ],
                [
                    ['text' => '🚀 Open App', 'web_app' => ['url' => BASE_URL . '/index.html#referrals']]
                ]
            ]
        ];
        
        sendMessage($chat_id, $text, $keyboard);
    }
}

/**
 * Register or update user in database
 */
function registerUser($user_id, $username, $first_name, $referral_code = null) {
    $db = Database::getInstance()->getConnection();
    
    // Check if user exists
    $stmt = $db->prepare("SELECT id FROM users WHERE telegram_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // Generate unique referral code
        $user_referral_code = generateReferralCode();
        
        // Insert new user
        $stmt = $db->prepare("INSERT INTO users (telegram_id, username, first_name, referral_code, referred_by) 
                              VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $username, $first_name, $user_referral_code, $referral_code]);
        
        // If referred by someone, update referrer's stats
        if ($referral_code) {
            $stmt = $db->prepare("UPDATE users SET total_referrals = total_referrals + 1 
                                  WHERE referral_code = ?");
            $stmt->execute([$referral_code]);
        }
    } else {
        // Update existing user
        $stmt = $db->prepare("UPDATE users SET username = ?, first_name = ?, last_active = NOW() 
                              WHERE telegram_id = ?");
        $stmt->execute([$username, $first_name, $user_id]);
    }
}

/**
 * Get user data from database
 */
function getUserData($user_id) {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("SELECT * FROM users WHERE telegram_id = ?");
    $stmt->execute([$user_id]);
    
    return $stmt->fetch();
}

/**
 * Send message to user
 */
function sendMessage($chat_id, $text, $keyboard = null) {
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML'
    ];
    
    if ($keyboard) {
        $data['reply_markup'] = json_encode($keyboard);
    }
    
    error_log("Sending message to chat_id: $chat_id");
    $result = sendTelegramRequest('sendMessage', $data);
    
    if (!$result || !$result['ok']) {
        error_log("Failed to send message: " . json_encode($result));
    } else {
        error_log("Message sent successfully");
    }
    
    return $result;
}

/**
 * Answer callback query
 */
function answerCallbackQuery($callback_query_id, $text = '', $show_alert = false) {
    $data = [
        'callback_query_id' => $callback_query_id,
        'text' => $text,
        'show_alert' => $show_alert
    ];
    
    return sendTelegramRequest('answerCallbackQuery', $data);
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
            'content' => http_build_query($data),
            'ignore_errors' => true
        ]
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    
    if ($result === false) {
        error_log("Telegram API Error: Failed to connect to $url");
        return ['ok' => false, 'description' => 'Failed to connect to Telegram API'];
    }
    
    $response = json_decode($result, true);
    
    if (!$response) {
        error_log("Telegram API Error: Invalid JSON response: $result");
        return ['ok' => false, 'description' => 'Invalid response from Telegram API'];
    }
    
    return $response;
}

?>
