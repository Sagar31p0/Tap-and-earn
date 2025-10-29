-- Add Adsgram Watch Ad Task
-- This task gives 50 points for watching an Adsgram ad daily

INSERT INTO `tasks` (
    `title`, 
    `description`, 
    `url`, 
    `reward`, 
    `icon`, 
    `type`, 
    `is_active`, 
    `sort_order`, 
    `ad_network`
) VALUES (
    'Watch Ad & Earn',
    'Watch a short advertisement and earn 50 coins! Available daily.',
    '#watch-ad',  -- Special URL indicator for ad watching
    50.00,
    'fas fa-video',
    'daily',
    1,
    1,
    'adsgram'
);

-- Verify the task was created
SELECT * FROM tasks WHERE title = 'Watch Ad & Earn';
