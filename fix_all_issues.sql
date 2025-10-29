-- ============================================
-- FIX ALL ADMIN PANEL CONNECTIVITY ISSUES
-- Run this SQL file in phpMyAdmin
-- ============================================

USE u988479389_tery;

-- Fix 1: Update Tap Ad Frequency from 5 to 2 taps
UPDATE settings 
SET setting_value = '2', updated_at = NOW() 
WHERE setting_key = 'tap_ad_frequency';

-- Fix 2: Update Spin Daily Limit from 10 to 500
UPDATE settings 
SET setting_value = '500', updated_at = NOW() 
WHERE setting_key = 'spin_daily_limit';

-- Fix 3: Update Tap Placement Frequency to 2
UPDATE ad_placements 
SET frequency = 2 
WHERE placement_key = 'tap';

-- Fix 4: Create Special "Watch Ad" Task for Adsgram
-- Check if task already exists, if not create it
INSERT INTO tasks (title, description, url, reward, icon, type, is_active, sort_order, ad_network, created_at)
SELECT * FROM (SELECT 
    'Watch Ad & Earn' as title,
    'Watch a video advertisement and earn coins instantly' as description,
    '#watch-ad' as url,
    50.00 as reward,
    'fas fa-video' as icon,
    'daily' as type,
    1 as is_active,
    1 as sort_order,
    'adsgram' as ad_network,
    NOW() as created_at
) AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM tasks WHERE url = '#watch-ad' AND ad_network = 'adsgram'
) LIMIT 1;

-- Fix 5: Ensure all ad networks are enabled
UPDATE ad_networks 
SET is_enabled = 1 
WHERE name IN ('adexium', 'monetag', 'adsgram', 'richads');

-- Fix 6: Ensure all critical ad units are active
UPDATE ad_units 
SET is_active = 1 
WHERE id IN (1, 2, 3, 4, 5, 6, 7);

-- Fix 7: Reset any stuck user spins (optional - only if users are facing issues)
-- UPDATE user_spins SET spins_today = 0 WHERE last_reset < CURDATE();

-- ============================================
-- VERIFICATION QUERIES
-- Run these to verify the fixes
-- ============================================

-- Verify Settings
SELECT 
    setting_key, 
    setting_value, 
    updated_at 
FROM settings 
WHERE setting_key IN ('tap_ad_frequency', 'spin_daily_limit', 'spin_interval_minutes')
ORDER BY setting_key;

-- Verify Ad Placements
SELECT 
    ap.placement_key,
    ap.frequency,
    au1.name as primary_unit,
    an1.name as primary_network,
    au2.name as secondary_unit,
    an2.name as secondary_network
FROM ad_placements ap
LEFT JOIN ad_units au1 ON ap.primary_ad_unit_id = au1.id
LEFT JOIN ad_networks an1 ON au1.network_id = an1.id
LEFT JOIN ad_units au2 ON ap.secondary_ad_unit_id = au2.id
LEFT JOIN ad_networks an2 ON au2.network_id = an2.id
ORDER BY ap.placement_key;

-- Verify Active Ad Units
SELECT 
    au.id,
    au.name,
    an.name as network,
    au.unit_type,
    au.placement_key,
    au.is_active
FROM ad_units au
JOIN ad_networks an ON au.network_id = an.id
ORDER BY au.placement_key, au.id;

-- Verify Tasks
SELECT 
    id,
    title,
    url,
    reward,
    type,
    ad_network,
    is_active
FROM tasks
ORDER BY sort_order, id;

-- ============================================
-- SUMMARY OF FIXES
-- ============================================
-- 1. ✅ Tap ad frequency: 5 → 2 taps
-- 2. ✅ Spin daily limit: 10 → 500 spins
-- 3. ✅ Tap placement frequency: 5 → 2
-- 4. ✅ Created Watch Ad task for Adsgram
-- 5. ✅ Enabled all ad networks
-- 6. ✅ Activated all ad units
-- ============================================
