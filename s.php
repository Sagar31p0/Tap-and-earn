<?php
require_once 'config.php';

// Set proper headers to prevent caching issues
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Error handling wrapper
try {
    // Get short code from URL
    $shortCode = $_GET['code'] ?? '';

    // If no code in URL, try to get from direct web app (will be handled by JavaScript)
    if (empty($shortCode)) {
    // Show a loader page that will handle Telegram start_param
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Loading...</title>
        <script src="https://telegram.org/js/telegram-web-app.js"></script>
        <style>
            body {
                margin: 0;
                padding: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            }
            .loader {
                text-align: center;
                color: white;
            }
            .spinner {
                width: 50px;
                height: 50px;
                border: 5px solid rgba(255,255,255,0.3);
                border-top-color: white;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin: 0 auto 20px;
            }
            @keyframes spin {
                to { transform: rotate(360deg); }
            }
        </style>
    </head>
    <body>
        <div class="loader">
            <div class="spinner"></div>
            <p>Loading short link...</p>
        </div>
        
        <script>
            // Check if opened via direct web app link
            if (window.Telegram && window.Telegram.WebApp) {
                const tg = window.Telegram.WebApp;
                tg.ready();
                tg.expand();
                
                const startParam = tg.initDataUnsafe.start_param;
                
                if (startParam && startParam.startsWith('s_')) {
                    // Extract code from start_param (format: s_CODE)
                    const code = startParam.substring(2);
                    
                    // Get user ID if available
                    let userId = '';
                    if (tg.initDataUnsafe.user && tg.initDataUnsafe.user.id) {
                        userId = tg.initDataUnsafe.user.id;
                    }
                    
                    // Redirect with proper parameters
                    const redirectUrl = window.location.origin + '/s.php?code=' + encodeURIComponent(code) + 
                                      (userId ? '&user_id=' + userId : '');
                    window.location.href = redirectUrl;
                } else {
                    // No valid short code found
                    setTimeout(() => {
                        window.location.href = window.location.origin + '/index.html';
                    }, 1000);
                }
            } else {
                // Not opened in Telegram, redirect to home
                setTimeout(() => {
                    window.location.href = window.location.origin + '/index.html';
                }, 1000);
            }
        </script>
    </body>
    </html>
    <?php
    exit;
}

// Validate database connection
$db = Database::getInstance()->getConnection();
if (!$db) {
    error_log("s.php: Database connection failed for code: " . $shortCode);
    http_response_code(500);
    echo "<!DOCTYPE html><html><body><h1>Service Unavailable</h1><p>Please try again later.</p></body></html>";
    exit;
}

// Get short link details
try {
    $stmt = $db->prepare("SELECT * FROM short_links WHERE short_code = ?");
    $stmt->execute([$shortCode]);
    $link = $stmt->fetch();
} catch (Exception $e) {
    error_log("s.php: Database query error: " . $e->getMessage() . " for code: " . $shortCode);
    http_response_code(500);
    echo "<!DOCTYPE html><html><body><h1>Error</h1><p>Unable to process request.</p></body></html>";
    exit;
}

if (!$link) {
    error_log("s.php: Short code not found: " . $shortCode);
    header('Location: /index.html');
    exit;
}

// Increment click counter
try {
    $stmt = $db->prepare("UPDATE short_links SET clicks = clicks + 1 WHERE id = ?");
    $stmt->execute([$link['id']]);
} catch (Exception $e) {
    error_log("s.php: Failed to increment click counter: " . $e->getMessage());
}

// Get user ID from session or Telegram WebApp data if available
$userId = $_GET['user_id'] ?? null;

// Log the click if user is identified
if ($userId) {
    try {
        logAdEvent($userId, 'shortlink', $link['ad_unit_id'], 'click');
    } catch (Exception $e) {
        error_log("s.php: Failed to log ad event: " . $e->getMessage());
    }
}

// Redirect based on mode
if ($link['mode'] === 'task_video') {
    // For task videos, show the video page with ad
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Loading Video...</title>
        <script src="https://telegram.org/js/telegram-web-app.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        
        <script>
            // Check if opened via direct web app link (startapp parameter)
            if (window.Telegram && window.Telegram.WebApp) {
                const tg = window.Telegram.WebApp;
                const startParam = tg.initDataUnsafe.start_param;
                
                if (startParam && startParam.startsWith('s_')) {
                    // Extract code from start_param (format: s_CODE)
                    const code = startParam.substring(2);
                    // Redirect to proper URL with code
                    const currentUrl = new URL(window.location.href);
                    if (!currentUrl.searchParams.has('code')) {
                        currentUrl.searchParams.set('code', code);
                        // Get user ID if available
                        if (tg.initDataUnsafe.user && tg.initDataUnsafe.user.id) {
                            currentUrl.searchParams.set('user_id', tg.initDataUnsafe.user.id);
                        }
                        window.location.href = currentUrl.toString();
                    }
                }
            }
        </script>
        <style>
            body {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .video-container {
                background: white;
                border-radius: 20px;
                padding: 30px;
                max-width: 800px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            }
            .ad-container {
                min-height: 250px;
                background: #f8f9fa;
                border-radius: 10px;
                margin-bottom: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .countdown {
                font-size: 48px;
                font-weight: bold;
                color: #667eea;
            }
            .btn-continue {
                display: none;
            }
        </style>
    </head>
    <body>
        <div class="video-container">
            <h3 class="text-center mb-4">Please watch the ad to continue</h3>
            
            <div class="ad-container" id="adContainer">
                <div class="text-center">
                    <div class="countdown" id="countdown">5</div>
                    <p class="text-muted">Please wait...</p>
                </div>
            </div>
            
            <div class="text-center">
                <button class="btn btn-primary btn-lg btn-continue" id="continueBtn" onclick="redirectToVideo()">
                    Continue to Video <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>
        
        <script>
            // Initialize Telegram WebApp
            if (window.Telegram && window.Telegram.WebApp) {
                const tg = window.Telegram.WebApp;
                tg.ready();
                tg.expand(); // Expand to full height
            }
            
            let countdown = 5;
            const originalUrl = <?php echo json_encode($link['original_url']); ?>;
            const linkId = <?php echo $link['id']; ?>;
            const userId = <?php echo json_encode($userId); ?>;
            
            const timer = setInterval(() => {
                countdown--;
                document.getElementById('countdown').textContent = countdown;
                
                if (countdown <= 0) {
                    clearInterval(timer);
                    document.getElementById('adContainer').innerHTML = '<p class="text-success"><i class="fas fa-check-circle fa-3x"></i><br>Ad completed!</p>';
                    document.getElementById('continueBtn').style.display = 'inline-block';
                    
                    // Record conversion
                    if (userId) {
                        fetch('<?php echo BASE_URL; ?>/api/track.php?action=conversion&link_id=' + linkId + '&user_id=' + userId);
                    }
                }
            }, 1000);
            
            function redirectToVideo() {
                window.location.href = originalUrl;
            }
        </script>
    </body>
    </html>
    <?php
} else {
    // Direct ad - show video player style interface with ad
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <title>Loading Content...</title>
        <script src="https://telegram.org/js/telegram-web-app.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        
        <script>
            // Check if opened via direct web app link (startapp parameter)
            if (window.Telegram && window.Telegram.WebApp) {
                const tg = window.Telegram.WebApp;
                const startParam = tg.initDataUnsafe.start_param;
                
                if (startParam && startParam.startsWith('s_')) {
                    // Extract code from start_param (format: s_CODE)
                    const code = startParam.substring(2);
                    // Redirect to proper URL with code
                    const currentUrl = new URL(window.location.href);
                    if (!currentUrl.searchParams.has('code')) {
                        currentUrl.searchParams.set('code', code);
                        // Get user ID if available
                        if (tg.initDataUnsafe.user && tg.initDataUnsafe.user.id) {
                            currentUrl.searchParams.set('user_id', tg.initDataUnsafe.user.id);
                        }
                        window.location.href = currentUrl.toString();
                    }
                }
            }
        </script>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                background: #0f0f1e;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            }
            
            .video-player-container {
                background: #1a1a2e;
                border-radius: 20px;
                max-width: 800px;
                width: 100%;
                overflow: hidden;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            }
            
            .video-player {
                position: relative;
                width: 100%;
                aspect-ratio: 16/9;
                background: #000;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
            }
            
            .video-overlay {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(135deg, rgba(102, 126, 234, 0.3) 0%, rgba(118, 75, 162, 0.3) 100%);
                backdrop-filter: blur(5px);
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                z-index: 1;
            }
            
            .loading-spinner {
                width: 80px;
                height: 80px;
                border: 6px solid rgba(255, 255, 255, 0.2);
                border-top: 6px solid #fff;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin-bottom: 20px;
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            .loading-text {
                color: #fff;
                font-size: 24px;
                font-weight: bold;
                margin-bottom: 10px;
                text-align: center;
            }
            
            .loading-subtext {
                color: rgba(255, 255, 255, 0.8);
                font-size: 16px;
                text-align: center;
            }
            
            .ad-ready-icon {
                font-size: 80px;
                color: #10b981;
                margin-bottom: 20px;
                display: none;
                animation: scaleIn 0.5s ease;
            }
            
            @keyframes scaleIn {
                from { transform: scale(0); }
                to { transform: scale(1); }
            }
            
            .player-controls {
                background: #1a1a2e;
                padding: 20px 30px;
                display: flex;
                flex-direction: column;
                gap: 15px;
            }
            
            .content-info {
                display: flex;
                align-items: center;
                gap: 15px;
                padding-bottom: 15px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }
            
            .content-icon {
                width: 60px;
                height: 60px;
                background: linear-gradient(135deg, #667eea, #764ba2);
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 28px;
                color: white;
            }
            
            .content-details {
                flex: 1;
            }
            
            .content-title {
                color: #fff;
                font-size: 18px;
                font-weight: 600;
                margin-bottom: 5px;
            }
            
            .content-meta {
                color: rgba(255, 255, 255, 0.6);
                font-size: 14px;
            }
            
            .watch-btn {
                width: 100%;
                padding: 18px;
                background: linear-gradient(135deg, #667eea, #764ba2);
                color: white;
                border: none;
                border-radius: 15px;
                font-size: 18px;
                font-weight: 600;
                cursor: pointer;
                display: none;
                align-items: center;
                justify-content: center;
                gap: 10px;
                transition: all 0.3s;
                box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
            }
            
            .watch-btn:active {
                transform: scale(0.98);
            }
            
            .watch-btn i {
                font-size: 24px;
            }
            
            .ad-notice {
                background: rgba(255, 193, 7, 0.1);
                border: 1px solid rgba(255, 193, 7, 0.3);
                border-radius: 10px;
                padding: 12px;
                color: #ffc107;
                font-size: 14px;
                text-align: center;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
            }
            
            .ad-loading-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.95);
                z-index: 9999;
                align-items: center;
                justify-content: center;
            }
            
            .ad-loading-overlay.show {
                display: flex;
            }
            
            .ad-loading-content {
                background: white;
                border-radius: 20px;
                padding: 40px;
                text-align: center;
                max-width: 400px;
                width: 90%;
            }
            
            .ad-loading-spinner {
                width: 60px;
                height: 60px;
                border: 6px solid #f3f3f3;
                border-top: 6px solid #667eea;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin: 0 auto 20px;
            }
            
            .ad-loading-text {
                font-size: 24px;
                font-weight: bold;
                margin-bottom: 10px;
            }
            
            .ad-loading-subtext {
                color: #666;
                margin-bottom: 10px;
            }
            
            .ad-error-content {
                background: #fff3cd;
            }
            
            .ad-error-icon {
                font-size: 48px;
                margin-bottom: 15px;
            }
            
            .ad-error-text {
                font-size: 20px;
                font-weight: bold;
                color: #856404;
                margin-bottom: 10px;
            }
            
            .ad-retry-btn {
                background: #667eea;
                color: white;
                border: none;
                padding: 12px 30px;
                border-radius: 10px;
                font-size: 16px;
                cursor: pointer;
                margin-top: 15px;
            }
            
            @media (max-width: 768px) {
                .video-player-container {
                    border-radius: 15px;
                }
                
                .loading-text {
                    font-size: 20px;
                }
                
                .loading-subtext {
                    font-size: 14px;
                }
                
                .player-controls {
                    padding: 15px 20px;
                }
                
                .content-title {
                    font-size: 16px;
                }
            }
        </style>
        
        <!-- Load Ad SDKs -->
        <script src="https://cdn.jsdelivr.net/gh/Bxstvn/AdexiumMiniAppAdsSDK@latest/AdexiumWidgets.js"></script>
        <script src="https://tb.tgadsnetwork.com/sdk.js"></script>
        <script src="https://sad.adsgram.ai/js/sad.min.js"></script>
        <script async src="https://cdn.adexium.io/tags/10113890/inpage.min.js"></script>
    </head>
    <body>
        <!-- Video Player Style Container -->
        <div class="video-player-container">
            <!-- Video Player Area -->
            <div class="video-player">
                <div class="video-overlay">
                    <div class="loading-spinner" id="loadingSpinner"></div>
                    <i class="fas fa-check-circle ad-ready-icon" id="adReadyIcon"></i>
                    <div class="loading-text" id="loadingText">Loading Ad...</div>
                    <div class="loading-subtext" id="loadingSubtext">Please wait while we prepare your content</div>
                </div>
            </div>
            
            <!-- Player Controls -->
            <div class="player-controls">
                <div class="content-info">
                    <div class="content-icon">
                        <i class="fas fa-link"></i>
                    </div>
                    <div class="content-details">
                        <div class="content-title">Short Link Redirect</div>
                        <div class="content-meta">Watch a short ad to continue</div>
                    </div>
                </div>
                
                <div class="ad-notice">
                    <i class="fas fa-info-circle"></i>
                    <span>Advertisement required to access content</span>
                </div>
                
                <button class="watch-btn" id="watchBtn" onclick="redirectToDestination()">
                    <i class="fas fa-play-circle"></i>
                    <span>Watch Now & Continue</span>
                </button>
            </div>
        </div>
        
        <script>
            // Initialize Telegram WebApp
            if (window.Telegram && window.Telegram.WebApp) {
                const tg = window.Telegram.WebApp;
                tg.ready();
                tg.expand();
            }
            
            const originalUrl = <?php echo json_encode($link['original_url']); ?>;
            const linkId = <?php echo $link['id']; ?>;
            const userId = <?php echo json_encode($userId); ?>;
            const API_URL = '<?php echo BASE_URL; ?>/api';
            const BASE_URL = '<?php echo BASE_URL; ?>';
            
            // User data for ad system
            const userData = {
                id: userId || 0
            };
            
            let adReady = false;
            let adShown = false;
            
            // UI Elements
            const loadingSpinner = document.getElementById('loadingSpinner');
            const adReadyIcon = document.getElementById('adReadyIcon');
            const loadingText = document.getElementById('loadingText');
            const loadingSubtext = document.getElementById('loadingSubtext');
            const watchBtn = document.getElementById('watchBtn');
            
            // Update UI to show ad is ready
            function showAdReady() {
                loadingSpinner.style.display = 'none';
                adReadyIcon.style.display = 'block';
                loadingText.textContent = 'Ad Ready!';
                loadingSubtext.textContent = 'Click the button below to watch and continue';
                watchBtn.style.display = 'flex';
                adReady = true;
            }
            
            // Update UI to show error
            function showError(message) {
                loadingSpinner.style.display = 'none';
                adReadyIcon.style.display = 'none';
                loadingText.textContent = 'Error';
                loadingText.style.color = '#ef4444';
                loadingSubtext.textContent = message || 'Failed to load ad';
                
                // Show watch button anyway to let user try
                setTimeout(() => {
                    watchBtn.style.display = 'flex';
                    watchBtn.querySelector('span').textContent = 'Try to Continue';
                }, 2000);
            }
            
            // Main redirect function
            async function redirectToDestination() {
                if (adShown) return;
                adShown = true;
                
                // Disable button
                watchBtn.disabled = true;
                watchBtn.style.opacity = '0.7';
                watchBtn.querySelector('span').textContent = 'Loading Ad...';
                
                try {
                    if (typeof AdManager === 'undefined') {
                        throw new Error('Ad system not loaded');
                    }
                    
                    // Show ad with redirect callback
                    await AdManager.show('shortlink', async () => {
                        // Ad completed successfully
                        loadingText.textContent = 'Success!';
                        loadingSubtext.textContent = 'Redirecting to your destination...';
                        watchBtn.querySelector('span').textContent = 'Redirecting...';
                        
                        // Record conversion
                        if (userId) {
                            try {
                                await fetch(`${BASE_URL}/api/track.php?action=conversion&link_id=${linkId}&user_id=${userId}`);
                            } catch (e) {
                                console.error('Conversion tracking error:', e);
                            }
                        }
                        
                        // Redirect after short delay
                        setTimeout(() => {
                            window.location.href = originalUrl;
                        }, 1000);
                    });
                    
                } catch (error) {
                    console.error('Ad display error:', error);
                    loadingText.textContent = 'Ad Error';
                    loadingSubtext.textContent = 'Redirecting to destination...';
                    
                    // Redirect anyway after 2 seconds
                    setTimeout(() => {
                        window.location.href = originalUrl;
                    }, 2000);
                }
            }
            
            // Main initialization
            async function initialize() {
                try {
                    loadingText.textContent = 'Loading Ad...';
                    loadingSubtext.textContent = 'Initializing ad system...';
                    
                    // Wait for AdManager to be available
                    let retries = 0;
                    const maxRetries = 50;
                    
                    while (typeof AdManager === 'undefined' && retries < maxRetries) {
                        await new Promise(resolve => setTimeout(resolve, 100));
                        retries++;
                    }
                    
                    if (typeof AdManager === 'undefined') {
                        showError('Ad system failed to load');
                        return;
                    }
                    
                    loadingSubtext.textContent = 'Preparing advertisement...';
                    
                    // Initialize AdManager
                    await AdManager.init();
                    
                    // Check if ad config exists
                    loadingSubtext.textContent = 'Checking ad availability...';
                    const adConfig = await AdManager.getAdConfig('shortlink');
                    
                    if (!adConfig || !adConfig.success) {
                        showError('No ad configuration found. Please setup ads in admin panel.');
                        return;
                    }
                    
                    // Ad is ready
                    console.log('Ad ready:', adConfig);
                    showAdReady();
                    
                } catch (error) {
                    console.error('Initialization error:', error);
                    showError(error.message || 'Failed to initialize');
                }
            }
            
            // Load ads.js dynamically
            const adsScript = document.createElement('script');
            adsScript.src = '<?php echo BASE_URL; ?>/js/ads.js';
            adsScript.onload = () => {
                console.log('Ads.js loaded successfully');
                initialize();
            };
            adsScript.onerror = () => {
                console.error('Failed to load ads.js');
                showError('Failed to load ad system');
            };
            document.head.appendChild(adsScript);
            
        </script>
    </body>
    </html>
    <?php
}

} catch (Exception $e) {
    // Catch any unhandled exceptions
    error_log("s.php: Unhandled exception: " . $e->getMessage());
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                margin: 0;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }
            .error-box {
                background: white;
                padding: 40px;
                border-radius: 10px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.2);
                text-align: center;
                max-width: 400px;
            }
            h1 {
                color: #e74c3c;
                margin-bottom: 20px;
            }
            p {
                color: #555;
                margin-bottom: 20px;
            }
            a {
                display: inline-block;
                padding: 10px 20px;
                background: #667eea;
                color: white;
                text-decoration: none;
                border-radius: 5px;
            }
        </style>
    </head>
    <body>
        <div class="error-box">
            <h1>?? Oops!</h1>
            <p>Something went wrong while processing your request.</p>
            <p>Please try again or contact support if the problem persists.</p>
            <a href="/index.html">Go to Home</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>
