// Telegram Web App
const tg = window.Telegram.WebApp;
tg.expand();
tg.ready();

// App State
let userData = null;
let currentScreen = 'home';
let tapCount = 0;
let tapTimer = null;

// API Base URL
const API_URL = 'https://reqa.antipiracyforce.org/test/api';

// Initialize App
document.addEventListener('DOMContentLoaded', async () => {
    try {
        await initializeApp();
    } catch (error) {
        console.error('Initialization error:', error);
        showError('Failed to initialize app');
    }
});

async function initializeApp() {
    // Get Telegram user data
    const initData = tg.initDataUnsafe;
    const user = initData.user;
    
    if (!user) {
        showError('Please open this app from Telegram');
        return;
    }
    
    // Check for referral code
    const urlParams = new URLSearchParams(window.location.search);
    const refCode = urlParams.get('ref');
    
    // Authenticate user
    const authData = {
        telegram_id: user.id,
        username: user.username || '',
        first_name: user.first_name || '',
        last_name: user.last_name || '',
        referral_code: refCode
    };
    
    const response = await fetch(`${API_URL}/auth.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(authData)
    });
    
    const data = await response.json();
    
    if (data.success) {
        userData = data.user;
        updateUI();
        hideLoading();
        
        // Load initial data
        await Promise.all([
            loadTasks(),
            loadGames(),
            loadReferrals(),
            loadWallet(),
            checkSpinAvailability(),
            loadLeaderboard()
        ]);
        
        // Start energy recharge timer
        startEnergyRecharge();
    } else {
        showError('Authentication failed');
    }
}

function hideLoading() {
    document.getElementById('loading-screen').style.display = 'none';
    document.getElementById('main-container').style.display = 'block';
}

function updateUI() {
    document.getElementById('user-name').textContent = userData.first_name;
    updateBalance();
    updateEnergy();
}

function updateBalance() {
    const coinBalance = document.getElementById('coin-balance');
    const balanceUsd = document.getElementById('balance-usd');
    const walletBalance = document.getElementById('wallet-balance');
    const walletUsd = document.getElementById('wallet-usd');
    
    const coins = parseFloat(userData.coins).toFixed(2);
    const usd = (userData.coins * 0.001).toFixed(2);
    
    if (coinBalance) coinBalance.textContent = coins;
    if (balanceUsd) balanceUsd.textContent = `≈ $${usd}`;
    if (walletBalance) walletBalance.textContent = coins;
    if (walletUsd) walletUsd.textContent = `≈ $${usd}`;
}

function updateEnergy() {
    const energyText = document.getElementById('energy-text');
    const energyFill = document.getElementById('energy-fill');
    const rechargeBtn = document.getElementById('btn-recharge-energy');
    
    const energy = userData.energy;
    energyText.textContent = `${energy}/100`;
    energyFill.style.width = `${energy}%`;
    
    if (energy === 0) {
        rechargeBtn.style.display = 'block';
    } else {
        rechargeBtn.style.display = 'none';
    }
}

// Tap Functionality
const tapCoin = document.getElementById('tap-coin');
const tapCounter = document.getElementById('tap-counter');

let isTappingBlocked = false;

tapCoin.addEventListener('click', async (e) => {
    if (isTappingBlocked) {
        showNotification('Please watch the ad to continue tapping!', 'warning');
        return;
    }
    
    if (userData.energy <= 0) {
        showNotification('No energy left! Watch an ad to recharge', 'warning');
        return;
    }
    
    tapCount++;
    
    // Show tap effect
    const rect = tapCoin.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    
    createFloatingText('+5', x, y);
    
    // Vibrate
    if (tg.HapticFeedback) {
        tg.HapticFeedback.impactOccurred('light');
    }
    
    // Update local state
    userData.coins += 5;
    userData.energy -= 1;
    updateBalance();
    updateEnergy();
    
    // Send to server (batched)
    clearTimeout(tapTimer);
    tapTimer = setTimeout(async () => {
        await sendTaps(tapCount);
        tapCount = 0;
    }, 500);
});

async function sendTaps(taps) {
    try {
        const response = await fetch(`${API_URL}/tap.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                user_id: userData.id,
                taps: taps
            })
        });
        
        const data = await response.json();
        if (data.success) {
            userData.coins = data.total_coins;
            userData.energy = data.energy;
            updateBalance();
            updateEnergy();
            
            // Check if ad should be shown (forced)
            if (data.show_ad) {
                // Block tapping until ad is watched
                isTappingBlocked = true;
                tapCoin.style.opacity = '0.5';
                tapCoin.style.cursor = 'not-allowed';
                
                await showAd('tap', () => {
                    // Unblock tapping after ad completion
                    isTappingBlocked = false;
                    tapCoin.style.opacity = '1';
                    tapCoin.style.cursor = 'pointer';
                    showNotification('Ad completed! Keep tapping!', 'success');
                });
            }
        }
    } catch (error) {
        console.error('Tap error:', error);
    }
}

