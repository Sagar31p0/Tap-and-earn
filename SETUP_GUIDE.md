# Telegram Bot Earn App - Complete Setup Guide

## 🎯 Overview

This is a complete Telegram Mini App earning platform with:
- ✅ Tap to earn coins
- ✅ Spin wheel with rewards
- ✅ Task completion system
- ✅ Games integration
- ✅ Referral program
- ✅ Multi-payment withdrawal system
- ✅ 4 Ad networks (Adexium, Monetag, Adsgram, Richads)

---

## 📋 Prerequisites

1. PHP 7.2+ with PDO MySQL support
2. MySQL/MariaDB database
3. Telegram Bot Token (from [@BotFather](https://t.me/BotFather))
4. HTTPS enabled domain (required for Telegram Mini Apps)

---

## 🚀 Installation Steps

### Step 1: Database Setup

1. Import the database schema:
```bash
mysql -u your_username -p your_database < database.sql
```

2. Update database credentials in `config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_database_password');
```

### Step 2: Bot Configuration

1. Create a Telegram bot via [@BotFather](https://t.me/BotFather)
2. Get your bot token
3. Update `config.php`:
```php
define('BOT_TOKEN', 'YOUR_BOT_TOKEN_HERE');
define('BOT_USERNAME', '@your_bot_username');
define('BASE_URL', 'https://yourdomain.com');
```

### Step 3: Upload Files

Upload all files to your web server:
- All PHP files (root directory)
- `/admin/` folder
- `/api/` folder
- `/css/` folder
- `/js/` folder
- `index.html`

Make sure your server has write permissions for error logs.

### Step 4: Set Bot Commands

Go to [@BotFather](https://t.me/BotFather) and set your bot commands:

```
start - Start the bot and open app
help - Show help message
balance - Check your balance
spin - Open spin wheel
tasks - View available tasks
wallet - Manage withdrawals
games - Play and earn games
```

### Step 5: Setup Webhook

Visit: `https://yourdomain.com/webhook.php?action=set`

This will configure your bot to receive updates. You should see:
```json
{
    "success": true,
    "message": "Webhook set successfully!"
}
```

To verify webhook is working, visit: `https://yourdomain.com/webhook.php?action=info`

### Step 6: Configure Ad Networks

All ad networks are already integrated in the code. You just need to verify/update the IDs:

**In `database.sql` - `ad_units` table:**

1. **Adexium** - Update widget ID if needed (currently: `wid-123456`)
2. **Monetag** - Update zone ID if needed (currently: `10055887`)
3. **Adsgram** - Update block IDs (currently: `16414`, `int-16415`, `123456`)
4. **Richads** - Update unit IDs (currently: `#375144`, `#375142`, `#375146`, `#375148`)

**SDKs are auto-loaded in `index.html`:**
- ✅ Adexium: `https://cdn.tgads.space/assets/js/adexium-widget.min.js`
- ✅ Monetag: `https://libtl.com/sdk.js`
- ✅ Adsgram: `https://sad.adsgram.ai/js/sad.min.js`
- ✅ Richads: `https://richinfo.co/richpartners/telegram/js/tg-ob.js`

### Step 7: Admin Panel Setup

1. Access admin panel: `https://yourdomain.com/admin/`
2. Default credentials:
   - Username: `admin`
   - Password: `admin123` (Change this immediately!)

3. **Change admin password:**
   - Go to Settings
   - Update password
   - Or manually update in database (password is hashed with bcrypt)

---

## 🔧 Configuration

### Energy System

Default settings (can be changed in admin panel):
- Maximum energy: 100
- Energy per tap: 1
- Recharge rate: 5 energy per 5 minutes
- Watch ad to fully recharge: Enabled

### Spin Wheel

Configure in admin panel → Spin Settings:
- Daily spin limit: 10
- Time between spins: 1 hour
- Reward blocks: 10, 20, 50, 100, 200, 500, 1000, JACKPOT
- Probabilities: Customizable per block

### Tasks

Add tasks in admin panel → Tasks:
- One-time tasks
- Daily tasks (reset every 24h)
- Social media tasks
- Ad watching tasks

### Withdrawal Methods

Configured in `payment_methods` table:
1. **PayPal** - Requires: `paypal_email`
2. **Bank Transfer** - Requires: `account_holder_name`, `account_number`, `bank_name`, `ifsc_code`
3. **UPI** - Requires: `upi_id`
4. **Crypto** - Requires: `wallet_address`, supports multiple coins and networks:
   - USDT (TRC20, ERC20, BEP20, Polygon)
   - Bitcoin (BTC)
   - Ethereum (ETH)
   - BNB (BEP20, BEP2)
   - USDC (ERC20, BEP20, Polygon)
   - TRON (TRX)

### Referral System

Default settings:
- Commission: 10% of referral's earnings
- Minimum referrals for withdrawal: None (configurable)

---

## 🎮 Testing Your Bot

### 1. Test /start Command
- Send `/start` to your bot
- Should receive welcome message with "Open App" button
- Clicking button should open the web app

### 2. Test Web App
- Tap the coin to earn
- Navigate between screens (Home, Spin, Tasks, etc.)
- Check if balance updates

### 3. Test Spin Wheel
- Go to Spin section
- Click "SPIN NOW"
- Should show animation and reward
- Try "Watch Ad & Double" feature

### 4. Test Tasks
- Complete a task
- Verify coins are added to balance

### 5. Test Withdrawal
- Go to Wallet
- Select payment method
- Fill in details (try Crypto with different coins)
- Submit request
- Check in admin panel → Withdrawals

### 6. Test Ad Networks
- Each ad placement should rotate between networks
- Check browser console for ad loading logs
- Verify fallback chain works if primary ad fails

---

## 🐛 Troubleshooting

### Bot not responding to commands

**Check:**
1. Webhook is set correctly: `webhook.php?action=info`
2. Bot token is correct in `config.php`
3. Server can receive HTTPS requests
4. Check error logs

**Fix:**
```bash
# Check error log
tail -f error.log

# Re-set webhook
curl "https://yourdomain.com/webhook.php?action=set"
```

### Web app not loading

**Check:**
1. BASE_URL in `config.php` is correct
2. All files uploaded correctly
3. index.html is accessible
4. Browser console for JavaScript errors

### Ads not showing

**Check:**
1. Ad network SDKs are loading (check browser console)
2. Ad units IDs are correct in database
3. Ad networks are enabled in `ad_networks` table
4. Check `ad_logs` table for error events

**Debug:**
```javascript
// Open browser console on web app
AdManager.show('tap', () => console.log('Ad completed'));
```

### Database connection error

**Check:**
1. Database credentials in `config.php`
2. Database exists and is accessible
3. User has proper permissions
4. Database tables are created

**Test:**
```bash
mysql -u your_user -p your_database -e "SHOW TABLES;"
```

### Spin "Coming Soon" message

**Fixed in bot.php!** The bot now opens the web app directly instead of showing "coming soon" message.

If still showing:
1. Make sure you're using the NEW `bot.php` file
2. Re-set webhook: `webhook.php?action=set`
3. Clear bot updates: `webhook.php?action=delete` then `?action=set`

---

## 📊 Admin Panel Features

Access: `https://yourdomain.com/admin/`

### Dashboard
- Total users
- Total coins distributed
- Active users
- Withdrawal statistics

### Users Management
- View all users
- Edit user coins
- Ban/unban users
- View user activity

### Withdrawals
- Approve/reject withdrawal requests
- View withdrawal history
- Export withdrawal data

### Tasks
- Add/edit/delete tasks
- Set rewards and requirements
- View task completion stats

### Games
- Add external games
- Set play rewards
- Track game plays

### Spin Settings
- Configure reward blocks
- Set probabilities
- Adjust daily limits

### Ad Networks
- Enable/disable networks
- View ad performance
- Manage ad placements

### Settings
- Change admin password
- Configure system settings
- Set withdrawal limits
- Adjust energy settings

---

## 🔒 Security Recommendations

1. **Change default admin password immediately**
2. **Use strong database passwords**
3. **Enable HTTPS (required for Telegram)**
4. **Set proper file permissions:**
   ```bash
   chmod 644 *.php
   chmod 755 admin/ api/ css/ js/
   chmod 600 config.php
   ```
5. **Disable error display in production:**
   ```php
   ini_set('display_errors', 0);
   ```
6. **Regular backups:**
   ```bash
   mysqldump -u user -p database > backup_$(date +%Y%m%d).sql
   ```
7. **Keep bot token secret** - Never commit to public repositories

---

## 📱 Telegram Mini App Settings

In [@BotFather](https://t.me/BotFather):

1. `/newapp` - Create new Mini App
2. Select your bot
3. Set app URL: `https://yourdomain.com/index.html`
4. Upload app icon (512x512 px)
5. Set short description and description

In bot settings:
- `/setdescription` - Set bot description
- `/setabouttext` - Set about text
- `/setuserpic` - Upload bot profile picture

---

## 🎨 Customization

### Branding
- Update bot name in config.php
- Change colors in `css/style.css`
- Update welcome messages in `bot.php`

### Coin Value
Default: 1 coin = $0.001
Change in conversion functions:
```javascript
// js/app.js
const usdValue = coins * 0.001; // Adjust multiplier
```

### Minimum Withdrawal
Update in `settings` table or admin panel

### Referral Commission
Update in `settings` table or referral logic in `api/referrals.php`

---

## 📈 Monitoring & Analytics

### Check Bot Health
```bash
# View webhook status
curl "https://yourdomain.com/webhook.php?action=info"

# Check recent errors
tail -n 100 error.log
```

### Database Queries

**Active users today:**
```sql
SELECT COUNT(*) FROM users WHERE DATE(last_active) = CURDATE();
```

**Total earnings distributed:**
```sql
SELECT SUM(coins) FROM users;
```

**Pending withdrawals:**
```sql
SELECT COUNT(*), SUM(amount) FROM withdrawals WHERE status = 'pending';
```

**Ad network performance:**
```sql
SELECT 
    an.name, 
    COUNT(*) as total_shows,
    SUM(CASE WHEN al.event = 'complete' THEN 1 ELSE 0 END) as completed
FROM ad_logs al
JOIN ad_units au ON al.ad_unit_id = au.id
JOIN ad_networks an ON au.network_id = an.id
GROUP BY an.name;
```

---

## 🆘 Support & Updates

### Included Files:
- ✅ `bot.php` - NEW! Telegram bot handler with all commands
- ✅ `webhook.php` - NEW! Webhook setup and management
- ✅ All web app files (index.html, js/app.js, js/ads.js)
- ✅ All API endpoints (complete and functional)
- ✅ Admin panel (full featured)
- ✅ Database schema with all tables

### Issues Fixed:
1. ✅ /start command - Now working with professional welcome message
2. ✅ Spin "Coming Soon" - Fixed! Opens web app directly
3. ✅ Wallet crypto support - Full implementation with 6 coins and multiple networks
4. ✅ Ad networks - All 4 networks integrated with proper SDKs and fallback
5. ✅ Database - Complete schema with all required tables

---

## 📝 Quick Start Checklist

- [ ] Upload all files to server
- [ ] Import database.sql
- [ ] Update config.php with database credentials
- [ ] Update config.php with bot token and URL
- [ ] Set bot commands via @BotFather
- [ ] Setup webhook via webhook.php
- [ ] Test /start command
- [ ] Test web app opens correctly
- [ ] Change admin password
- [ ] Configure ad network IDs
- [ ] Test spin wheel
- [ ] Test withdrawal process
- [ ] Add tasks in admin panel
- [ ] Set bot description and profile picture
- [ ] Enable bot and start promoting!

---

## 🎉 You're All Set!

Your Telegram earning bot is now fully functional with:
- Complete bot command handling
- Beautiful web app interface
- Multiple ad networks with rotation
- Comprehensive withdrawal system
- Admin panel for management
- All features working end-to-end

**Start your bot and begin earning!** 🚀
