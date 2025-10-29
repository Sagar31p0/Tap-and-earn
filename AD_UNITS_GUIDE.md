# Ad Units Configuration Guide (विज्ञापन यूनिट्स गाइड)

यह गाइड आपको बताएगी कि Ad Units और Head Code कहाँ और कैसे change या add करें।

---

## 📋 Table of Contents

1. [Head Code (SDK Scripts) - कहाँ Add करें](#1-head-code-sdk-scripts)
2. [Ad Networks Configuration - Admin Panel में](#2-ad-networks-configuration)
3. [Ad Unit Codes - JavaScript में](#3-ad-unit-codes-javascript)
4. [Ad Placements - कहाँ Ads Show होती हैं](#4-ad-placements)
5. [Testing Ads - कैसे Test करें](#5-testing-ads)

---

## 1. Head Code (SDK Scripts)

### 📁 File: `index.html`
### 📍 Location: Lines 9-21 (HEAD Section में)

यहाँ आप नए Ad Network के SDK scripts add कर सकते हैं:

```html
<head>
    <!-- Line 9-21: Ad Networks SDKs -->
    
    <!-- Adexium SDK -->
    <script type="text/javascript" src="https://cdn.tgads.space/assets/js/adexium-widget.min.js"></script>
    
    <!-- Monetag SDK -->
    <script src="//libtl.com/sdk.js" data-zone="10055887" data-sdk="show_10055887"></script>
    
    <!-- Adsgram SDK -->
    <script src="https://sad.adsgram.ai/js/sad.min.js"></script>
    
    <!-- Richads SDK -->
    <script src="https://richinfo.co/richpartners/telegram/js/tg-ob.js"></script>
</head>
```

### ✅ नया Ad Network SDK कैसे Add करें:

**Line 21 के बाद नया script tag add करें:**

```html
<!-- Your New Ad Network -->
<script src="https://your-ad-network-url.com/sdk.js"></script>
```

**Example:**
```html
<!-- Line 21 के बाद add करें -->
<!-- PropellerAds -->
<script src="https://propellerads.com/js/sdk.js" data-zone="YOUR_ZONE_ID"></script>
```

---

## 2. Ad Networks Configuration

### 📁 File: `admin/ads.php` (Admin Panel)
### 📍 Location: पूरी file - Lines 1-754

Admin panel में ad networks और units manage करने के लिए:

### A. Network Names (Lines 93-94)
Database query जो networks fetch करती है:
```php
$stmt = $db->query("SELECT * FROM ad_networks ORDER BY id ASC");
$adNetworks = $stmt->fetchAll();
```

### B. Add Network Form (Lines 372-398)
नया ad network add करने के लिए modal:
```html
<!-- Line 372-398: Add Network Modal -->
- Network Name: adsgram, monetag, adexium, richads, etc.
- Enable/Disable checkbox
```

### C. Add Unit Form (Lines 431-489)
नया ad unit add करने के लिए modal:

**Important Fields:**
- **Line 443-448**: Network Selection dropdown
- **Line 455-457**: Unit Code/ID field (यहाँ अपनी ad unit ID डालें)
- **Line 462-467**: Unit Type (banner/interstitial/rewarded/native)
- **Line 471-478**: Placement Key (कहाँ show करना है)

```html
<!-- Line 455-457: Ad Unit Code Input -->
<textarea class="form-control" name="unit_code" rows="3" required></textarea>
<small class="text-muted">Ad unit code or ID from the network</small>
```

### D. Placement Configuration (Lines 555-617)
यहाँ आप configure करते हैं कि कौनसी ad unit कहाँ show हो:

- **Primary Ad Unit**: पहली ad network (Line 575-581)
- **Secondary Ad Unit**: Fallback (Line 585-591)
- **Tertiary Ad Unit**: 2nd Fallback (Line 595-601)
- **Frequency**: कितनी बार show करें (Line 605-607)

---

## 3. Ad Unit Codes (JavaScript)

### 📁 File: `js/ads.js`
### 📍 Location: Lines 1-554

यह file सभी ad networks को handle करती है।

### A. Network Initialization (Lines 16-65)

**Richads Initialization (Lines 24-37):**
```javascript
// यहाँ Richads के pubId और appId change करें
this.networks.richads = new TelegramAdsController();
this.networks.richads.initialize({
    pubId: "820238",      // ← अपना pubId यहाँ डालें
    appId: "4130"         // ← अपना appId यहाँ डालें
});
```

**Important Lines:**
- **Line 28**: `pubId: "820238"` - यहाँ अपना Publisher ID डालें
- **Line 29**: `appId: "4130"` - यहाँ अपना App ID डालें

### B. Adexium Configuration (Lines 179-237)

```javascript
// Line 189-194: Adexium Widget ID extraction
if (widgetId && widgetId.includes('wid:')) {
    const match = widgetId.match(/wid:\s*['"]([^'"]+)['"]/i);
    if (match && match[1]) {
        widgetId = match[1];  // Widget ID यहाँ extract होती है
    }
}
```

**Important:**
- **Line 197-222**: Adexium widget configuration
- यहाँ widget callbacks handle होती हैं (onComplete, onError, onClose)

### C. Monetag Configuration (Lines 240-283)

```javascript
// Line 245: Monetag function call
if (typeof show_10055887 === 'function') {
    // ↑ यह "10055887" आपका zone ID है
    // HEAD में SDK के data-zone attribute से match होना चाहिए
}
```

**ध्यान दें:** Zone ID को 2 जगह update करें:
1. `index.html` में SDK script tag का `data-zone` (Line 14)
2. `js/ads.js` में function name (Line 54, 77, 245)

### D. Adsgram Configuration (Lines 286-331)

```javascript
// Line 303: Adsgram initialization
const AdController = window.Adsgram.init({ 
    blockId: blockId    // ← Ad Unit में दी गई block ID यहाँ आएगी
});
```

### E. Richads Configuration (Lines 334-386)

```javascript
// Line 354: Richads unit display
this.networks.richads.showAd(unitId)  // ← Unit ID automatically pass होती है
```

---

## 4. Ad Placements

### 📁 File: `js/ads.js`
### 📍 Lines 388-521 (Main Show Function)

यहाँ ads को different placements में show किया जाता है:

```javascript
// Line 388: Main ad show function
async show(placement, onComplete, isRetry = false) {
    // placement values:
    // - "tap" (Tap & Earn)
    // - "spin" (Spin Wheel)
    // - "game_preroll" (Game start से पहले)
    // - "task" (Task complete करने पर)
    // - "energy_recharge" (Energy recharge)
    // - "wallet" (Wallet operations)
}
```

### Available Placements:

| Placement Key | Description | File Location |
|--------------|-------------|---------------|
| `tap` | Tap करने पर ad | `js/app.js` |
| `spin` | Spin wheel use करने पर | `js/app.js` |
| `game_preroll` | Game start होने से पहले | `js/app.js` |
| `task` | Task complete करने पर | `js/app.js` |
| `energy_recharge` | Energy recharge button | `index.html` Line 77-79 |
| `wallet` | Wallet operations | `js/app.js` |

### Energy Recharge Button Example:

📁 **File: `index.html`**
📍 **Lines 77-79:**

```html
<button class="btn-watch-ad" id="btn-recharge-energy" style="display: none;">
    <i class="fas fa-video"></i> Watch Ad to Recharge Energy
</button>
```

---

## 5. Testing Ads

### A. Admin Panel Test Function

📁 **File: `admin/ads.php`**
📍 **Lines 669-709:**

```javascript
// Line 669: Test Ad Unit function
async function testAdUnit(unitId, placement) {
    // यह function ad unit को test करता है
    // Status badge update करता है
}
```

### B. Test All Ads Button

📁 **File: `admin/ads.php`**
📍 **Lines 711-730:**

```javascript
// Line 711: Check all ads
async function checkAllAdStatus() {
    // सभी ad placements को एक साथ test करता है
}
```

---

## 📝 Quick Reference Chart

| Task | File | Lines | Description |
|------|------|-------|-------------|
| **Add SDK Script** | `index.html` | 9-21 | HEAD में SDK scripts |
| **Richads pubId/appId** | `js/ads.js` | 28-29 | Publisher और App IDs |
| **Monetag Zone ID** | `index.html` | 14 | SDK script data-zone |
| **Monetag Function Name** | `js/ads.js` | 54, 77, 245 | show_XXXXXX function |
| **Adexium Widget** | `js/ads.js` | 197-222 | Widget configuration |
| **Adsgram Block** | `js/ads.js` | 303 | Block ID initialization |
| **Add Network (Admin)** | `admin/ads.php` | 372-398 | Add network modal |
| **Add Unit (Admin)** | `admin/ads.php` | 431-489 | Add unit modal |
| **Configure Placement** | `admin/ads.php` | 555-617 | Placement config modal |
| **Energy Button** | `index.html` | 77-79 | Recharge energy button |
| **Test Ads** | `admin/ads.php` | 669-730 | Testing functions |

---

## 🔧 Step-by-Step: Naya Ad Network Add Karna

### Step 1: SDK Script Add करें
📁 `index.html` → Line 21 के बाद

```html
<!-- Your New Network -->
<script src="https://new-network.com/sdk.js"></script>
```

### Step 2: Admin Panel में Network Add करें
1. Admin Panel खोलें → Ads Management
2. "Add Network" button पर click करें
3. Network name डालें (lowercase में: e.g., "propeller")
4. Enable checkbox check करें
5. Submit करें

### Step 3: Ad Unit Add करें
1. "Add Ad Unit" button पर click करें
2. Network select करें
3. Unit Name दें
4. **Unit Code/ID** डालें (यह important है!)
5. Unit Type select करें
6. Placement Key select करें
7. Submit करें

### Step 4: Placement Configure करें
1. "Configure" button पर click placement के सामने
2. Primary Ad Unit select करें
3. Fallback units select करें (optional)
4. Frequency set करें
5. Update करें

### Step 5: JavaScript में Handler Add करें
📁 `js/ads.js` → Line 421 के बाद (switch statement में)

```javascript
case 'yournewnetwork':
    await this.showYourNewNetwork(adConfig.ad_unit);
    break;
```

और नया function बनाएं:

```javascript
async showYourNewNetwork(adUnit) {
    return new Promise((resolve, reject) => {
        try {
            if (window.YourNetworkSDK) {
                window.YourNetworkSDK.show({
                    unitId: adUnit.id,
                    onComplete: () => resolve(),
                    onError: (error) => reject(error)
                });
            } else {
                reject(new Error('SDK not loaded'));
            }
        } catch (error) {
            reject(error);
        }
    });
}
```

---

## ⚠️ Important Notes

1. **Unit Code Format**: हर network की अलग format होती है:
   - Adsgram: `123456` (numeric block ID)
   - Adexium: `wid: 'abc-123-def'` (widget ID)
   - Richads: `12345` (numeric unit ID)
   - Monetag: Zone ID SDK में ही डालनी होती है

2. **Fallback System**: 
   - Primary ad fail हो तो Secondary चलेगी
   - Secondary fail हो तो Tertiary चलेगी
   - सभी fail हों तो error show होगी

3. **Testing**: हमेशा Admin Panel से test करें before going live

4. **Frequency**: 
   - Frequency बताती है कितनी बार action होने पर ad show होगी
   - Example: Frequency = 5 means हर 5वीं tap पर ad

---

## 🎯 Common Ad Unit IDs Format

### Adsgram
```
Block ID: 1234
Format: Numeric
Example: 5678
```

### Adexium
```
Widget ID: abc-123-def-456
Format: String with hyphens
Example: wid: '123-abc-456'
```

### Richads
```
Unit ID: 12345
Format: Numeric
Example: 4130
```

### Monetag
```
Zone ID: 10055887
Format: Numeric (SDK script में)
Example: data-zone="10055887"
```

---

## 📞 API Endpoints

### Get Ad for Placement
```
GET /api/ads.php?placement=tap&user_id=123
```

📁 **File: `api/ads.php`**
📍 **Lines 8-83**

### Log Ad Event
```
POST /api/ads.php
Body: {user_id, placement, ad_unit_id, event}
```

📁 **File: `api/ads.php`**
📍 **Lines 85-139**

---

## ✨ Summary

1. **Head Code**: `index.html` Lines 9-21
2. **Ad Configuration**: Admin Panel → `admin/ads.php`
3. **JavaScript Logic**: `js/ads.js` Lines 1-554
4. **API Backend**: `api/ads.php` Lines 1-141

अगर कोई doubt हो तो इन files और line numbers को check करें। सभी ad-related code इन्हीं files में है! 🚀
