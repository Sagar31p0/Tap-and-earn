# Adsgram Task Ads Guide

## Overview
Adsgram Task Ads are special ad units designed for promoting Telegram channels and bots. When users watch these ads, they automatically see the channel/bot name, logo, and description.

## How Adsgram Task Ads Work

### 1. **Task Ads vs Regular Ads**
- **Regular Ads**: Show promotional content from advertisers
- **Task Ads**: Specifically designed for Telegram channel/bot promotion
- **Automatic Display**: Channel/bot info (name, logo) is automatically shown by Adsgram

### 2. **Creating Task Ads in Adsgram**

#### Step 1: Login to Adsgram Dashboard
1. Go to https://adsgram.ai/
2. Login with your account

#### Step 2: Create/Configure Platform
1. Click "Platforms" in sidebar
2. Click "Add Platform" or edit existing one
3. **IMPORTANT**: Set the **App URL** to match your bot's URL exactly:
   ```
   https://go.teraboxurll.in
   ```
4. Save the platform

#### Step 3: Create Task Ad Block
1. Go to "Ad Blocks" section
2. Click "Create Ad Block"
3. Select block type: **"Task"** or **"Promo"**
4. Configure the task ad:
   - **Block Name**: e.g., "Channel Join Task"
   - **Platform**: Select the platform you created
   - **Ad Format**: Task/Promo
5. Note the **Block ID** (e.g., 16414)
6. Save the ad block

#### Step 4: Configure Task Requirements (in Adsgram)
When creating a task ad in Adsgram, you can set:
- **Channel/Bot to promote**: Enter @username
- **Task type**: Join channel, start bot, etc.
- **Reward amount**: Coins to give users
- **Daily limits**: How many times users can complete

#### Step 5: Add Task Ad to Your Bot
1. Go to your bot's Admin Panel ? Ads Management
2. Click "Add Ad Unit"
3. Fill in:
   - **Network**: Select "Adsgram"
   - **Unit Name**: e.g., "Channel Join Task Ad"
   - **Unit Code/ID**: Enter the Block ID from Adsgram (e.g., `16414`)
   - **Unit Type**: Select "rewarded" or "task"
   - **Placement Key**: Select "task" (for task completion)
   - **Active**: Check this box
4. Click "Add Unit"

5. Go to "Ad Placements Configuration"
6. Find the "task" placement
7. Click "Configure"
8. Set your new task ad as Primary Unit
9. Save

## Common Issues and Solutions

### Issue 1: "Platform App url for blockId = XXXXX not equal to https://go.teraboxurll.in"

**Cause**: The app URL in Adsgram dashboard doesn't match your actual app URL.

**Solution**:
1. Go to Adsgram dashboard
2. Navigate to Platforms section
3. Find the platform associated with block ID XXXXX
4. Update the App URL to: `https://go.teraboxurll.in`
5. OR: Create a new platform with the correct URL and create a new block

### Issue 2: Task ad doesn't show channel/bot info

**Cause**: The ad block is not configured as a "Task" type in Adsgram.

**Solution**:
1. In Adsgram dashboard, edit your ad block
2. Make sure the block type is "Task" or "Promo"
3. Configure the Telegram channel/bot in the task settings
4. Save and test again

### Issue 3: Ad loads but doesn't complete

**Possible causes**:
1. User didn't complete the task (e.g., didn't join channel)
2. Task verification failed
3. Network connection issue

**Solution**:
1. Make sure task requirements are clear to users
2. Test with a real Telegram account
3. Check browser console for errors

## Task Ad Types in Adsgram

### 1. **Channel Join Task**
- Users must join a Telegram channel
- Channel info is displayed automatically
- Verification happens via Telegram API

### 2. **Bot Start Task**
- Users must start a bot
- Bot info is displayed automatically
- Can include additional bot-specific tasks

### 3. **Message Task**
- Users must send a message to channel/bot
- Less common, used for engagement

## Best Practices

### 1. **Clear Task Instructions**
- Make sure users understand what they need to do
- Use descriptive task titles
- Set appropriate rewards

### 2. **Proper Platform Configuration**
- Always match the app URL exactly
- Use HTTPS, not HTTP
- Don't include trailing slashes

### 3. **Testing**
- Test with a real Telegram account
- Test in both development and production
- Verify task completion rewards are given

### 4. **Ad Placement**
- Use "task" placement for task-related ads
- Use "task_verify" placement after verification
- Don't overload users with too many task ads

## Integration with Your Bot

### Current Setup
Your bot automatically handles Adsgram task ads:
1. User clicks "Start" on a task
2. Ad is shown (Adsgram displays channel/bot info)
3. User completes the task (joins channel/starts bot)
4. User clicks "Verify"
5. Bot verifies completion
6. Reward is given

### Code Flow
```javascript
// 1. User starts task
startTask(taskId) ? showAd('task') ? Task ad displayed by Adsgram

// 2. User completes task (joins channel/bot)
// This happens in Telegram, outside your bot

// 3. User verifies task
verifyTask(taskId) ? showAd('task_verify') ? Reward given
```

## Debugging Task Ads

### Check Browser Console
```javascript
// Look for these messages:
? Adsgram SDK available
?? Adsgram controller initialized for block: 16414
? Adsgram ad completed: 16414

// If you see this error:
? Adsgram Configuration Error: Platform App url...
// ? Fix the platform URL in Adsgram dashboard
```

### Check Network Tab
1. Open browser DevTools (F12)
2. Go to Network tab
3. Look for requests to `adsgram.ai`
4. Check if the requests are successful (200 status)
5. Check response for error messages

### Test Configuration
```bash
# Current app URL
echo "App URL: $(curl -s https://go.teraboxurll.in | grep -o 'https://[^"]*' | head -1)"

# Should match what's in Adsgram platform settings
```

## Summary

? **Task ads automatically show channel/bot info** - This is an Adsgram feature  
? **Configure platform URL correctly** - Must match exactly: `https://go.teraboxurll.in`  
? **Use "Task" block type** - Select this when creating ad blocks in Adsgram  
? **Test thoroughly** - Verify task completion and rewards work correctly  

## Support

If you continue to have issues:
1. Check this guide first
2. Verify all URLs match exactly
3. Test with a fresh Telegram account
4. Check both browser console and server logs
5. Contact Adsgram support if the issue is on their end
