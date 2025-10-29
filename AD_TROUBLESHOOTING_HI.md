# Ad Networks Troubleshooting Guide / विज्ञापन समस्या निवारण गाइड

## समस्या / Current Issue
- ✅ **Adsgram** - Kaam kar raha hai (Working)
- ❌ **Monetag** - Nahi dikh raha (Not showing)
- ❌ **Richads** - Nahi dikh raha (Not showing)
- ❌ **Adexium** - Nahi dikh raha (Not showing)

## क्यों नहीं काम कर रहे? / Why Not Working?

### Primary Reason:
Database में **unit_code** field में galat data hai. Full JavaScript code store hai instead of just ID.

### Example (Current WRONG format):
```
unit_code = "document.addEventListener('DOMContentLoaded', () => { ... })"  ❌
```

### Example (Correct format):
```
unit_code = "ef364bbc-e2b8-434c-8b52-c735de561dc7"  ✅
```

## Fix Steps / ठीक करने के स्टेप्स

### Step 1: Database Fix (सबसे ज़रूरी!)

**Option A - SQL Script चलाएं:**
```bash
# Server par login karke:
mysql -u username -p database_name < complete_ad_fix.sql
```

**Option B - phpMyAdmin में:**
```sql
-- Copy paste karke run karein:
UPDATE ad_units 
SET unit_code = 'ef364bbc-e2b8-434c-8b52-c735de561dc7'
WHERE id = 1 AND network_id = 1;

UPDATE ad_units 
SET unit_code = '10055887'
WHERE id = 2 AND network_id = 2;
```

**Option C - PHP Script:**
```bash
php fix_ad_units.php
```

### Step 2: Verify Database

SQL run करने के बाद verify करें:
```sql
SELECT id, network_id, unit_code, unit_type 
FROM ad_units 
WHERE id IN (1, 2, 3, 4, 5, 6, 7);
```

**Expected Output:**
```
ID | Network | Unit Code                           | Type
---+---------+-------------------------------------+-------------
1  | 1       | ef364bbc-e2b8-434c-8b52-c735de561dc7| interstitial
2  | 2       | 10055887                            | interstitial
3  | 3       | 16414                               | rewarded
4  | 3       | int-16415                           | interstitial
5  | 3       | task-16416                          | native
6  | 4       | #375144                             | rewarded
7  | 4       | #375143                             | interstitial
```

### Step 3: Add Watch Ad Task

```sql
INSERT INTO tasks (title, description, url, reward, icon, type, is_active, sort_order, ad_network)
VALUES ('Watch Ad & Earn 50 Coins', 'Watch ad daily for 50 coins', '#watch-ad', 50.00, 'fas fa-video', 'daily', 1, 1, 'adsgram');
```

### Step 4: Clear Cache & Test

1. Browser cache clear करें (Ctrl + Shift + Delete)
2. Hard refresh करें (Ctrl + Shift + R)
3. App reload करें Telegram में

## Testing / टेस्टिंग

### Browser Console में Debug करें (F12):

#### 1. Check SDK Loading:
```javascript
console.log({
    Adsgram: !!window.Adsgram,
    Adexium: !!window.AdexiumWidget,
    Monetag: typeof show_10055887 === 'function',
    Richads: !!window.TelegramAdsController
});
```

**Expected Output:**
```javascript
{
    Adsgram: true,
    Adexium: true,
    Monetag: true,
    Richads: true
}
```

अगर कोई `false` है तो SDK load nahi hua.

#### 2. Test Individual Networks:

**Test Adsgram (Currently Working):**
```javascript
const ctrl = window.Adsgram.init({ blockId: "16415" });
ctrl.show().then(() => console.log("✅ Adsgram OK"));
```

**Test Adexium:**
```javascript
const widget = new AdexiumWidget({
    wid: 'ef364bbc-e2b8-434c-8b52-c735de561dc7',
    adFormat: 'interstitial'
});
widget.show();
```

**Test Monetag:**
```javascript
show_10055887({
    type: 'inApp',
    inAppSettings: {
        frequency: 1,
        capping: 0,
        interval: 0,
        timeout: 0,
        everyPage: false
    }
});
```

**Test Richads:**
```javascript
const rich = new TelegramAdsController();
rich.initialize({ pubId: "820238", appId: "4130" });
rich.showAd(375144);
```

## Common Errors / आम Errors

### Error 1: "SDK not loaded"
**Kya dikhe:**
```
❌ Adexium SDK not available
```

**Solution:**
- Check `index.html` में SDK script tags hai
- Network tab में check करें ki scripts load ho rahi hain
- Ad blocker disable करें

