<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Please Wait...</title>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    
    <!-- Ad Networks SDKs -->
    <script type="text/javascript" src="https://cdn.tgads.space/assets/js/adexium-widget.min.js"></script>
    <script src="//libtl.com/sdk.js" data-zone="10055887" data-sdk="show_10055887"></script>
    <script src="https://richinfo.co/richpartners/telegram/js/tg-ob.js"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px 30px;
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        
        .icon {
            font-size: 60px;
            margin-bottom: 20px;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        p {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .task-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .task-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }
        
        .btn {
            display: inline-block;
            padding: 15px 40px;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            margin-top: 10px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #e9ecef;
            color: #333;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }
        
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .countdown {
            font-size: 48px;
            font-weight: bold;
            color: #667eea;
            margin: 20px 0;
        }
        
        .ad-container {
            min-height: 250px;
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .hidden {
            display: none !important;
        }
        
        .step {
            display: inline-block;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e9ecef;
            color: #666;
            line-height: 30px;
            margin: 0 5px;
            font-weight: 600;
        }
        
        .step.active {
            background: #667eea;
            color: white;
        }
        
        .step.completed {
            background: #38ef7d;
            color: white;
        }
        
        .steps {
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🎬</div>
        
        <!-- Steps Indicator -->
        <div class="steps" id="steps">
            <span class="step active" id="step1">1</span>
            <span class="step" id="step2">2</span>
            <span class="step" id="step3">3</span>
        </div>
        
        <!-- Loading State -->
        <div id="loading-state">
            <h1>Loading...</h1>
            <div class="loader"></div>
            <p>Please wait while we prepare your content</p>
        </div>
        
        <!-- Task State (for task_video mode) -->
        <div id="task-state" class="hidden">
            <h1>Complete Task First</h1>
            <div class="task-section">
                <div class="task-title" id="task-title">Complete the task</div>
                <p id="task-description"></p>
                <a href="#" id="task-link" class="btn btn-secondary" target="_blank">
                    🎯 Start Task
                </a>
            </div>
            <button class="btn btn-success" id="btn-task-completed" disabled>
                ✓ I Completed the Task
            </button>
            <p class="mt-3"><small>Complete the task first to unlock the next step</small></p>
        </div>
        
        <!-- Ad State -->
        <div id="ad-state" class="hidden">
            <h1>Watch Video</h1>
            <p>Please watch the video to continue to your destination</p>
            
            <div class="ad-container" id="ad-container">
                <div class="loader"></div>
            </div>
            
            <button class="btn btn-primary" id="btn-play-ad">
                ▶️ Play Video
            </button>
            
            <p class="mt-3"><small>Video duration: ~30 seconds</small></p>
        </div>
        
        <!-- Redirect State -->
        <div id="redirect-state" class="hidden">
            <h1>✅ All Done!</h1>
            <p>Redirecting you in <span class="countdown" id="countdown">3</span> seconds...</p>
            <button class="btn btn-primary" id="btn-redirect-now">
                Go Now
            </button>
        </div>
        
        <!-- Error State -->
        <div id="error-state" class="hidden">
            <div class="icon">⚠️</div>
            <h1>Oops!</h1>
            <p id="error-message">Something went wrong. Please try again.</p>
            <button class="btn btn-secondary" onclick="location.reload()">
                Try Again
            </button>
        </div>
    </div>
    
    <script>
        const tg = window.Telegram.WebApp;
        tg.ready();
        tg.expand();
        
        // Get short code from URL
        const urlParts = window.location.pathname.split('/');
        const shortCode = urlParts[urlParts.length - 1] || new URLSearchParams(window.location.search).get('code');
        
        let linkData = null;
        let adNetwork = null;
        let adUnitCode = null;
        let destinationUrl = null;
        
        // States
        const states = {
            loading: document.getElementById('loading-state'),
            task: document.getElementById('task-state'),
            ad: document.getElementById('ad-state'),
            redirect: document.getElementById('redirect-state'),
            error: document.getElementById('error-state')
        };
        
        // Step indicators
        const steps = {
            step1: document.getElementById('step1'),
            step2: document.getElementById('step2'),
            step3: document.getElementById('step3')
        };
        
        function showState(stateName) {
            Object.values(states).forEach(el => el.classList.add('hidden'));
            if (states[stateName]) {
                states[stateName].classList.remove('hidden');
            }
        }
        
        function updateStep(stepNum) {
            for (let i = 1; i <= 3; i++) {
                if (i < stepNum) {
                    steps['step' + i].classList.add('completed');
                    steps['step' + i].classList.remove('active');
                } else if (i === stepNum) {
                    steps['step' + i].classList.add('active');
                } else {
                    steps['step' + i].classList.remove('active', 'completed');
                }
            }
        }
        
        function showError(message) {
            document.getElementById('error-message').textContent = message;
            showState('error');
        }
        
        async function loadShortLink() {
            try {
                const baseUrl = window.location.origin + '/test';
                const response = await fetch(`${baseUrl}/api/shortener.php?code=${shortCode}`);
                const data = await response.json();
                
                if (!data.success) {
                    showError(data.message || 'Link not found or expired');
                    return;
                }
                
                linkData = data.data;
                destinationUrl = linkData.original_url;
                
                // Increment click count
                const baseUrl = window.location.origin + '/test';
                fetch(`${baseUrl}/api/shortener.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'click', code: shortCode })
                });
                
                // Determine flow based on mode
                if (linkData.mode === 'task_video') {
                    // Show task first
                    setupTaskMode();
                } else {
                    // Direct ad mode - skip to ad
                    updateStep(2);
                    setupAdMode();
                }
            } catch (error) {
                console.error('Error loading link:', error);
                showError('Failed to load link. Please try again.');
            }
        }
        
        function setupTaskMode() {
            updateStep(1);
            document.getElementById('task-title').textContent = linkData.task_title || 'Complete the task';
            document.getElementById('task-description').textContent = linkData.task_description || 'Click the button below to start the task';
            
            const taskLink = document.getElementById('task-link');
            taskLink.href = linkData.task_url || '#';
            
            // Enable "I Completed" button after clicking task link
            taskLink.addEventListener('click', () => {
                setTimeout(() => {
                    document.getElementById('btn-task-completed').disabled = false;
                }, 2000);
            });
            
            document.getElementById('btn-task-completed').addEventListener('click', () => {
                updateStep(2);
                setupAdMode();
            });
            
            showState('task');
        }
        
        function setupAdMode() {
            adNetwork = linkData.ad_network;
            adUnitCode = linkData.ad_unit_code;
            
            document.getElementById('btn-play-ad').addEventListener('click', playAd);
            
            showState('ad');
        }
        
        function playAd() {
            document.getElementById('btn-play-ad').disabled = true;
            
            // Show ad based on network
            switch(adNetwork) {
                case 'Adexium':
                    playAdexiumAd();
                    break;
                case 'Monetag':
                    playMonetagAd();
                    break;
                case 'Richads':
                    playRichadsAd();
                    break;
                case 'Adsgram':
                    playAdsgramAd();
                    break;
                default:
                    // Simulate ad for testing
                    simulateAd();
            }
        }
        
        function playAdexiumAd() {
            try {
                const widget = new AdexiumWidget({
                    wid: adUnitCode,
                    adFormat: 'interstitial'
                });
                widget.show();
                // Simulate completion after 3 seconds
                setTimeout(onAdComplete, 3000);
            } catch (error) {
                console.error('Adexium error:', error);
                simulateAd();
            }
        }
        
        function playMonetagAd() {
            try {
                if (typeof show_10055887 !== 'undefined') {
                    show_10055887().then(() => {
                        onAdComplete();
                    }).catch(() => {
                        simulateAd();
                    });
                } else {
                    simulateAd();
                }
            } catch (error) {
                console.error('Monetag error:', error);
                simulateAd();
            }
        }
        
        function playRichadsAd() {
            try {
                if (window.TelegramAdsController) {
                    // Richads implementation
                    setTimeout(onAdComplete, 3000);
                } else {
                    simulateAd();
                }
            } catch (error) {
                console.error('Richads error:', error);
                simulateAd();
            }
        }
        
        function playAdsgramAd() {
            // Adsgram is Telegram-native, would need Telegram's ad API
            simulateAd();
        }
        
        function simulateAd() {
            // Simulate ad playback for testing
            document.getElementById('ad-container').innerHTML = '<p style="color: #667eea; font-weight: bold;">🎬 Video Ad Playing...</p>';
            setTimeout(onAdComplete, 3000);
        }
        
        function onAdComplete() {
            updateStep(3);
            
            // Log conversion
            const baseUrl = window.location.origin + '/test';
            fetch(`${baseUrl}/api/shortener.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'convert', code: shortCode })
            });
            
            showState('redirect');
            startCountdown();
        }
        
        function startCountdown() {
            let count = 3;
            const countdownEl = document.getElementById('countdown');
            
            const interval = setInterval(() => {
                count--;
                countdownEl.textContent = count;
                
                if (count <= 0) {
                    clearInterval(interval);
                    redirect();
                }
            }, 1000);
            
            document.getElementById('btn-redirect-now').addEventListener('click', () => {
                clearInterval(interval);
                redirect();
            });
        }
        
        function redirect() {
            if (destinationUrl) {
                window.location.href = destinationUrl;
            } else {
                showError('Destination URL not found');
            }
        }
        
        // Initialize
        if (!shortCode) {
            showError('Invalid or missing link code');
        } else {
            loadShortLink();
        }
    </script>
</body>
</html>
