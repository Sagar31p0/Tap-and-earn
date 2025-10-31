-- Update Ad Units from adsunit.md
-- This script will update the ad units with the correct IDs from the ad networks

-- First, clear existing ad units (keep the networks)
DELETE FROM ad_units;

-- Reset auto-increment
ALTER TABLE ad_units AUTO_INCREMENT = 1;

-- Add Adexium Ad Units
INSERT INTO ad_units (network_id, name, unit_code, unit_type, placement_key, is_active) VALUES
(1, 'Adexium Interstitial', '8391da33-7acd-47a9-8d83-f7b4bf4956b1', 'interstitial', 'tap', 1);

-- Add Monetag Ad Units
INSERT INTO ad_units (network_id, name, unit_code, unit_type, placement_key, is_active) VALUES
(2, 'Monetag Rewarded Interstitial', '10113890', 'rewarded', 'spin', 1),
(2, 'Monetag In-App Interstitial', '10113890', 'interstitial', 'game_preroll', 1),
(2, 'Monetag Rewarded Popup', '10113890', 'rewarded', 'wallet', 1);

-- Add Richads Ad Units
INSERT INTO ad_units (network_id, name, unit_code, unit_type, placement_key, is_active) VALUES
(4, 'Richads Push-style', '375934', 'native', 'tap', 1),
(4, 'Richads Embedded Banner', '375935', 'banner', 'shortlink', 1),
(4, 'Richads Interstitial Banner', '375936', 'interstitial', 'game_preroll', 1),
(4, 'Richads Interstitial Video', '375937', 'interstitial', 'spin', 1),
(4, 'Richads Playable Ads', '375938', 'native', 'task', 1);

-- Add Adsgram Ad Units
INSERT INTO ad_units (network_id, name, unit_code, unit_type, placement_key, is_active) VALUES
(3, 'Adsgram Task', 'task-16619', 'native', 'task', 1),
(3, 'Adsgram Interstitial', 'int-16618', 'interstitial', 'tap', 1),
(3, 'Adsgram Reward', '16617', 'rewarded', 'wallet', 1);

-- Update Ad Placements with new unit IDs
-- Note: IDs will be sequential from 1-12 based on insertion order above

UPDATE ad_placements SET 
    primary_ad_unit_id = 1,    -- Adexium Interstitial
    secondary_ad_unit_id = 11,  -- Adsgram Interstitial  
    tertiary_ad_unit_id = 5,    -- Richads Push-style
    frequency = 5
WHERE placement_key = 'tap';

UPDATE ad_placements SET 
    primary_ad_unit_id = 8,    -- Richads Interstitial Video
    secondary_ad_unit_id = 2,  -- Monetag Rewarded Interstitial
    tertiary_ad_unit_id = NULL,
    frequency = 1
WHERE placement_key = 'spin';

UPDATE ad_placements SET 
    primary_ad_unit_id = 7,    -- Richads Interstitial Banner
    secondary_ad_unit_id = 3,  -- Monetag In-App Interstitial
    tertiary_ad_unit_id = NULL,
    frequency = 1
WHERE placement_key = 'game_preroll';

UPDATE ad_placements SET 
    primary_ad_unit_id = 10,   -- Adsgram Task
    secondary_ad_unit_id = 9,  -- Richads Playable Ads
    tertiary_ad_unit_id = NULL,
    frequency = 1
WHERE placement_key = 'task';

UPDATE ad_placements SET 
    primary_ad_unit_id = 6,    -- Richads Embedded Banner
    secondary_ad_unit_id = NULL,
    tertiary_ad_unit_id = NULL,
    frequency = 1
WHERE placement_key = 'shortlink';

UPDATE ad_placements SET 
    primary_ad_unit_id = 4,    -- Monetag Rewarded Popup
    secondary_ad_unit_id = 12, -- Adsgram Reward
    tertiary_ad_unit_id = NULL,
    frequency = 1
WHERE placement_key = 'wallet';
