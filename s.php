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
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Redirecting...</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .redirect-container {
                background: white;
                border-radius: 20px;
                padding: 40px;
                max-width: 600px;
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
        </style>
    </head>
    <body>
        <div class="redirect-container">
            <h3>Please wait...</h3>
            <div class="spinner"></div>
            <p class="text-muted">Redirecting you in <span id="countdown">3</span> seconds...</p>
            <div id="adSpace" style="min-height: 200px; margin: 20px 0;"></div>
            <button class="btn btn-primary" onclick="skipRedirect()">Skip Ad</button>
        </div>
        
        <script>
            let countdown = 3;
            const originalUrl = <?php echo json_encode($link['original_url']); ?>;
            const linkId = <?php echo $link['id']; ?>;
            const userId = <?php echo json_encode($userId); ?>;
            
            const timer = setInterval(() => {
                countdown--;
                document.getElementById('countdown').textContent = countdown;
                
                if (countdown <= 0) {
                    clearInterval(timer);
                    redirect();
                }
            }, 1000);
            
            function skipRedirect() {
                clearInterval(timer);
                redirect();
            }
            
            function redirect() {
                // Record conversion
                if (userId) {
                    fetch('<?php echo BASE_URL; ?>/api/track.php?action=conversion&link_id=' + linkId + '&user_id=' + userId);
                }
                window.location.href = originalUrl;
            }
        </script>
    </body>
    </html>
    <?php
}
?>
