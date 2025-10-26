I need only php codes
My server:- shares cloud hosting
Domain:- https://reqa.antipiracyforce.org/test
Database and username:- u988479389_tery
Bot:- @kuchpvildybot

PART 1 — USER-FACING FEATURES (What users will see)

1. TAP & EARN

User taps a coin icon → they earn coins (admin-configurable amount).

Energy bar (100% max) decreases per tap; auto-recharges over time (admin-configurable rate).

Watch Ad button when energy is 0 to instantly recharge (admin-configurable %).

Admin controls:

Tap reward amount (e.g., +5 coins per tap)

Energy consumption per tap (or %)

Energy recharge rate (e.g., 5 minutes = +1%)

Watch-ad recharge amount (e.g., +5%)

Tap ad frequency (e.g., every 7 taps show an ad)

Select ad network for tap ads (Adexium, Monetag, Adsgram, Richads)

Add multiple ad units per ad network; choose which unit to use per placement



2. SPIN THE WHEEL

Hourly or admin-configured free spins (e.g., 1 free spin per hour).

Wheel with 8 blocks: 10, 20, 50, 100, 200, 500, 1000, JACKPOT.

Double Reward Option: user watches ad → reward ×2.

Timer shows next available spin.

Admin controls:

Rewards list and values

Per-block probability (ability to change % chance or disable a block)

Daily spin limit

Which ad network and which ad unit to use for spin ads

Option to make double-reward visible per spin block



3. GAMES (Play & Earn)

List of playable games. Each play gives coins.

Ad displays before game opens (pre-roll).

Admin controls:

Add/remove games, name, icon

Reward per game

Game URL

Play limit (daily/weekly/unlimited)

Which ad network + ad unit to use per game



4. TASKS (Complete & Earn)

Two task types:

1. One-time tasks (follow Twitter, visit site, join channel, watch ad, etc.)


2. Daily tasks (reset daily)



Flow: click task → ad plays → external URL opens → user completes → user clicks Verify → server validates → coins awarded.

Admin controls:

Create tasks (title, URL, reward, icon)

Type: daily / one-time

Assign ad network (per-task override possible)

Toggle active/inactive

Sort order



5. REFERRAL SYSTEM

Each user gets a unique referral link.

Reward unlock condition: referred friend must complete at least 1 task (configurable).

UI shows referral list with status (Pending / Approved).

Admin controls:

Referral reward amount

Unlock condition (number of tasks completed by friend)



6. WALLET & WITHDRAWALS

Users view coin balance; request withdrawals.

Admin controls:

Add payment methods (PayPal, Bank, Crypto, UPI)

Minimum withdrawal amount

Approve / Reject flows

Upload payment proof



7. URL SHORTENER (Hidden / Monetized)

Mode 1 — Task + Video: user completes task → Watch Video button appears → ad plays → original URL opens.

Mode 2 — Direct Ad: click → Play Ad → original URL opens automatically.

Admin can create short links, choose mode, pick ad unit and task mapping.



---

PART 2 — ADMIN PANEL FEATURES

Dashboard

KPIs: total users, new users today, active users, total taps, total spins, ads watched, tasks completed, active games, coins distributed, total referrals, active tasks, pending withdrawals.

Quick actions: Manage users, Configure ads, Add tasks, Manage games, Spin settings, Process withdrawals, Broadcast, Global settings, URL shortener.


Ads Management (Core)

Supported networks: Adexium, Monetag, Adsgram, Richads.

For each network:

Toggle ON/OFF

Add multiple ad units (per unit: name, placement type, code/ID, status)

Unit types supported: rewarded, interstitial, banner, native, push-style, embedded banner


Per-placement mapping:

Map placements (Tap, Spin, Game Pre-roll, Task Ad, Short Link Ad, Wallet Watch Ad) → choose ad network + specific ad unit.


Fallback order:

Configure primary → secondary → tertiary networks for each placement.


Ad frequency / capping:

Set frequency (e.g., show ad every N taps)

Session capping and global capping controls



Tasks Management

CRUD tasks

Per-task ad network override

Daily task reset settings


Games Management

CRUD games, game stats, play limits, ad unit selection per game


Spin Wheel Settings

Configure wheel blocks, reward amounts, per-block probability (%), double reward toggle, jackpot settings, daily limit


User Management

View users with pagination, stats, ads watched etc.

Ban / Unban / Delete

Export users


Withdrawals

Request list, approve/reject, upload payment proof, transaction IDs, filters by status


Broadcast System

Rich content (text, image, video), CTA buttons, send to segments (All / Active / Specific)