function createFloatingText(text, x, y) {
    const floatingText = document.createElement('div');
    floatingText.textContent = text;
    floatingText.style.cssText = `
        position: absolute;
        left: ${x}px;
        top: ${y}px;
        color: #10b981;
        font-size: 24px;
        font-weight: bold;
        pointer-events: none;
        animation: floatUp 1s ease-out forwards;
    `;
    
    tapCoin.parentElement.appendChild(floatingText);
    
    setTimeout(() => floatingText.remove(), 1000);
}

// Energy Recharge
function startEnergyRecharge() {
    setInterval(async () => {
        if (userData.energy < 100) {
            // Refresh from server
            const response = await fetch(`${API_URL}/auth.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ telegram_id: tg.initDataUnsafe.user.id })
            });
            
            const data = await response.json();
            if (data.success) {
                userData.energy = data.user.energy;
                updateEnergy();
            }
        }
    }, 60000); // Check every minute
}

// Watch Ad for Energy
document.getElementById('btn-recharge-energy').addEventListener('click', async () => {
    await showAd('energy_recharge', async () => {
        userData.energy = Math.min(userData.energy + 5, 100);
        updateEnergy();
        showNotification('Energy recharged!', 'success');
    });
});

// Navigation
const navButtons = document.querySelectorAll('.nav-btn');
navButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        const screen = btn.dataset.screen;
        navigateTo(screen);
    });
});

function navigateTo(screenName) {
    // Hide all screens
    document.querySelectorAll('.screen').forEach(s => s.classList.remove('active'));
    
    // Show target screen
    document.getElementById(`screen-${screenName}`).classList.add('active');
    
    // Update nav
    navButtons.forEach(btn => {
        if (btn.dataset.screen === screenName) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    
    currentScreen = screenName;
    
    // Redraw spin wheel when navigating to spin screen
    if (screenName === 'spin' && spinBlocks.length > 0) {
        setTimeout(() => drawSpinWheel(), 100);
    }
}

// Tasks
async function loadTasks() {
    try {
        const response = await fetch(`${API_URL}/tasks.php?user_id=${userData.id}`);
        const data = await response.json();
        
        if (data.success) {
            renderTasks(data.tasks);
        }
    } catch (error) {
        console.error('Load tasks error:', error);
    }
}

function renderTasks(tasks) {
    const oneTimeTasks = tasks.filter(t => t.type === 'one_time');
    const dailyTasks = tasks.filter(t => t.type === 'daily');
    
    renderTaskList('task-list-one_time', oneTimeTasks);
    renderTaskList('task-list-daily', dailyTasks);
}

function renderTaskList(containerId, tasks) {
    const container = document.getElementById(containerId);
    container.innerHTML = '';
    
    tasks.forEach(task => {
        const taskCard = document.createElement('div');
        taskCard.className = 'task-card';
        
        let actionBtn = '';
        if (task.status === 'available') {
            actionBtn = `<button class="task-action start" onclick="startTask(${task.id})">Start</button>`;
        } else if (task.status === 'pending') {
            actionBtn = `<button class="task-action verify" onclick="verifyTask(${task.id})">Verify</button>`;
        } else {
            actionBtn = `<button class="task-action claimed" disabled>Claimed</button>`;
        }
        
        taskCard.innerHTML = `
            <div class="task-icon"><i class="${task.icon}"></i></div>
            <div class="task-info">
                <div class="task-title">${task.title}</div>
                <div class="task-reward">+${task.reward} coins</div>
            </div>
            ${actionBtn}
        `;
        
        container.appendChild(taskCard);
    });
}

async function startTask(taskId) {
    try {
        const response = await fetch(`${API_URL}/tasks.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                user_id: userData.id,
                task_id: taskId,
                action: 'start'
            })
        });
        
        const data = await response.json();
        if (data.success) {
            // Show ad first
            await showAd('task', async () => {
                // Open URL
                if (data.url) {
                    tg.openLink(data.url);
                }
                // Reload tasks
                await loadTasks();
            });
        }
    } catch (error) {
        console.error('Start task error:', error);
    }
}

