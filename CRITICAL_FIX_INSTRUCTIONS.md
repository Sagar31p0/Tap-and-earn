# CRITICAL FIX INSTRUCTIONS

## Issues Identified:

### 1. Tap & Earn: Getting 5 Points Instead of 1
**Root Cause**: OpCache is caching old setting values. Database has `tap_reward = '1'` but OpCache is serving cached value of `5`.

### 2. Shortener: Ads Not Displaying
**Root Cause**: Ad placement configuration needs verification and proper cache clearing.

---

## IMMEDIATE FIX STEPS:

### Step 1: Clear All Cache (DO THIS FIRST!)

1. **Login to Admin Panel**: Go to `/admin/` and login
2. **Go to Settings Page**: Click on "Settings" in admin menu
3. **Scroll to Cache Management Section** at the bottom
4. **Check the box**: "Also clear user sessions"
5. **Click "Force Clear Cache"** button
6. **Wait for confirmation** message

### Step 2: Verify Settings

1. **Still in Settings Page**, verify these values:
   - **Coins Per Tap**: Should be `1` (not 5)
   - **Energy Per Tap**: Check it's your desired value
   - **Tap Ad Frequency**: Check it's set correctly

2. **If Coins Per Tap shows 5**: 
   - Change it to `1`
   - Click "Save All Settings"
   - Clear cache again (Step 1)

### Step 3: Verify Shortener Ad Configuration

1. **Go to Ads Management**: Click "Ads" in admin menu
2. **Check Ad Placements tab**
3. **Find "shortlink" placement**:
   - Should have at least one ad unit assigned (Primary)
   - Ad unit should be ACTIVE (green checkmark)
   - Ad network should be ENABLED

4. **If shortlink placement is missing or not configured**:
   - Go to Ad Placements
   - Find or create "shortlink" placement
   - Assign an active ad unit to it
   - Save changes

### Step 4: Restart PHP-FPM (If Possible)

If you have server access:
```bash
# For most Linux servers with PHP-FPM
sudo systemctl restart php-fpm

# Or for cPanel/WHM
/scripts/restartsrv_httpd

# Or for Nginx
sudo systemctl restart php8.x-fpm nginx
```

### Step 5: Test Both Features

1. **Test Tap & Earn**:
   - Open the Telegram bot
   - Go to Tap & Earn section
   - Tap once and check points earned
   - Should show +1 point (not +5)

2. **Test Shortener**:
   - Open any shortlink from admin panel
   - Should see ad loading interface
   - Ad should display before redirect

---

## If Issues Persist:

### For Tap Points Still Showing 5:

Run this SQL query directly in phpMyAdmin or database:

```sql
-- Verify current value
SELECT * FROM settings WHERE setting_key = 'tap_reward';

-- Force update
UPDATE settings SET setting_value = '1', updated_at = NOW() WHERE setting_key = 'tap_reward';

-- Verify update
SELECT * FROM settings WHERE setting_key = 'tap_reward';
```

Then clear cache again and restart web server.

### For Shortener Ads Still Not Showing:

Run this SQL to verify placement:

```sql
-- Check shortlink placement
SELECT 
    ap.*, 
    au.name as ad_unit_name,
    au.is_active,
    an.name as network_name,
    an.is_enabled
FROM ad_placements ap
LEFT JOIN ad_units au ON ap.primary_ad_unit_id = au.id
LEFT JOIN ad_networks an ON au.network_id = an.id
WHERE ap.placement_key = 'shortlink';
```

If no results or ad_unit_name is NULL, run:

```sql
-- Assign an active ad unit to shortlink placement
UPDATE ad_placements 
SET primary_ad_unit_id = (
    SELECT id FROM ad_units 
    WHERE is_active = 1 
    LIMIT 1
)
WHERE placement_key = 'shortlink';
```

---

## Alternative: Manual Cache Clear

If admin panel cache clear doesn't work, you can manually clear PHP cache:

### Method 1: Create a cache clear script

Create file: `/public_html/clear_cache_now.php`

```php
<?php
// Clear OpCache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OpCache cleared<br>";
}

// Clear APCu
if (function_exists('apcu_clear_cache')) {
    apcu_clear_cache();
    echo "APCu cleared<br>";
}

// Clear stat cache
clearstatcache(true);
echo "Stat cache cleared<br>";

echo "<br>All caches cleared! Delete this file now for security.";
?>
```

Visit this file in browser, then **DELETE IT IMMEDIATELY** for security.

### Method 2: .htaccess method

Add to `.htaccess`:

```apache
# Disable OpCache for this site (temporary)
php_flag opcache.enable Off
```

**Remove this line after testing!**

---

## Expected Results After Fix:

? Tap & Earn shows correct points (1 per tap, not 5)
? Shortener links show ad before redirecting
? Settings in admin panel match actual behavior
? No caching issues

---

## Need Help?

If issues still persist after all steps:

1. Check browser console for JavaScript errors (F12 ? Console tab)
2. Check server error logs
3. Verify PHP version supports all functions
4. Contact hosting support to restart PHP-FPM

---

**IMPORTANT**: After fixing, always clear cache when changing settings!
