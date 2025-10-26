// Ad Management System
// Handles multiple ad networks: Adexium, Monetag, Adsgram, Richads

const AdManager = {
    initialized: false,
    networks: {
        adexium: null,
        monetag: null,
        adsgram: null,
        richads: null
    },
    
    async init() {
        if (this.initialized) return;
        
        try {
            // Initialize Richads
            if (window.TelegramAdsController) {
                this.networks.richads = new TelegramAdsController();
                this.networks.richads.initialize({
                    pubId: "820238",
                    appId: "4130"
                });
                console.log('Richads initialized');
            }
            
            this.initialized = true;
        } catch (error) {
            console.error('Ad initialization error:', error);
        }
    },
    
    async getAdConfig(placement) {
        try {
            const response = await fetch(`${API_URL}/ads.php?placement=${placement}&user_id=${userData.id}`);
            const data = await response.json();
            
            if (data.success) {
                return data;
            }
            return null;
        } catch (error) {
            console.error('Get ad config error:', error);
            return null;
        }
    },
    
    async showAdexium(adUnit) {
        return new Promise((resolve, reject) => {
            try {
                const widget = new AdexiumWidget({
                    wid: adUnit.id,
                    adFormat: adUnit.type || 'interstitial'
                });
                
                // Show the ad
                widget.show();
                
                // Simulate completion (Adexium should have callbacks in real implementation)
                setTimeout(() => {
                    resolve();
                }, 3000);
            } catch (error) {
                console.error('Adexium error:', error);
                reject(error);
            }
        });
    },
    
    async showMonetag(adUnit) {
        return new Promise((resolve, reject) => {
            try {
                if (typeof show_10055887 === 'function') {
                    show_10055887({
                        type: adUnit.type || 'inApp',
                        inAppSettings: {
                            frequency: 1,
                            capping: 0,
                            interval: 0,
                            timeout: 0,
                            everyPage: false
                        }
                    }).then(() => {
                        resolve();
                    }).catch((error) => {
                        console.error('Monetag error:', error);
                        resolve(); // Resolve anyway to not block flow
                    });
                } else {
                    console.error('Monetag SDK not loaded');
                    resolve();
                }
            } catch (error) {
                console.error('Monetag error:', error);
                resolve();
            }
        });
    },
    
    async showAdsgram(adUnit) {
        return new Promise((resolve, reject) => {
            try {
                // Adsgram implementation for Telegram
                // This would use Adsgram's SDK which is Telegram-specific
                console.log('Showing Adsgram ad:', adUnit.id);
                
                // Simulate ad display
                setTimeout(() => {
                    resolve();
                }, 2000);
            } catch (error) {
                console.error('Adsgram error:', error);
                resolve();
            }
        });
    },
    
    async showRichads(adUnit) {
        return new Promise((resolve, reject) => {
            try {
                if (this.networks.richads) {
                    // Show based on unit type
                    const unitId = parseInt(adUnit.id.replace('#', ''));
                    
                    // Richads implementation
                    console.log('Showing Richads ad:', unitId);
                    
                    // Simulate ad display
                    setTimeout(() => {
                        resolve();
                    }, 2000);
                } else {
                    console.error('Richads not initialized');
                    resolve();
                }
            } catch (error) {
                console.error('Richads error:', error);
                resolve();
            }
        });
    },
    
    async show(placement, onComplete) {
        await this.init();
        
        // Get ad configuration from server
        const adConfig = await this.getAdConfig(placement);
        
        if (!adConfig) {
            console.warn('No ad config found for placement:', placement);
            if (onComplete) onComplete();
            return;
        }
        
        // Log impression
        await this.logEvent(placement, adConfig.ad_unit.id, 'impression');
        
        try {
            // Show ad based on network
            switch (adConfig.network) {
                case 'adexium':
                    await this.showAdexium(adConfig.ad_unit);
                    break;
                case 'monetag':
                    await this.showMonetag(adConfig.ad_unit);
                    break;
                case 'adsgram':
                    await this.showAdsgram(adConfig.ad_unit);
                    break;
                case 'richads':
                    await this.showRichads(adConfig.ad_unit);
                    break;
                default:
                    console.warn('Unknown ad network:', adConfig.network);
            }
            
            // Log completion
            await this.logEvent(placement, adConfig.ad_unit.id, 'complete');
            
            // Call completion callback
            if (onComplete) {
                await onComplete();
            }
        } catch (error) {
            console.error('Ad display error:', error);
            
            // Try fallback if available
            if (adConfig.fallback && adConfig.fallback.length > 0) {
                console.log('Trying fallback ad...');
                const fallback = adConfig.fallback[0];
                try {
                    await this[`show${fallback.network.charAt(0).toUpperCase() + fallback.network.slice(1)}`](fallback.ad_unit);
                    if (onComplete) await onComplete();
                } catch (fallbackError) {
                    console.error('Fallback ad error:', fallbackError);
                    if (onComplete) onComplete(); // Still call callback
                }
            } else {
                if (onComplete) onComplete(); // Call callback even if ad fails
            }
        }
    },
    
    async logEvent(placement, adUnitId, event) {
        try {
            await fetch(`${API_URL}/ads.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    user_id: userData.id,
                    placement: placement,
                    ad_unit_id: adUnitId,
                    event: event
                })
            });
        } catch (error) {
            console.error('Log ad event error:', error);
        }
    }
};

// Global helper function to show ads
async function showAd(placement, onComplete) {
    return await AdManager.show(placement, onComplete);
}

// Initialize when document is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        AdManager.init();
    });
} else {
    AdManager.init();
}