async function verifyTask(taskId) {
    try {
        const response = await fetch(`${API_URL}/tasks.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                user_id: userData.id,
                task_id: taskId,
                action: 'verify'
            })
        });
        
        const data = await response.json();
        if (data.success) {
            // Show ad after task verification
            await showAd('task_verify', async () => {
                userData.coins = data.total_coins;
                updateBalance();
                showNotification(`+${data.reward} coins earned!`, 'success');
                await loadTasks();
            });
        } else {
            showNotification(data.error, 'error');
        }
    } catch (error) {
        console.error('Verify task error:', error);
    }
}

// Task tabs
const tabs = document.querySelectorAll('.tab');
tabs.forEach(tab => {
    tab.addEventListener('click', () => {
        const targetTab = tab.dataset.tab;
        
        tabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        
        document.querySelectorAll('.task-list').forEach(list => {
            list.style.display = 'none';
        });
        document.getElementById(`task-list-${targetTab}`).style.display = 'block';
    });
});

// Games
async function loadGames() {
    try {
        const response = await fetch(`${API_URL}/games.php?user_id=${userData.id}`);
        const data = await response.json();
        
        if (data.success) {
            renderGames(data.games);
        }
    } catch (error) {
        console.error('Load games error:', error);
    }
}

function renderGames(games) {
    const container = document.getElementById('game-list');
    container.innerHTML = '';
    
    games.forEach(game => {
        const gameCard = document.createElement('div');
        gameCard.className = 'game-card';
        
        const playBtn = game.can_play 
            ? `<button class="game-action play" onclick="playGame(${game.id}, '${game.game_url}')">Play</button>`
            : `<button class="game-action" disabled>Limit Reached</button>`;
        
        gameCard.innerHTML = `
            <div class="game-icon"><i class="${game.icon || 'fas fa-gamepad'}"></i></div>
            <div class="game-info">
                <div class="game-title">${game.name}</div>
                <div class="game-reward">+${game.reward} coins</div>
                ${game.plays_remaining >= 0 ? `<small>${game.plays_remaining} plays left</small>` : ''}
            </div>
            ${playBtn}
        `;
        
        container.appendChild(gameCard);
    });
}

async function playGame(gameId, gameUrl) {
    await showAd('game_preroll', async () => {
        tg.openLink(gameUrl);
        
        // Mark as played
        const response = await fetch(`${API_URL}/games.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                user_id: userData.id,
                game_id: gameId
            })
        });
        
        const data = await response.json();
        if (data.success) {
            userData.coins = data.total_coins;
            updateBalance();
            showNotification(`+${data.reward} coins earned!`, 'success');
            await loadGames();
        }
    });
}

// Referrals
async function loadReferrals() {
    try {
        const response = await fetch(`${API_URL}/referrals.php?user_id=${userData.id}`);
        const data = await response.json();
        
        if (data.success) {
            renderReferrals(data);
        }
    } catch (error) {
        console.error('Load referrals error:', error);
    }
}