Global Settings

Bot name, username, welcome message, support contact, reward defaults, limits, features toggle


URL Shortener Management

Create short link, select mode, assign tasks and ad units, view stats and click conversions



---

PART 3 — AD INTEGRATION (How it works + SDK snippets)

Principles

Admin adds ad networks and multiple ad units per network in Admin → Ads Management.

Each placement (Tap, Spin, Game, Task) has a mapped ad unit (or fallback chain).

Client calls /api/get_ads?placement=tap to get which network + ad unit to show.

Client/ads.js shows ad using the network SDK snippet associated with the returned ad unit.

Server must log ad events: impression, click, complete, reward grant.



---

1) Adexium — Integration & Example

Admin fields:

Toggle ON/OFF

Widget ID(s) per ad format (interstitial, rewarded, banner)

Example admin entry names: Tap Interstitial, Tap Rewarded, Banner Home


SDK snippet to place in <head>

<!-- Adexium -->
<script src="https://telegram.org/js/telegram-web-app.js?56"></script>
<script type="text/javascript" src="https://cdn.tgads.space/assets/js/adexium-widget.min.js"></script>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', () => {
  // Example: Interstitial
  const adexiumWidget = new AdexiumWidget({
    wid: 'ef364bbc-e2b8-434c-8b52-c735de561dc7', // admin-provided widget id
    adFormat: 'interstitial'
  });
  // Auto mode will decide when to show as per SDK
  adexiumWidget.autoMode();
  
  // Example: Show manually
  // adexiumWidget.show();
});
</script>

Admin must create ad unit records like:

name: Tap Interstitial → widget id: ef364bbc-e2b8-434c-8b52-c735de561dc7 → format: interstitial



---

2) Monetag — Integration & Example

Admin fields:

Toggle ON/OFF

SDK ID / Zone ID (e.g., 10055887)

Per ad format config: rewarded/interstitial/inApp settings


SDK snippet to place in <head>

<!-- Monetag -->
<script src="//libtl.com/sdk.js" data-zone="10055887" data-sdk="show_10055887"></script>

Usage examples (client-side JS):

// Rewarded interstitial example:
show_10055887().then(() => {
  // Reward callback: grant reward here on successful complete
  grantUserReward();
});

// In-App Interstitial with advanced settings:
show_10055887({
  type: 'inApp',
  inAppSettings: {
    frequency: 2,       // show automatically 2 ads within time window
    capping: 0.1,       // 0.1 hours = 6 minutes
    interval: 30,       // 30 sec between ads
    timeout: 5,         // delay 5 sec before first show
    everyPage: false    // session preserved between pages
  }
});

Admin note: store SDK ID 10055887 and allow per-placement ad unit creation.


---

3) Adsgram — Integration & Example

Admin fields:

Toggle ON/OFF

Block IDs for each format (reward block, interstitial block, task ad block)

Adsgram is Telegram-native — ensure block IDs map to placements


Ad units examples provided by you:

Task ad unit: task-16416

Interstitial ad unit: int-16415

Reward ad unit: 16414


Admin mapping example:

Task Ads → adsgram block task-16416

Game Pre-roll → adsgram int-16415

Spin Reward → adsgram 16414


Client usage:

/api/get_ads?placement=task → returns {network: 'adsgram', unitId: 'task-16416'}

client calls Adsgram SDK or Adsgram code to show block task-16416 (SDK snippet will depend on Adsgram docs / Telegram environment).



---

4) Richads — Integration & Example

Admin fields:

Toggle ON/OFF

Publisher ID, App ID

Ad unit IDs for different styles


SDK snippet to place in <head>

<!-- Richads -->
<script src="https://richinfo.co/richpartners/telegram/js/tg-ob.js"></script>
<script>
  window.TelegramAdsController = new TelegramAdsController();
  window.TelegramAdsController.initialize({
    pubId: "820238",
    appId: "4130"
  });
</script>

Ad unit examples you provided:

#375144 — Telegram Interstitial video

#375143 — Telegram Interstitial banner

#375141 — Telegram Push-style

#375142 — Telegram Embedded banner


Admin mapping example:

Tap ads → Richads #375144 (if chosen)

Banner home → Richads #375142



---

Example: /api/get_ads Flow (server → client)

1. Client requests: GET /api/get_ads?placement=tap


2. Server reads admin mapping (placement → ad_unit → network) and fallback chain.


3. Server responds:



