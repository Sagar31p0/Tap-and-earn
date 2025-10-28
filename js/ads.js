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
                if (typeof AdexiumWidget !== 'undefined') {
                    const widget = new AdexiumWidget({
                        wid: adUnit.id,
                        adFormat: adUnit.type || 'interstitial',
                        onComplete: () => {
                            console.log('Adexium ad completed');
                            resolve();
                        },
                        onError: (error) => {
                            console.error('Adexium error:', error);
                            resolve(); // Resolve anyway to not block flow
                        },
                        onClose: () => {
                            console.log('Adexium ad closed');
                            resolve();
                        }
                    });
                    
                    widget.show();
                } else {
                    console.warn('Adexium SDK not loaded');
                    resolve();
                }
            } catch (error) {
                console.error('Adexium error:', error);
                resolve(); // Changed from reject to resolve
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
                // Adsgram SDK for Telegram Mini Apps
                if (window.Adsgram) {
                    const AdController = window.Adsgram.init({ blockId: adUnit.id });
                    
                    AdController.show().then(() => {
                        console.log('Adsgram ad completed:', adUnit.id);
                        resolve();
                    }).catch((error) => {
                        console.error('Adsgram error:', error);
                        // Resolve anyway to not block the flow
                        resolve();
                    });
                } else {
                    console.warn('Adsgram SDK not loaded');
                    resolve();
                }
            } catch (error) {
                console.error('Adsgram error:', error);
                resolve();
            }
        });
    },
    
    async showRichads(adUnit) {
        return new Promise((resolve, reject) => {
            try {
                if (this.networks.richads && window.TelegramAdsController) {
                    // Show based on unit type
                    const unitId = parseInt(adUnit.id.replace('#', ''));
                    
                    console.log('Showing Richads ad:', unitId);
                    
                    // Use Richads SDK
                    this.networks.richads.showAd(unitId)
                        .then(() => {
                            console.log('Richads ad completed');
                            resolve();
                        })
                        .catch((error) => {
                            console.error('Richads display error:', error);
                            resolve();
                        });
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
