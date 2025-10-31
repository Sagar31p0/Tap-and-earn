<?php
require_once 'config.php';

// Get short code from URL
$shortCode = $_GET['code'] ?? '';

if (empty($shortCode)) {
    header('Location: index.html');
    exit;
}

$db = Database::getInstance()->getConnection();

// Get short link details
$stmt = $db->prepare("SELECT * FROM short_links WHERE short_code = ?");
$stmt->execute([$shortCode]);
$link = $stmt->fetch();

if (!$link) {
    header('Location: index.html');
    exit;
}

// Increment click counter
$stmt = $db->prepare("UPDATE short_links SET clicks = clicks + 1 WHERE id = ?");
$stmt->execute([$link['id']]);

// Get user ID from session or Telegram WebApp data if available
$userId = $_GET['user_id'] ?? null;

// Log the click if user is identified
if ($userId) {
    logAdEvent($userId, 'shortlink', $link['ad_unit_id'], 'click');
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
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
    // Direct ad - show interstitial then redirect
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <title>Redirecting...</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0;
                padding: 20px;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            }
            .redirect-container {
                background: white;
                border-radius: 20px;
                padding: 40px;
                max-width: 600px;
                width: 100%;
                text-align: center;
                box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            }
            .spinner {
                width: 60px;
                height: 60px;
                border: 5px solid #f3f3f3;
                border-top: 5px solid #667eea;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin: 20px auto;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            .ad-message {
                background: #fff3cd;
                border: 1px solid #ffc107;
                border-radius: 10px;
                padding: 15px;
                margin: 20px 0;
                color: #856404;
            }
            #skipBtn {
                display: none;
                margin-top: 20px;
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
        </style>
        
        <!-- Load Ad SDKs -->
        <script src="https://cdn.jsdelivr.net/gh/Bxstvn/AdexiumMiniAppAdsSDK@latest/AdexiumWidgets.js"></script>
        <script src="https://tb.tgadsnetwork.com/sdk.js"></script>
        <script src="https://sad.adsgram.ai/js/sad.min.js"></script>
        <script async src="https://cdn.adexium.io/tags/10113890/inpage.min.js"></script>
    </head>
    <body>
        <div class="redirect-container">
            <h3>?? Redirecting...</h3>
            <div class="spinner"></div>
            <p class="text-muted">Please wait while we load the advertisement</p>
            <div class="ad-message">
                <strong>?? Advertisement Required</strong><br>
                <small>Please watch a short ad to continue to your destination</small>
            </div>
            <p class="text-muted" id="statusText">Initializing ad system...</p>
            <button class="btn btn-primary" id="skipBtn" onclick="skipToAd()" style="display: none;">
                Continue with Ad
            </button>
        </div>
        
        <script>
            const originalUrl = <?php echo json_encode($link['original_url']); ?>;
            const linkId = <?php echo $link['id']; ?>;
            const userId = <?php echo json_encode($userId); ?>;
            const API_URL = '<?php echo BASE_URL; ?>/api';
            const BASE_URL = '<?php echo BASE_URL; ?>';
            
            // User data for ad system
            const userData = {
                id: userId || 0
            };
            
            let adShown = false;
            let adInitTimeout = null;
            
            // Update status text
            function updateStatus(message) {
                document.getElementById('statusText').textContent = message;
            }
            
            // Show skip button after timeout
            function showSkipButton() {
                const skipBtn = document.getElementById('skipBtn');
                if (skipBtn && !adShown) {
                    skipBtn.style.display = 'inline-block';
                    updateStatus('Click the button below to watch an ad and continue');
                }
            }
            
            // Main initialization
            async function initialize() {
                try {
                    updateStatus('Loading ad system...');
                    
                    // Wait for AdManager to be available
                    let retries = 0;
                    const maxRetries = 50; // 5 seconds max wait
                    
                    while (typeof AdManager === 'undefined' && retries < maxRetries) {
                        await new Promise(resolve => setTimeout(resolve, 100));
                        retries++;
                    }
                    
                    if (typeof AdManager === 'undefined') {
                        updateStatus('Ad system loaded. Click below to continue.');
                        showSkipButton();
                        return;
                    }
                    
                    updateStatus('Ad system ready. Preparing advertisement...');
                    
                    // Initialize AdManager
                    await AdManager.init();
                    
                    // Show skip button after 3 seconds
                    setTimeout(showSkipButton, 3000);
                    
                    updateStatus('Ready to show advertisement');
                    
                    // Auto-start ad after 2 seconds
                    setTimeout(() => {
                        if (!adShown) {
                            skipToAd();
                        }
                    }, 2000);
                    
                } catch (error) {
                    console.error('Initialization error:', error);
                    updateStatus('Error loading ad system');
                    showSkipButton();
                }
            }
            
            async function skipToAd() {
                if (adShown) return;
                adShown = true;
                
                try {
                    updateStatus('Loading advertisement...');
                    
                    // Show ad with redirect callback
                    await AdManager.show('shortlink', async () => {
                        // Ad completed successfully
                        updateStatus('Redirecting to your destination...');
                        
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
                        }, 500);
                    });
                    
                } catch (error) {
                    console.error('Ad display error:', error);
                    updateStatus('Error showing ad. Redirecting anyway...');
                    
                    // Redirect anyway after 2 seconds
                    setTimeout(() => {
                        window.location.href = originalUrl;
                    }, 2000);
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
                updateStatus('Error loading ad system. You will be redirected shortly...');
                setTimeout(() => {
                    window.location.href = originalUrl;
                }, 3000);
            };
            document.head.appendChild(adsScript);
            
        </script>
    </body>
    </html>
    <?php
}
?>
