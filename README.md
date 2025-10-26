# Telegram Mini Bot - Complete Installation Guide

## 📋 Overview
This is a complete Telegram Mini App with an admin panel featuring tap-to-earn, spin wheel, tasks, games, referrals, wallet system, and multi-network ad integration.

## 🚀 Features

### User Features
- ✅ Tap & Earn with energy system
- ✅ Spin the Wheel with rewards
- ✅ Tasks (One-time & Daily)
- ✅ Play & Earn Games
- ✅ Referral System
- ✅ Wallet & Withdrawals
- ✅ Leaderboard
- ✅ Multi-language support
- ✅ Dark/Light mode

### Admin Features
- ✅ Dashboard with KPIs
- ✅ User Management
- ✅ Task Management
- ✅ Game Management
- ✅ Spin Wheel Configuration
- ✅ Ad Network Management (Adexium, Monetag, Adsgram, Richads)
- ✅ Withdrawal Management
- ✅ Broadcast System
- ✅ Global Settings
- ✅ Analytics

## 📦 Installation

### Step 1: Upload Files
Upload all files to your server at: `https://reqa.antipiracyforce.org/test/`

### Step 2: Database Setup
1. Log in to your hosting panel (cPanel/Plesk)
2. Go to phpMyAdmin
3. Select database: `u988479389_tery`
4. Import `database.sql` file
5. Database will be automatically set up with all tables

### Step 3: Configuration
Edit `config.php` and update:

```php
// Update with your actual database password
define('DB_PASS', 'your_actual_password');

// Update with your Telegram Bot Token
define('BOT_TOKEN', 'YOUR_BOT_TOKEN_FROM_@BOTFATHER');
```

