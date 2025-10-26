# 🚀 Quick Installation Guide

## Prerequisites
- Shared hosting with PHP 7.4+ and MySQL
- Domain: https://reqa.antipiracyforce.org/test
- Database: u988479389_tery
- Telegram Bot: @kuchpvildybot

## ⚡ 5-Minute Setup

### Step 1: Upload Files (2 minutes)
1. Upload all files to `/test` directory on your server
2. Ensure file structure is intact:
   ```
   /test/
   ├── index.html
   ├── config.php
   ├── database.sql
   ├── api/
   ├── admin/
   ├── css/
   └── js/
   ```

### Step 2: Import Database (1 minute)
1. Open phpMyAdmin
2. Select database: `u988479389_tery`
3. Click "Import"
4. Choose `database.sql` file
5. Click "Go"
6. Wait for "Import has been successfully finished"

### Step 3: Configure (1 minute)
Edit `config.php`:

```php
// Line 4: Add your database password
define('DB_PASS', 'YOUR_DATABASE_PASSWORD_HERE');

// Line 8: Add your bot token from @BotFather
define('BOT_TOKEN', 'YOUR_BOT_TOKEN_HERE');
```

### Step 4: Set Bot Menu (1 minute)
1. Open Telegram
2. Search for `@BotFather`
3. Send: `/setmenubutton`
4. Select your bot: `@kuchpvildybot`
5. Send button text: `Open App`
6. Send URL: `https://reqa.antipiracyforce.org/test/`

### Step 5: Test (30 seconds)
1. Open `@kuchpvildybot` in Telegram
2. Click "Menu" or send `/start`
3. App should load successfully ✅

## 🔐 First Login

### Access Admin Panel
- URL: `https://reqa.antipiracyforce.org/test/admin/`
- Username: `admin`
- Password: `admin123`

⚠️ **CHANGE PASSWORD IMMEDIATELY!**

## ✅ Post-Installation Checklist

### Required Settings (5 minutes)
1. **Change Admin Password**
   - Go to Admin Panel
   - Settings → Change password

2. **Configure Basic Settings**
   - Bot name
   - Welcome message
   - Tap reward amount
   - Energy settings

3. **Add First Task**
   - Admin → Tasks → Add New Task
   - Example:
     - Title: "Join our Telegram Channel"
     - URL: https://t.me/yourchannel
     - Reward: 50 coins

4. **Add First Game**
   - Admin → Games → Add New Game
   - Example:
     - Name: "Memory Game"
     - URL: https://example.com/game
     - Reward: 10 coins

5. **Configure Spin Wheel**
   - Admin → Spin Wheel
   - Verify probabilities (should total 100%)
   - Set daily limit

### Optional Settings
- [ ] Add more tasks
- [ ] Add more games
- [ ] Configure ad networks
- [ ] Set up payment methods
- [ ] Customize rewards
- [ ] Set minimum withdrawal

## 🎯 Testing Everything

### Test User Features
1. **Tap to Earn**
   - Tap the coin icon
   - Verify energy decreases
   - Check coins increase

2. **Tasks**
   - Open Tasks screen
   - Start a task
   - Complete and verify

3. **Spin Wheel**
   - Try spinning
   - Verify rewards work

4. **Referral**
   - Copy referral link
   - Test with another account

### Test Admin Features
1. **Dashboard**
   - Check if stats update

2. **User Management**
   - Find test user
   - Adjust coins
   - Verify in app

3. **Withdrawals**
   - Request withdrawal in app
   - Check in admin panel
   - Test approve/reject

## 🔧 Troubleshooting

### App won't load
- Check config.php has correct DB password
- Verify all files uploaded
- Check file permissions (755 for folders)

### Can't login to admin
- Database imported correctly?
- Try clearing browser cache
- Check error.log file

### Tap not working
- Open browser console (F12)
- Check for JavaScript errors
- Verify API endpoints accessible

### Database connection error
- Double-check password in config.php
- Verify database name is correct
- Contact hosting support if persists

## 📱 Ad Networks Setup (Optional)

### Adexium
1. Sign up at Adexium
2. Create ad units
3. Copy Widget IDs
4. Add in Admin → Ads Management

### Monetag
1. Get Zone ID from Monetag
2. Add in Admin → Ads Management
3. Test ad display

### Adsgram
1. Get Block IDs
2. Add in Admin → Ads Management
3. Configure placements

### Richads
1. Already pre-configured in code
2. Add your Publisher ID
3. Add unit IDs in admin

## 🎉 You're Done!

Your Telegram Mini Bot is now ready! 🚀

### Next Steps:
1. Promote your bot
2. Add more content (tasks/games)
3. Monitor user growth
4. Process withdrawals
5. Optimize settings based on usage

### Support Resources:
- README.md - Complete documentation
- Admin panel - Built-in help
- Error logs - /workspace/error.log
- Browser console - JavaScript errors

---

## 📞 Need Help?

1. Check README.md for detailed docs
2. Review error logs
3. Test with different browsers
4. Verify hosting requirements

**Installation Time:** ~5 minutes
**Difficulty:** Easy ⭐
**PHP Required:** 7.4+
**MySQL Required:** 5.7+

Good luck! 🎮💰
