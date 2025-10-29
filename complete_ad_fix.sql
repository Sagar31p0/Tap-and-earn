-- Complete Ad System Fix
-- This script fixes all ad-related issues

-- =============================================
-- 1. FIX AD UNIT CODES
-- =============================================

-- Fix Adexium unit (remove JavaScript code, keep only widget ID)
UPDATE ad_units 
SET unit_code = 'ef364bbc-e2b8-434c-8b52-c735de561dc7'
WHERE id = 1 AND network_id = 1;

-- Fix Monetag unit (keep only zone ID)
UPDATE ad_units 
SET unit_code = '10055887'
WHERE id = 2 AND network_id = 2;

-- =============================================
-- 2. ADD/UPDATE AD PLACEMENTS
-- =============================================

-- Update existing placements or add if missing
INSERT INTO ad_placements (id, placement_key, description, primary_ad_unit_id, secondary_ad_unit_id, tertiary_ad_unit_id, frequency)
VALUES 
    (7, 'task_ad', 'Task Watch Ad Placement', 5, 4, 3, 1),
    (8, 'energy_recharge', 'Energy Recharge Ad', 4, 3, 2, 1)
ON DUPLICATE KEY UPDATE
    description = VALUES(description),
    primary_ad_unit_id = VALUES(primary_ad_unit_id),
    secondary_ad_unit_id = VALUES(secondary_ad_unit_id),
    tertiary_ad_unit_id = VALUES(tertiary_ad_unit_id);

-- =============================================
-- 3. ADD WATCH AD TASK
-- =============================================

INSERT INTO tasks (
    title, 
    description, 
    url, 
    reward, 
    icon, 
    type, 
    is_active, 
    sort_order, 
    ad_network
) VALUES (
    'Watch Ad & Earn 50 Coins',
    'Watch a short advertisement and earn 50 coins instantly! This task resets daily.',
    '#watch-ad',
    50.00,
    'fas fa-video',
    'daily',
    1,
    1,
    'adsgram'
)
ON DUPLICATE KEY UPDATE
    description = VALUES(description),
    reward = VALUES(reward);

-- =============================================
-- 4. VERIFY CONFIGURATIONS
-- =============================================

-- Show all ad networks
SELECT 
    'AD NETWORKS' as info,
    id, 
    name, 
    is_enabled 
FROM ad_networks;

-- Show all ad units with their codes
SELECT 
    'AD UNITS' as info,
    au.id, 
    an.name as network, 
    au.name as unit_name, 
    au.unit_code, 
    au.unit_type,
    au.is_active
FROM ad_units au
JOIN ad_networks an ON au.network_id = an.id;

-- Show all placements
SELECT 
    'AD PLACEMENTS' as info,
    p.id,
    p.placement_key,
    p.description,
    p.primary_ad_unit_id,
    p.secondary_ad_unit_id,
    p.tertiary_ad_unit_id,
    p.frequency
FROM ad_placements p;

-- Show the watch ad task
SELECT 
    'WATCH AD TASK' as info,
    id,
    title,
    url,
    reward,
    type,
    is_active,
    ad_network
FROM tasks 
WHERE url = '#watch-ad';