function renderReferrals(data) {
    document.getElementById('ref-total').textContent = data.stats.total;
    document.getElementById('ref-approved').textContent = data.stats.approved;
    document.getElementById('ref-earnings').textContent = data.stats.total_earnings;
    
    document.getElementById('referral-link').value = data.referral_link;
    
    window.telegramShareLink = data.telegram_share_link;
    
    const container = document.getElementById('referral-list');
    container.innerHTML = '<h4>Your Referrals</h4>';
    
    data.referrals.forEach(ref => {
        const item = document.createElement('div');
        item.className = 'referral-item';
        item.innerHTML = `
            <div>
                <div>${ref.username}</div>
                <small>${ref.tasks_completed} tasks completed</small>
            </div>
            <span class="referral-status ${ref.status}">${ref.status}</span>
        `;
        container.appendChild(item);
    });
}

function copyReferralLink() {
    const input = document.getElementById('referral-link');
    input.select();
    document.execCommand('copy');
    showNotification('Link copied!', 'success');
}

function shareReferralLink() {
    tg.openTelegramLink(window.telegramShareLink);
}

// Wallet
async function loadWallet() {
    try {
        const response = await fetch(`${API_URL}/wallet.php?user_id=${userData.id}`);
        const data = await response.json();
        
        if (data.success) {
            renderWallet(data);
        }
    } catch (error) {
        console.error('Load wallet error:', error);
    }
}

function renderWallet(data) {
    // Render payment methods
    const methodSelect = document.getElementById('payment-method');
    if (!methodSelect) {
        console.error('Payment method select element not found');
        return;
    }
    
    methodSelect.innerHTML = '<option value="">Select method...</option>';
    
    // Log for debugging
    console.log('Payment methods:', data.payment_methods);
    
    if (data.payment_methods && data.payment_methods.length > 0) {
        data.payment_methods.forEach(method => {
            const option = document.createElement('option');
            option.value = method.name;
            option.textContent = method.name;
            option.dataset.fields = method.fields_required || '';
            methodSelect.appendChild(option);
        });
    } else {
        console.warn('No payment methods available');
    }
    
    // Add "Enter Manually" option
    const manualOption = document.createElement('option');
    manualOption.value = 'manual';
    manualOption.textContent = '✍️ Enter Manually';
    methodSelect.appendChild(manualOption);
    
    // Render withdrawal history
    const container = document.getElementById('withdrawal-list');
    container.innerHTML = '';
    
    data.withdrawals.forEach(w => {
        const item = document.createElement('div');
        item.className = 'referral-item';
        
        const statusClass = w.status === 'pending' ? 'pending' : (w.status === 'approved' ? 'approved' : 'rejected');
        
        item.innerHTML = `
            <div>
                <div>${w.amount} coins</div>
                <small>${w.payment_method} - ${new Date(w.created_at).toLocaleDateString()}</small>
            </div>
            <span class="referral-status ${statusClass}">${w.status}</span>
        `;
        container.appendChild(item);
    });
}