{
  "placement": "tap",
  "network": "adexium",
  "ad_unit": {
    "id": "ef364bbc-e2b8-434c-8b52-c735de561dc7",
    "type": "interstitial",
    "meta": {}
  },
  "fallback": [
    {"network":"monetag","ad_unit":{"id":"10055887","type":"rewarded"}},
    {"network":"adsgram","ad_unit":{"id":"task-16416","type":"task"}}
  ]
}

4. Client reads network and uses the correct SDK snippet to show the ad. On success/completion the client calls /api/report_ad_event with placement, event=complete, user_id, ad_unit.




---

ADMIN DATA MODEL SUGGESTIONS (high level)

ad_networks (id, name, enabled, default_settings)

ad_units (id, network_id, placement_key, unit_code, unit_type, name, status)

placements (key: tap|spin|game|task|shortlink, description)

ads_logs (user_id, placement, ad_unit_id, event, timestamp)

users, tasks, games, spins, withdrawals, short_links, referrals, user_energy

🏆 Leaderboard System (Top 20 Users + Personal Rank)

What it does:

Displays the Top 20 users based on their total coins or points.

Shows 1st, 2nd, and 3rd positions with gold, silver, and bronze cards (like your screenshot).

Displays user’s own rank below — even if they’re not in the top 20.

Live updates as users earn more coins.

Clicking “Leaderboard” opens a clean, animated rank board.


Admin Control:

Select leaderboard type:

Coins-based

Tasks completed

Referrals earned


Set reset frequency (daily, weekly, monthly)

Option to auto-reward Top 3 or Top 10 users

Display toggle for “Your Rank” section (ON/OFF)


Example Flow:

Leaderboard:
🥇 WinWheelPlus_Su — 219 coins
🥈 Sagar_systum — 96 coins
🥉 0p_bp67 — 50 coins
4. Siksha — 38 coins
5. KIRA_JOD_MAXX — 0 coins
↓
Your Rank: #1 (219 coins)


---

2. 📊 Ads Analytics Dashboard

What it does: Shows admin the performance of all connected ad networks — helps identify which network gives the best earnings and engagement.

Metrics shown:

Total Ads Shown

Total Impressions

Click-Through Rate (CTR)

Completion Rate (for rewarded ads)

Estimated Earnings (auto-calculated)

Top-performing ad units (ranked by performance)


Admin Controls:

Date filters (today / yesterday / 7 days / 30 days)

Filter by network (Adexium / Monetag / Adsgram / Richads)

Export to CSV or PDF

Chart visualization (Bar + Line Graph)


Example Display:

Monetag — 5,200 Ads Shown | 4,950 Completions | CTR: 8.2% | Est. $12.35
Adexium — 2,840 Ads Shown | 2,700 Completions | CTR: 7.1% | Est. $7.80
Adsgram — 1,120 Ads Shown | 1,100 Completions | CTR: 9.5% | Est. $5.40

👉 Helps admin optimize placements for better ad revenue.


---

3. 🔔 Firebase Push Notifications (Instant Alerts)

What it does: Sends real-time push notifications to users through Firebase.

Use cases:

“Your daily bonus is ready!”

“You have a free spin waiting!”

“You’ve reached Level 2 — claim your reward!”

“Withdrawal Approved: ₹50 sent to your account.”


Admin Control:

Create and send push messages from Admin Panel.

Schedule notifications (specific time or daily reminders).

Segment users:

All users

Active users (within last 24 hours)

Inactive users (no activity for 3+ days)



Technical:

Firebase SDK integrated in client app.

Notifications are clickable and redirect to related app sections (e.g., “Spin Wheel” screen).



---

4. 🌙 Dark and Light Mode

What it does:

Gives users the choice between Light Mode and Dark Mode UI themes.

Saves theme preference locally so it stays even after app restart.


User Interface:

Toggle in Profile or Settings:
“🌓 Switch to Dark Mode”

Smooth transition animation between modes.


Admin Option:

Default mode (Light / Dark / Auto)

Theme color customization (primary gradient, button color, background intensity)


Technical:

Uses CSS variables or Telegram theme detection for auto adjustment.



---

5. 🏠 Home Screen Wallet Display (Clickable Balance)

What it does:

Displays the user’s current coins and their USD equivalent (or INR).

Clicking the balance opens the Wallet screen directly.

Auto-refreshes when user earns new coins.


Display Example (like your screenshot):

💰 Balance
219.27 Coins
≈ $0.22

Admin Control:

Set coin-to-USD (or INR) conversion rate.

Toggle currency display (ON/OFF)

Control wallet screen routing (e.g., “open wallet” or “show withdrawal options”)


Check all files and send paths and koi feature miss hua toh batana me bataunga fir apko add karna hai ya nhi.
