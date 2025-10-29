-- Fix Ad Unit Codes
-- This script updates the ad_units table to have clean unit codes instead of full JavaScript

-- Fix Adexium Interstitial (Unit 1)
-- Extract the widget ID from the JavaScript code
UPDATE ad_units 
SET unit_code = 'ef364bbc-e2b8-434c-8b52-c735de561dc7'
WHERE id = 1 AND network_id = 1;

-- Fix Monetag Interstitial (Unit 2)  
-- Monetag uses zone ID 10055887 (already in the SDK script tag)
-- For interstitial, we just need to reference the function
UPDATE ad_units 
SET unit_code = '10055887'
WHERE id = 2 AND network_id = 2;

-- Verify the updates
SELECT id, network_id, name, unit_code, unit_type 
FROM ad_units 
WHERE id IN (1, 2);