// Handle payment method change to show appropriate fields
document.getElementById('payment-method').addEventListener('change', function() {
    const fieldsContainer = document.getElementById('payment-fields');
    const selectedOption = this.options[this.selectedIndex];
    const method = this.value;
    
    console.log('Payment method selected:', method);
    
    if (!fieldsContainer) {
        console.error('Payment fields container not found');
        return;
    }
    
    fieldsContainer.innerHTML = '';
    
    if (!method || method === '') {
        // No method selected, show helper text
        fieldsContainer.innerHTML = '<p style="color: #888; font-size: 14px; margin-top: 10px;">Please select a payment method to continue</p>';
        return;
    }
    
    if (method === 'manual') {
        // Show manual entry fields
        fieldsContainer.innerHTML = `
            <div class="form-group">
                <label>Payment Method Name</label>
                <input type="text" name="method_name" placeholder="e.g., PayPal, Bank, Crypto" required>
            </div>
            <div class="form-group">
                <label>Wallet Address / Account Details</label>
                <textarea name="wallet_details" rows="3" placeholder="Enter your wallet address, account number, or payment details" required></textarea>
            </div>
            <div class="form-group">
                <label>Additional Information (Optional)</label>
                <input type="text" name="additional_info" placeholder="Network, IFSC code, etc.">
            </div>
        `;
    } else if (method === 'Crypto') {
        // Show crypto-specific fields with coin and network selection
        fieldsContainer.innerHTML = `
            <div class="form-group">
                <label>Select Cryptocurrency</label>
                <select name="crypto_coin" id="crypto-coin" required>
                    <option value="">Choose coin...</option>
                    <option value="USDT">USDT (Tether)</option>
                    <option value="Bitcoin">Bitcoin (BTC)</option>
                    <option value="Ethereum">Ethereum (ETH)</option>
                    <option value="BNB">BNB (Binance Coin)</option>
                    <option value="USDC">USDC</option>
                    <option value="TRX">TRON (TRX)</option>
                </select>
            </div>
            <div class="form-group" id="network-group" style="display: none;">
                <label>Select Network</label>
                <select name="crypto_network" id="crypto-network" required>
                    <option value="">Choose network...</option>
                </select>
            </div>
            <div class="form-group">
                <label>Wallet Address</label>
                <input type="text" name="wallet_address" placeholder="Enter your wallet address" required>
            </div>
            <div class="form-group">
                <label>Memo/Tag (if required)</label>
                <input type="text" name="memo" placeholder="Optional">
            </div>
        `;
        
        // Add event listener for crypto coin selection to show networks
        document.getElementById('crypto-coin').addEventListener('change', function() {
            const networkGroup = document.getElementById('network-group');
            const networkSelect = document.getElementById('crypto-network');
            const coin = this.value;
            
            networkSelect.innerHTML = '<option value="">Choose network...</option>';
            
            if (coin === 'USDT') {
                networkGroup.style.display = 'block';
                networkSelect.innerHTML += `
                    <option value="TRC20">TRC20 (TRON)</option>
                    <option value="ERC20">ERC20 (Ethereum)</option>
                    <option value="BEP20">BEP20 (BSC)</option>
                    <option value="Polygon">Polygon (MATIC)</option>
                `;
                networkSelect.required = true;
            } else if (coin === 'Ethereum' || coin === 'USDC') {
                networkGroup.style.display = 'block';
                networkSelect.innerHTML += `
                    <option value="ERC20">ERC20 (Ethereum)</option>
                    <option value="BEP20">BEP20 (BSC)</option>
                    <option value="Polygon">Polygon (MATIC)</option>
                `;
                networkSelect.required = true;
            } else if (coin === 'BNB') {
                networkGroup.style.display = 'block';
                networkSelect.innerHTML += `
                    <option value="BEP20">BEP20 (BSC)</option>
                    <option value="BEP2">BEP2 (Binance Chain)</option>
                `;
                networkSelect.required = true;
            } else if (coin === 'Bitcoin' || coin === 'TRX') {
                networkGroup.style.display = 'none';
                networkSelect.required = false;
            } else {
                networkGroup.style.display = 'none';
                networkSelect.required = false;
            }
        });
    } else if (method && selectedOption.dataset.fields) {
        // Show predefined fields based on payment method
        const fieldsStr = selectedOption.dataset.fields.trim();
        console.log('Fields for method:', fieldsStr);
        
        if (fieldsStr) {
            const fields = fieldsStr.split(',');
            fields.forEach(field => {
                const fieldName = field.trim();
                if (fieldName) {
                    const label = fieldName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    fieldsContainer.innerHTML += `
                        <div class="form-group">
                            <label>${label}</label>
                            <input type="text" name="${fieldName}" placeholder="Enter ${label.toLowerCase()}" required>
                        </div>
                    `;
                }
            });
        } else {
            // No fields defined, show generic field
            fieldsContainer.innerHTML = `
                <div class="form-group">
                    <label>Account Details</label>
                    <textarea name="account_details" rows="3" placeholder="Enter your ${method} account details" required></textarea>
                </div>
            `;
        }
    } else {
        // Fallback for methods without defined fields
        fieldsContainer.innerHTML = `
            <div class="form-group">
                <label>Account Details</label>
                <textarea name="account_details" rows="3" placeholder="Enter your ${method} account details" required></textarea>
            </div>
        `;
    }
    
    console.log('Payment fields rendered');
});

