-- Migration: Fix Shortlink Ad Unit IDs
-- Date: 2025-11-01
-- Description: Update short_links to use correct ad_unit_id from ad_placements

-- Get the primary ad unit for shortlink placement (should be Monetag - ID 3)
-- If links have ad_unit_id set, update them to use the shortlink placement's primary unit

-- First, let's see what the shortlink placement uses
-- From database.sql line 205: shortlink placement has primary_ad_unit_id = 3 (Monetag)

-- Update all short_links that have ad_unit_id = 12 (Adsgram Reward) to use 3 (Monetag)
-- This ensures shortener uses the correct ad placement
UPDATE short_links 
SET ad_unit_id = 3 
WHERE ad_unit_id = 12 
AND mode = 'direct_ad';

-- Alternative: Set ad_unit_id to NULL so it uses the placement configuration
-- UPDATE short_links 
-- SET ad_unit_id = NULL 
-- WHERE mode = 'direct_ad';

-- Note: If ad_unit_id is NULL, the s.php and api/ads.php will use the 
-- ad_placements table to determine which ad to show (shortlink placement)
