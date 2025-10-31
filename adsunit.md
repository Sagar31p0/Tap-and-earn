1. adexium.io

interstitial ad unit
<script type="text/javascript" src="https://cdn.tgads.space/assets/js/adexium-widget.min.js"></script>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', () => {
        const adexiumWidget = new AdexiumWidget({wid: '8391da33-7acd-47a9-8d83-f7b4bf4956b1', adFormat: 'interstitial'});
        adexiumWidget.autoMode();
    });
</script>

2. monetag.com

// Rewarded interstitial

show_10113890().then(() => {
    // You need to add your user reward function here, which will be executed after the user watches the ad.
    // For more details, please refer to the detailed instructions.
    alert('You have seen an ad!');
})


// In-App Interstitial

show_10113890({
  type: 'inApp',
  inAppSettings: {
    frequency: 2,
    capping: 0.1,
    interval: 30,
    timeout: 5,
    everyPage: false
  }
})

/*
This value is decoded as follows:
- show automatically 2 ads
  within 0.1 hours (6 minutes)
  with a 30-second interval between them
  and a 5-second delay before the first one is shown.
  The last digit, 0, means that the session will be saved when you navigate between pages.
  If you set the last digit as 1, then at any transition between pages,
  the session will be reset, and the ads will start again.
*/


// Rewarded Popup

show_10113890('pop').then(() => {
    // user watch ad till the end or close it in interstitial format
    // your code to reward user for rewarded format
}).catch(e => {
    // user get error during playing ad
    // do nothing or whatever you want
})


3. Richads.com

ads unit
#375934
Telegram Push-style
#375935
Telegram Embedded banner
#375936
Telegram Interstitial banner
#375937
Telegram Interstitial video
#375938
Telegram Playable ads

4. adsgram.io

  ads units
  1. Task:- task-16619
  2. Interstitial:- int-16618
  3. Reward:- 16617