// Withdrawal form
document.getElementById('withdrawal-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const amount = parseFloat(document.getElementById('withdrawal-amount').value);
    let method = document.getElementById('payment-method').value;
    
    // Collect payment details based on method
    const paymentDetails = {};
    document.querySelectorAll('#payment-fields input, #payment-fields textarea').forEach(input => {
        if (input.value) {
            paymentDetails[input.name] = input.value;
        }
    });
    
    // If manual entry, use the custom method name
    if (method === 'manual') {
        method = paymentDetails.method_name || 'Manual Entry';
        delete paymentDetails.method_name;
    }
    
    try {
        const response = await fetch(`${API_URL}/wallet.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                user_id: userData.id,
                amount: amount,
                payment_method: method,
                payment_details: paymentDetails
            })
        });
        
        const data = await response.json();
        if (data.success) {
            showNotification('Withdrawal request submitted!', 'success');
            document.getElementById('withdrawal-form').reset();
            document.getElementById('payment-fields').innerHTML = '';
            await loadWallet();
        } else {
            showNotification(data.error, 'error');
        }
    } catch (error) {
        console.error('Withdrawal error:', error);
        showNotification('Failed to submit withdrawal', 'error');
    }
});

// Leaderboard
async function loadLeaderboard() {
    try {
        const response = await fetch(`${API_URL}/leaderboard.php?user_id=${userData.id}&type=coins`);
        const data = await response.json();
        
        if (data.success) {
            renderLeaderboard(data);
        }
    } catch (error) {
        console.error('Load leaderboard error:', error);
    }
}

function renderLeaderboard(data) {
    const container = document.getElementById('leaderboard-list');
    container.innerHTML = '';
    
    data.leaderboard.forEach(item => {
        const div = document.createElement('div');
        div.className = `leaderboard-item ${item.rank <= 3 ? 'top-' + item.rank : ''}`;
        
        const medal = item.rank === 1 ? '🥇' : item.rank === 2 ? '🥈' : item.rank === 3 ? '🥉' : '';
        
        div.innerHTML = `
            <div class="rank-badge">${medal || item.rank}</div>
            <div class="flex-grow-1">
                <div class="font-weight-bold">${item.username}</div>
                <small>${item.value} coins</small>
            </div>
            ${item.is_current_user ? '<span class="badge bg-primary">You</span>' : ''}
        `;
        
        container.appendChild(div);
    });
    
    if (data.user_rank) {
        document.getElementById('user-rank-number').textContent = `#${data.user_rank.rank}`;
        document.getElementById('user-rank-value').textContent = `${data.user_rank.value} coins`;
    }
}

// Spin Wheel
let spinBlocks = [];
let wheelCanvas = null;
let wheelCtx = null;
let currentRotation = 0;
let isSpinning = false;

async function checkSpinAvailability() {
    try {
        const response = await fetch(`${API_URL}/spin.php?user_id=${userData.id}`);
        const data = await response.json();
        
        if (data.success) {
            // Store blocks for wheel rendering
            if (data.blocks && data.blocks.length > 0) {
                spinBlocks = data.blocks;
                drawSpinWheel();
            }
            updateSpinUI(data);
        }
    } catch (error) {
        console.error('Check spin error:', error);
    }
}

function drawSpinWheel(rotation = 0) {
    if (!wheelCanvas) {
        wheelCanvas = document.getElementById('wheel-canvas');
        if (!wheelCanvas) {
            console.error('Wheel canvas not found!');
            return;
        }
        wheelCtx = wheelCanvas.getContext('2d');
    }
    
    if (!spinBlocks || spinBlocks.length === 0) {
        console.warn('No spin blocks available to draw');
        return;
    }
    
    const centerX = wheelCanvas.width / 2;
    const centerY = wheelCanvas.height / 2;
    const radius = Math.min(centerX, centerY) - 10;
    
    // Clear canvas
    wheelCtx.clearRect(0, 0, wheelCanvas.width, wheelCanvas.height);
    
    // Save context and apply rotation
    wheelCtx.save();
    wheelCtx.translate(centerX, centerY);
    wheelCtx.rotate(rotation);
    wheelCtx.translate(-centerX, -centerY);
    
    // Define vibrant colors for blocks
    const colors = [
        '#FF6B6B', '#4ECDC4', '#FFD93D', '#95E1D3',
        '#F38181', '#6C5CE7', '#A8E6CF', '#FF8B94'
    ];
    
    const totalBlocks = spinBlocks.length;
    const anglePerBlock = (2 * Math.PI) / totalBlocks;
    
    // Draw each segment
    spinBlocks.forEach((block, index) => {
        const startAngle = index * anglePerBlock - Math.PI / 2;
        const endAngle = startAngle + anglePerBlock;
        
        // Draw segment
        wheelCtx.beginPath();
        wheelCtx.moveTo(centerX, centerY);
        wheelCtx.arc(centerX, centerY, radius, startAngle, endAngle);
        wheelCtx.closePath();
        
        // Fill with color
        wheelCtx.fillStyle = colors[index % colors.length];
        wheelCtx.fill();
        
        // Draw border
        wheelCtx.strokeStyle = '#ffffff';
        wheelCtx.lineWidth = 3;
        wheelCtx.stroke();
        
        // Draw text
        wheelCtx.save();
        wheelCtx.translate(centerX, centerY);
        wheelCtx.rotate(startAngle + anglePerBlock / 2);
        wheelCtx.textAlign = 'center';
        wheelCtx.textBaseline = 'middle';
        wheelCtx.fillStyle = '#ffffff';
        wheelCtx.font = 'bold 20px Arial';
        wheelCtx.shadowColor = 'rgba(0, 0, 0, 0.7)';
        wheelCtx.shadowBlur = 5;
        
        // Draw block label
        wheelCtx.fillText(block.block_label, radius * 0.65, 0);
        
        wheelCtx.shadowBlur = 0;
        wheelCtx.restore();
    });
    
    // Restore context before drawing center circle
    wheelCtx.restore();
    
    // Draw center circle (not rotated)
    wheelCtx.beginPath();
    wheelCtx.arc(centerX, centerY, 25, 0, 2 * Math.PI);
    
    // Add gradient to center
    const gradient = wheelCtx.createRadialGradient(centerX, centerY, 0, centerX, centerY, 25);
    gradient.addColorStop(0, '#ffffff');
    gradient.addColorStop(1, '#f0f0f0');
    wheelCtx.fillStyle = gradient;
    wheelCtx.fill();
    
    wheelCtx.strokeStyle = '#333333';
    wheelCtx.lineWidth = 3;
    wheelCtx.stroke();
}

function updateSpinUI(data) {
    const spinInfo = document.getElementById('spin-info');
    const spinBtn = document.getElementById('btn-spin');
    
    if (data.can_spin) {
        spinInfo.textContent = `You have a free spin! (${data.spins_today}/${data.daily_limit} today)`;
        spinBtn.disabled = false;
    } else {
        if (data.next_spin_time) {
            const timeLeft = Math.max(0, data.next_spin_time - Math.floor(Date.now() / 1000));
            const minutes = Math.floor(timeLeft / 60);
            spinInfo.textContent = `Next spin in ${minutes} minutes`;
        } else {
            spinInfo.textContent = data.reason;
        }
        spinBtn.disabled = true;
    }
}

async function animateSpinWheel(winningBlock) {
    return new Promise((resolve) => {
        if (isSpinning) return;
        isSpinning = true;
        
        // Find the index of the winning block
        const winningIndex = spinBlocks.findIndex(b => b.block_label === winningBlock);
        if (winningIndex === -1) {
            console.error('Winning block not found:', winningBlock);
            isSpinning = false;
            resolve();
            return;
        }
        
        const totalBlocks = spinBlocks.length;
        const anglePerBlock = (2 * Math.PI) / totalBlocks;
        
        // Calculate target angle (pointer is at top, so we need to rotate to align winning block with top)
        // Winning block should end up at the top (pointer position)
        const targetBlockAngle = (winningIndex * anglePerBlock) + (anglePerBlock / 2);
        const minSpins = 5; // Minimum full rotations
        const randomExtra = Math.random() * Math.PI * 2; // Random extra rotation
        const targetRotation = (minSpins * Math.PI * 2) + targetBlockAngle + randomExtra;
        
        // Animation parameters
        const duration = 5000; // 5 seconds
        const startTime = Date.now();
        const startRotation = currentRotation;
        
        console.log('Starting spin animation:', {
            winningBlock,
            winningIndex,
            targetRotation: (targetRotation * 180 / Math.PI).toFixed(2) + '°',
            duration: duration + 'ms'
        });
        
        function animate() {
            const now = Date.now();
            const elapsed = now - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Easing function for smooth deceleration
            const easeOut = 1 - Math.pow(1 - progress, 4);
            
            currentRotation = startRotation + (targetRotation * easeOut);
            drawSpinWheel(currentRotation);
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                isSpinning = false;
                console.log('✅ Spin animation completed');
                resolve();
            }
        }
        
        requestAnimationFrame(animate);
    });
}

