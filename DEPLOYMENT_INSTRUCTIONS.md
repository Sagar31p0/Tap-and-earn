# Deployment Instructions - Ad Fixes & Watch Ad Task

## Overview / संक्षिप्त विवरण

This deployment includes:
1. ✅ Fixed ad loading for all networks (Adsgram, Monetag, Richads, Adexium)
2. ✅ Added "Watch Ad & Earn 50 Coins" daily task
3. ✅ Improved error handling and debugging

## What Changed / क्या बदला

### Modified Files:
1. **js/ads.js** - Complete ad management rewrite with better SDK handling
2. **js/app.js** - Added special handling for watch ad task

### New Files:
1. **complete_ad_fix.sql** - Complete database fix (MUST RUN)
2. **add_adsgram_task.sql** - Adds watch ad task only
3. **fix_ad_units.sql** - Fixes unit codes only
4. **fix_ad_units.php** - PHP version of fix
5. **AD_TROUBLESHOOTING_HI.md** - Hindi troubleshooting guide
6. **AD_LOADING_FIX.md** - Detailed English documentation
7. **DEPLOYMENT_INSTRUCTIONS.md** - This file

## Deployment Steps / डिप्लॉयमेंट स्टेप्स

### Step 1: Backup Database ⚠️

```bash
mysqldump -u username -p database_name > backup_before_ad_fix.sql
```

### Step 2: Run Database Fix (REQUIRED!)

Choose ONE option:

**Option A - Complete Fix (Recommended):**
```bash
mysql -u username -p database_name < complete_ad_fix.sql
```

**Option B - Manual SQL:**
```sql
-- Fix unit codes
UPDATE ad_units 
SET unit_code = 'ef364bbc-e2b8-434c-8b52-c735de561dc7'
WHERE id = 1 AND network_id = 1;

UPDATE ad_units 
SET unit_code = '10055887'
WHERE id = 2 AND network_id = 2;

-- Add watch ad task
INSERT INTO tasks (title, description, url, reward, icon, type, is_active, sort_order, ad_network)
VALUES ('Watch Ad & Earn 50 Coins', 'Watch ad daily for 50 coins', '#watch-ad', 50.00, 'fas fa-video', 'daily', 1, 1, 'adsgram');

-- Add task ad placement
INSERT INTO ad_placements (placement_key, description, primary_ad_unit_id, secondary_ad_unit_id, tertiary_ad_unit_id, frequency)
VALUES ('task_ad', 'Task Watch Ad Placement', 5, 4, 3, 1);
```

### Step 3: Upload Updated Files

Upload these files to your server:
```
/js/ads.js       (REQUIRED - ad management)
/js/app.js       (REQUIRED - task handling)
```

**Using FTP/SFTP:**
```bash
# Upload files maintaining directory structure
scp js/ads.js user@server:/path/to/project/js/
scp js/app.js user@server:/path/to/project/js/
```

**Using Git (if using version control):**
```bash
git add js/ads.js js/app.js
git commit -m "Fix ad loading and add watch ad task"
git push
```

### Step 4: Verify Database Changes

Run this query to verify:
```sql
-- Check ad units
SELECT id, network_id, unit_code, unit_type 
FROM ad_units 
WHERE id IN (1, 2, 3, 4, 5, 6, 7);

-- Check watch ad task exists
SELECT * FROM tasks WHERE url = '#watch-ad';

-- Check placements
SELECT * FROM ad_placements WHERE placement_key = 'task_ad';
```

**Expected Results:**

Ad Units should show clean codes:
```
1 | 1 | ef364bbc-e2b8-434c-8b52-c735de561dc7 | interstitial
2 | 2 | 10055887                            | interstitial
```

Task should exist:
```
title: "Watch Ad & Earn 50 Coins"
url: "#watch-ad"
reward: 50.00
type: "daily"
```

### Step 5: Clear Cache

**Server-side (if using CDN/caching):**
```bash
# Clear CDN cache for JS files
# Or bump version numbers
```

**Client-side:**
- Users should do hard refresh (Ctrl + Shift + R)
- Or cache will clear automatically after some time

### Step 6: Test

1. **Open app in Telegram**
2. **Check Console (if testing in browser):**
   - Should see: `✅ All ad SDKs loaded`
   - Should see: `🎬 AdManager initialized successfully`

3. **Test Tap & Earn:**
   - Tap 5 times
   - Ad should load (any network)
   - Check console for which network loaded

4. **Test Watch Ad Task:**
   - Go to Tasks section
   - Look for "Watch Ad & Earn 50 Coins"
   - Click "Start"
   - Ad should show directly
   - After completion, should auto-verify
   - Should receive 50 coins

## Rollback Plan / रोलबैक प्लान

If something goes wrong:

**Step 1: Restore Database**
```bash
mysql -u username -p database_name < backup_before_ad_fix.sql
```

