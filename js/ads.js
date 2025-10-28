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
    currentPlacement: null,
    currentCallback: null,
    maxRetries: 3,
    
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
    
    showLoadingOverlay(message = 'Ad Loading...') {
        let overlay = document.getElementById('ad-loading-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'ad-loading-overlay';
            overlay.innerHTML = `
                <div class="ad-loading-content">
                    <div class="ad-loading-spinner"></div>
                    <div class="ad-loading-text">📺 Ad Loading...</div>
                    <div class="ad-loading-subtext">Please wait while we load the ad</div>
                    <div class="ad-loading-message">This is required to continue</div>
                </div>
            `;
            document.body.appendChild(overlay);
        }
        overlay.classList.add('show');
    },
    
    hideLoadingOverlay() {
        const overlay = document.getElementById('ad-loading-overlay');
        if (overlay) {
            overlay.classList.remove('show');
        }
    },
    
    showErrorOverlay(error, retryCallback) {
        let overlay = document.getElementById('ad-loading-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'ad-loading-overlay';
            document.body.appendChild(overlay);
        }
        
        overlay.innerHTML = `
            <div class="ad-loading-content ad-error-content">
                <div class="ad-error-icon">⚠️</div>
                <div class="ad-error-text">Ad Failed to Load</div>
                <div class="ad-loading-subtext">${error || 'Unable to load advertisement'}</div>
                <div class="ad-loading-message">Ad is required to continue this action</div>
                <button class="ad-retry-btn" onclick="AdManager.retryAd()">
                    🔄 Retry Ad
                </button>
            </div>
        `;
        overlay.classList.add('show');
    },
    
    async retryAd() {
        if (this.currentPlacement && this.currentCallback) {
            this.hideLoadingOverlay();
            await this.show(this.currentPlacement, this.currentCallback);
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
                    let adCompleted = false;
                    
                    const widget = new AdexiumWidget({
                        wid: adUnit.id,
                        adFormat: adUnit.type || 'interstitial',
                        onComplete: () => {
                            console.log('Adexium ad completed');
                            adCompleted = true;
                            resolve();
                        },
                        onError: (error) => {
                            console.error('Adexium error:', error);
                            if (!adCompleted) {
                                reject(new Error('Adexium ad failed to load: ' + error));
                            }
                        },
                        onClose: () => {
                            console.log('Adexium ad closed');
                            if (adCompleted) {
                                resolve();
                            } else {
                                reject(new Error('Adexium ad closed without completion'));
                            }
                        }
                    });
                    
                    widget.show();
                    
                    // Timeout after 30 seconds
                    setTimeout(() => {
                        if (!adCompleted) {
                            reject(new Error('Adexium ad timeout'));
                        }
                    }, 30000);
                } else {
                    reject(new Error('Adexium SDK not loaded'));
                }
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
                        console.log('Monetag ad completed');
                        resolve();
                    }).catch((error) => {
                        console.error('Monetag error:', error);
                        reject(new Error('Monetag ad failed: ' + error));
                    });
                    
                    // Timeout after 30 seconds
                    setTimeout(() => {
                        reject(new Error('Monetag ad timeout'));
                    }, 30000);
                } else {
                    reject(new Error('Monetag SDK not loaded'));
                }
            } catch (error) {
                console.error('Monetag error:', error);
                reject(error);
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
                        reject(new Error('Adsgram ad failed: ' + error));
                    });
                    
                    // Timeout after 30 seconds
                    setTimeout(() => {
                        reject(new Error('Adsgram ad timeout'));
                    }, 30000);
                } else {
                    reject(new Error('Adsgram SDK not loaded'));
                }
            } catch (error) {
                console.error('Adsgram error:', error);
                reject(error);
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
                            reject(new Error('Richads ad failed: ' + error));
                        });
                    
                    // Timeout after 30 seconds
                    setTimeout(() => {
                        reject(new Error('Richads ad timeout'));
                    }, 30000);
                } else {
                    reject(new Error('Richads not initialized or SDK not loaded'));
                }
            } catch (error) {
                console.error('Richads error:', error);
                reject(error);
            }
        });
    },
    
    async show(placement, onComplete, isRetry = false) {
        await this.init();
        
        // Store for retry functionality
        this.currentPlacement = placement;
        this.currentCallback = onComplete;
        
        console.log(`🎬 AdManager: Requesting ad for placement: ${placement}`);
        
        // Show loading overlay
        this.showLoadingOverlay();
        
        try {
            // Get ad configuration from server
            const adConfig = await this.getAdConfig(placement);
            
            if (!adConfig) {
                console.error('⚠️ No ad config found for placement:', placement);
                this.hideLoadingOverlay();
                this.showErrorOverlay('No ad configuration found. Please setup ads in admin panel.');
                return; // DO NOT proceed without ad!
            }
            
            console.log(`📺 AdManager: Showing ${adConfig.network} ad...`);
            
            // Log impression
            await this.logEvent(placement, adConfig.ad_unit.id, 'impression');
            
            let adShown = false;
            let lastError = null;
            
            // Try primary ad
            try {
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
                        throw new Error('Unknown ad network: ' + adConfig.network);
                }
                
                adShown = true;
                
                // Log completion
                await this.logEvent(placement, adConfig.ad_unit.id, 'complete');
                
                console.log('✅ Ad completed successfully');
                
                // Hide loading and execute callback
                this.hideLoadingOverlay();
                
                if (onComplete) {
                    console.log('🎯 Executing post-ad callback...');
                    await onComplete();
                }
                
            } catch (error) {
                console.error('❌ Primary ad error:', error);
                lastError = error;
                
                // Try fallback ads
                if (adConfig.fallback && adConfig.fallback.length > 0) {
                    console.log('🔄 Trying fallback ads...');
                    
                    for (const fallback of adConfig.fallback) {
                        try {
                            console.log(`Trying fallback: ${fallback.network}`);
                            
                            switch (fallback.network) {
                                case 'adexium':
                                    await this.showAdexium(fallback.ad_unit);
                                    break;
                                case 'monetag':
                                    await this.showMonetag(fallback.ad_unit);
                                    break;
                                case 'adsgram':
                                    await this.showAdsgram(fallback.ad_unit);
                                    break;
                                case 'richads':
                                    await this.showRichads(fallback.ad_unit);
                                    break;
                            }
                            
                            adShown = true;
                            console.log('✅ Fallback ad completed successfully');
                            
                            // Hide loading and execute callback
                            this.hideLoadingOverlay();
                            
                            if (onComplete) {
                                await onComplete();
                            }
                            
                            break; // Exit loop if fallback succeeds
                            
                        } catch (fallbackError) {
                            console.error('❌ Fallback ad error:', fallbackError);
                            lastError = fallbackError;
                            continue; // Try next fallback
                        }
                    }
                }
            }
            
            // If no ad was shown successfully, show error
            if (!adShown) {
                this.hideLoadingOverlay();
                this.showErrorOverlay(
                    lastError?.message || 'Failed to load any advertisement. Please check your connection and try again.'
                );
                console.error('❌ All ads failed to load. User MUST watch ad to continue.');
                // DO NOT call onComplete - user must retry!
            }
            
        } catch (error) {
            console.error('❌ Critical ad error:', error);
            this.hideLoadingOverlay();
            this.showErrorOverlay(error.message || 'An error occurred while loading the ad');
            // DO NOT proceed without ad!
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