document.getElementById('btn-spin').addEventListener('click', async () => {
    const spinBtn = document.getElementById('btn-spin');
    const spinInfo = document.getElementById('spin-info');
    
    if (isSpinning) {
        showNotification('Please wait for the current spin to complete', 'warning');
        return;
    }
    
    spinBtn.disabled = true;
    const originalText = spinInfo.textContent;
    spinInfo.textContent = '📺 Please watch the ad first...';
    
    try {
        console.log('🎬 Showing ad before spin...');
        
        // First show the ad - the spin will NOT start until ad is completed
        await showAd('spin', async () => {
            console.log('✅ Ad completed, now performing spin...');
            spinInfo.textContent = '🎰 Spinning...';
            
            // Get the spin result from server
            const response = await fetch(`${API_URL}/spin.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    user_id: userData.id,
                    double_reward: false
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                console.log('🎯 Spin result received:', data.block);
                
                // Animate the wheel to the winning block
                await animateSpinWheel(data.block);
                
                // Update user data
                userData.coins = data.total_coins;
                updateBalance();
                
                // Show detailed spin result with block information
                const blockEmoji = getBlockEmoji(data.block);
                showNotification(`🎉 Congratulations!\n\n${blockEmoji} Block: ${data.block}\n💰 Reward: ${data.reward} coins`, 'success');
                
                // Also log to console for debugging
                console.log('Spin Result:', {
                    block: data.block,
                    reward: data.reward,
                    total_coins: data.total_coins
                });
                
                // Refresh spin availability
                await checkSpinAvailability();
            } else {
                showNotification(data.error || 'Spin failed', 'error');
                spinBtn.disabled = false;
                spinInfo.textContent = originalText;
            }
        });
    } catch (error) {
        console.error('Spin error:', error);
        showNotification('Spin failed. Please try again.', 'error');
        spinBtn.disabled = false;
        spinInfo.textContent = originalText;
    }
});

// Notifications
function showNotification(message, type = 'info') {
    tg.showAlert(message);
}

function showError(message) {
    tg.showAlert(message);
}

// Helper function to get emoji for spin blocks
function getBlockEmoji(blockLabel) {
    const emojiMap = {
        '10': '🎯',
        '20': '🎲',
        '50': '🎰',
        '100': '💎',
        '200': '🌟',
        '500': '⭐',
        '1000': '🏆',
        'JACKPOT': '💰'
    };
    
    // Try to find exact match or partial match
    for (const [key, emoji] of Object.entries(emojiMap)) {
        if (blockLabel.includes(key)) {
            return emoji;
        }
    }
    
    return '🎁'; // Default emoji
}