**Step 2: Restore Old Files**
```bash
git checkout HEAD~1 js/ads.js js/app.js
```

## Expected Behavior / अपेक्षित व्यवहार

### Tap & Earn Ads:
- Every 5 taps → ad shows
- Tries: Richads → Adsgram → Monetag
- Adsgram currently working as fallback ✓

### Task Section:
- Regular tasks: Show ad → Open URL → Verify
- Watch ad task: Show ad directly → Auto-verify → Give 50 coins

### Console Logs (Success):
```
✅ All ad SDKs loaded
✅ Richads initialized
✅ Adsgram SDK available
✅ Adexium SDK available
✅ Monetag SDK available
🎬 AdManager initialized successfully
```

### When Ad Shows:
```
🎬 AdManager: Requesting ad for placement: task_ad
📞 Fetching ad config for placement: task_ad
📦 Ad config received: {...}
📺 AdManager: Showing adsgram ad...
🎬 Attempting to show Adsgram ad: {...}
📝 Cleaned Adsgram block ID: 16416
📺 Adsgram controller initialized for block: 16416
✅ Adsgram ad completed
```

## Troubleshooting / समस्या निवारण

### Issue: Ads still not showing

**Check:**
1. Database fix was run? (Step 2)
2. Files uploaded? (Step 3)
3. Cache cleared? (Step 5)

**Debug:**
```javascript
// In browser console:
console.log('SDK Status:', {
    adsgram: !!window.Adsgram,
    adexium: !!window.AdexiumWidget,
    monetag: typeof show_10055887 === 'function',
    richads: !!window.TelegramAdsController
});
```

### Issue: Watch ad task not showing

**Check:**
```sql
SELECT * FROM tasks WHERE url = '#watch-ad' AND is_active = 1;
```

If empty, run:
```sql
INSERT INTO tasks (title, description, url, reward, icon, type, is_active, sort_order, ad_network)
VALUES ('Watch Ad & Earn 50 Coins', 'Watch ad daily for 50 coins', '#watch-ad', 50.00, 'fas fa-video', 'daily', 1, 1, 'adsgram');
```

### Issue: Task shows but doesn't work

**Check console for errors:**
- If "ad_unit not found" → Run database fix
- If "SDK not loaded" → Check index.html has SDK scripts
- If "placement not found" → Add task_ad placement

## Performance Impact / प्रदर्शन प्रभाव

- **Positive:** Better error handling, fewer failed ad loads
- **Positive:** Faster SDK initialization with timeout
- **Neutral:** Similar file size (slightly larger for better logging)
- **No breaking changes:** Backwards compatible

## Security Considerations / सुरक्षा विचार

- ✅ No new external dependencies
- ✅ Input validation maintained
- ✅ SQL injection protected (prepared statements)
- ✅ XSS protection maintained

## Support / सहायता

For issues after deployment:

1. **Check browser console** (F12 → Console tab)
2. **Check error logs** on server
3. **Verify database changes** (queries above)
4. **See AD_TROUBLESHOOTING_HI.md** for detailed debugging

## Files Reference / फ़ाइल संदर्भ

### Must Deploy:
- ✅ `js/ads.js` - Core ad functionality
- ✅ `js/app.js` - Task handling

### Must Run:
- ✅ `complete_ad_fix.sql` - Database updates

### Optional (Documentation):
- 📖 `AD_TROUBLESHOOTING_HI.md` - Hindi troubleshooting
- 📖 `AD_LOADING_FIX.md` - English documentation
- 📖 `DEPLOYMENT_INSTRUCTIONS.md` - This file

### Alternative Scripts (use if needed):
- `fix_ad_units.sql` - Only fixes unit codes
- `add_adsgram_task.sql` - Only adds watch ad task
- `fix_ad_units.php` - PHP version of fix

## Verification Checklist ✅

After deployment, verify:

- [ ] Database backup taken
- [ ] SQL script executed successfully
- [ ] Files uploaded to server
- [ ] Watch ad task appears in Tasks section
- [ ] Tap & earn ads working
- [ ] Watch ad task gives 50 coins
- [ ] Console shows SDK loaded messages
- [ ] No JavaScript errors in console

## Timeline / समयरेखा

- **Preparation:** 5 minutes (backup, upload files)
- **Database Update:** 1 minute (run SQL)
- **Cache Clear:** Automatic or 5 minutes
- **Testing:** 10 minutes (test all features)
- **Total:** ~20-25 minutes

## Contact

If issues persist after following all steps:
1. Check console logs screenshot
2. Run diagnostic queries
3. Review AD_TROUBLESHOOTING_HI.md

---

**Status:** Ready for Deployment
**Version:** 1.0
**Date:** 2025-10-29
**Breaking Changes:** None
**Database Changes:** Yes (must run SQL)
