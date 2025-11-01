-- Fix shortlink ad placement configuration
-- The shortlink placement exists but has no ad units configured
-- This migration links the existing shortlink ad units to the placement

UPDATE ad_placements 
SET 
    primary_ad_unit_id = 3,    -- Reward Adsgram (16414)
    secondary_ad_unit_id = 9,  -- banner richads (#375142)
    tertiary_ad_unit_id = NULL
WHERE placement_key = 'shortlink' AND id = 5;

-- Verify the update
SELECT 
    ap.id,
    ap.placement_key,
    ap.description,
    ap.primary_ad_unit_id,
    ap.secondary_ad_unit_id,
    ap.tertiary_ad_unit_id,
    au1.name as primary_ad_name,
    au2.name as secondary_ad_name
FROM ad_placements ap
LEFT JOIN ad_units au1 ON ap.primary_ad_unit_id = au1.id
LEFT JOIN ad_units au2 ON ap.secondary_ad_unit_id = au2.id
WHERE ap.placement_key = 'shortlink';