### Error 2: "Ad unit not found"
**Kya dikhe:**
```
❌ No ad config found for placement: task
```

**Solution:**
- Database fix run करें (Step 1)
- `ad_placements` table में entry honi chahiye

### Error 3: "Adexium ad failed to load"
**Kya dikhe:**
```
❌ Adexium ad failed to load: Invalid widget ID
```

**Solution:**
- Unit code check करें database में
- Hona chahiye: `ef364bbc-e2b8-434c-8b52-c735de561dc7`
- NOT: Full JavaScript code

### Error 4: "All ads failed to load"
**Kya dikhe:**
```
❌ All ads failed to load
📊 Primary network: adexium
📊 Fallback networks: adsgram, monetag
```

**Solution:**
1. Check console log - kaunsa network fail hua
2. Database fix verify करें
3. Individual network test करें (upar dekhen)

## Watch Ad Task Configuration

### Task Details:
- **Title:** Watch Ad & Earn 50 Coins
- **URL:** `#watch-ad` (special identifier)
- **Reward:** 50 coins
- **Type:** Daily (reset hota hai)
- **Ad Network:** Adsgram (primary)

### How It Works:
1. User "Tasks" section mein jata hai
2. "Watch Ad & Earn" task dikhta hai
3. "Start" click karne par directly ad show hota hai (no URL open)
4. Ad complete hone par automatic verify hota hai
5. 50 coins mil jate hain
6. Next day reset ho jata hai

## Ad Placement Priority

### For Tap (Every 5 taps):
```
Primary   → Richads (#375144)
Fallback  → Adsgram (int-16415)  ✓ Currently working
Fallback  → Monetag (10055887)
```

### For Tasks:
```
Primary   → Adsgram task ad (task-16416)
Fallback  → Adsgram interstitial (int-16415)
Fallback  → Richads (#375144)
```

### For Watch Ad Task:
```
Primary   → Adsgram native (task-16416)
Fallback  → Adsgram interstitial (int-16415)
Fallback  → Adsgram rewarded (16414)
```

## Network Status Check

### In Browser Console:
```javascript
// Check AdManager initialization
console.log('AdManager initialized:', AdManager.initialized);

// Force re-initialize
AdManager.initialized = false;
AdManager.init().then(() => console.log('✅ Reinitialized'));
```

## Files to Check

### 1. Database Tables:
- `ad_networks` - Network enabled hai?
- `ad_units` - Unit codes sahi hain?
- `ad_placements` - Placements configured hain?
- `tasks` - Watch ad task hai?

### 2. JavaScript Files:
- `js/ads.js` - Updated version deploy hua?
- `js/app.js` - Task handling updated hai?

### 3. HTML File:
- `index.html` - SDK scripts load ho rahi hain?

## Quick Diagnosis Commands

### Check Everything:
```javascript
// Run in browser console
const diagnosis = {
    sdks: {
        adsgram: !!window.Adsgram,
        adexium: !!window.AdexiumWidget,
        monetag: typeof show_10055887 === 'function',
        richads: !!window.TelegramAdsController
    },
    adManager: {
        initialized: AdManager.initialized,
        networks: Object.keys(AdManager.networks).reduce((acc, key) => {
            acc[key] = !!AdManager.networks[key];
            return acc;
        }, {})
    },
    userData: !!window.userData
};

console.table(diagnosis.sdks);
console.log('AdManager:', diagnosis.adManager);
console.log('User Data:', diagnosis.userData);
```

## Final Checklist ✅

Before testing, make sure:

- [ ] Database fix SQL script run kiya
- [ ] `ad_units` table में correct unit codes hain
- [ ] Watch ad task add ho gaya database में
- [ ] `js/ads.js` updated file upload hui server par
- [ ] `js/app.js` updated file upload hui server par
- [ ] Browser cache clear kiya
- [ ] Console में SDK status check kiya

## Support

Agar problem continue ho:

1. **Console log screenshot lein** (F12 → Console)
2. **Database query run karein:**
   ```sql
   SELECT * FROM ad_units WHERE id IN (1,2,3,4,5,6,7);
   SELECT * FROM ad_placements;
   SELECT * FROM tasks WHERE url = '#watch-ad';
   ```
3. **Network tab check karein** (F12 → Network)
   - SDK scripts load ho rahi hain?
   - API calls successful hain?

---

**Important:** सबसे पहले database fix ज़रूर run करें! Bina iske Monetag, Richads, और Adexium काम नहीं करेंगे।

**Status:** All fixes ready, database fix pending deployment
**Last Updated:** 2025-10-29