### Step 4: Set Telegram Bot
1. Create a bot using [@BotFather](https://t.me/botfather)
2. Get your bot token
3. Set the bot's Mini App URL:
   ```
   /setmenubutton
   Select your bot: @kuchpvildybot
   URL: https://reqa.antipiracyforce.org/test/
   ```

### Step 5: File Permissions
Make sure these directories are writable (chmod 755):
- `/workspace` (root directory)
- `/workspace/uploads` (create this folder for future file uploads)

## 🔐 Admin Access

### Default Login
- URL: `https://reqa.antipiracyforce.org/test/admin/login.php`
- Username: `admin`
- Password: `admin123`

**⚠️ IMPORTANT:** Change the admin password immediately after first login!

### Change Admin Password
Run this in phpMyAdmin (replace `new_password` with your desired password):

```sql
UPDATE admin_users 
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE username = 'admin';
```

Or use this PHP script to generate a new password hash:
```php
<?php
echo password_hash('your_new_password', PASSWORD_DEFAULT);
?>
```

## 🎮 Testing the App

1. Open Telegram
2. Search for your bot: `@kuchpvildybot`
3. Start the bot
4. Click "Menu" button or type `/start`
5. The Mini App should open

## 📱 Ad Network Setup

### 1. Adexium
1. Go to Admin Panel → Ads Management
2. Add Adexium ad units
3. Enter Widget IDs from your Adexium account

### 2. Monetag
1. Sign up at Monetag
2. Get your Zone ID
3. Add it in Admin → Ads Management
4. Update the SDK script in `index.html` if needed

### 3. Adsgram
1. Get block IDs from Adsgram
2. Add units in Admin Panel:
   - Task ad: `task-16416`
   - Interstitial: `int-16415`
   - Reward: `16414`

### 4. Richads
1. Publisher ID: `820238`
2. App ID: `4130`
3. Add unit IDs in Admin Panel:
   - `#375144` - Interstitial video
   - `#375143` - Interstitial banner
   - `#375141` - Push-style
   - `#375142` - Embedded banner

## 🎯 Initial Setup Checklist

- [ ] Database imported successfully
- [ ] config.php updated with correct credentials
- [ ] Admin panel accessible
- [ ] Admin password changed
- [ ] Bot token configured
- [ ] Mini App URL set in BotFather
- [ ] Test user registration works
- [ ] Tap feature works
- [ ] At least one task created
- [ ] At least one game added
- [ ] Spin wheel configured
- [ ] Payment methods added
- [ ] Ad networks configured

## 📝 Creating Content

### Add Tasks
1. Go to Admin → Tasks
2. Click "Add New Task"
3. Fill in:
   - Title
   - Description
   - URL (external link)
   - Reward (coins)
   - Icon (Font Awesome class)
   - Type (one_time or daily)

### Add Games
1. Go to Admin → Games
2. Click "Add New Game"
3. Fill in:
   - Name
   - Game URL
   - Reward
   - Play limit type
   - Ad network (optional)

### Configure Spin Wheel
1. Go to Admin → Spin Wheel
2. Edit reward blocks
3. Set probabilities (must total 100%)
4. Enable/disable double reward option

## 🔧 Troubleshooting

### Issue: Can't access admin panel
- Check file permissions
- Verify database connection in config.php
- Clear browser cache

### Issue: Telegram app not opening
- Verify bot token in config.php
- Check Mini App URL in BotFather
- Ensure all files are uploaded correctly

### Issue: Energy not recharging
- Check settings in Admin → Settings
- Verify cron jobs (if needed for background tasks)
- Check energy_recharge_interval setting

### Issue: Ads not showing
- Verify ad network credentials
- Check ad units are configured
- Check browser console for errors
- Ensure ad SDKs are loading correctly

## 🌐 File Structure

```
/workspace/
├── index.html              # Main app
├── config.php              # Configuration
├── database.sql            # Database schema
├── README.md              # This file
├── api/                   # API endpoints
│   ├── auth.php
│   ├── tap.php
│   ├── spin.php
│   ├── tasks.php
│   ├── games.php
│   ├── referrals.php
│   ├── wallet.php
│   ├── ads.php
│   └── leaderboard.php
├── admin/                 # Admin panel
│   ├── index.php
│   ├── login.php
│   ├── users.php
│   ├── tasks.php
│   ├── games.php
│   ├── spin.php
│   ├── ads.php
│   ├── withdrawals.php
│   ├── broadcast.php
│   ├── settings.php
│   └── logout.php
├── css/
│   └── style.css
└── js/
    ├── app.js
    └── ads.js
```

## 📊 Database Tables

- `users` - User accounts
- `user_stats` - User statistics
- `user_tasks` - Task completion tracking
- `user_games` - Game play tracking
- `user_spins` - Spin tracking
- `tasks` - Task definitions
- `games` - Game definitions
- `spin_config` - Spin wheel configuration
- `referrals` - Referral tracking
- `withdrawals` - Withdrawal requests
- `ad_networks` - Ad network configurations
- `ad_units` - Ad unit definitions
- `ad_placements` - Ad placement mappings
- `ad_logs` - Ad event tracking
- `transactions` - All coin transactions
- `settings` - Global settings
- `admin_users` - Admin accounts
- `broadcasts` - Broadcast messages
- `payment_methods` - Payment method definitions
- `short_links` - URL shortener data

## 🔒 Security Notes

1. **Change default admin password immediately**
2. Keep your database credentials secure
3. Don't share your bot token
4. Regularly backup your database
5. Use HTTPS (your domain already has it)
6. Keep PHP updated on your server

## 📞 Support

For issues or questions:
1. Check this README first
2. Check the troubleshooting section
3. Review error logs: `/workspace/error.log`
4. Check browser console for JavaScript errors

## 🎉 Launch Checklist

Before going live:
- [ ] Test all features thoroughly
- [ ] Add initial tasks and games
- [ ] Configure all ad networks
- [ ] Set appropriate coin rewards
- [ ] Test withdrawal system
- [ ] Set minimum withdrawal amount
- [ ] Configure payment methods
- [ ] Test referral system
- [ ] Review all settings
- [ ] Backup database
- [ ] Monitor error logs
- [ ] Test on multiple devices

## 📈 Monitoring

Regular checks:
- User growth
- Active users
- Coin distribution
- Withdrawal requests
- Ad performance
- Error logs
- Database size

---

**Version:** 1.0
**Last Updated:** 2025-10-26
**Database:** u988479389_tery
**Domain:** https://reqa.antipiracyforce.org/test
**Bot:** @kuchpvildybot

Good luck with your Telegram Mini Bot! 🚀
