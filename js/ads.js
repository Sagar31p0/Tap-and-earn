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
            // Wait for ad SDKs to load
            await this.waitForSDKs();
            
            // Initialize Richads
            if (window.TelegramAdsController) {
                try {
                    this.networks.richads = new TelegramAdsController();
                    this.networks.richads.initialize({
                        pubId: "820238",
                        appId: "4130"
                    });
                    console.log('✅ Richads initialized');
                } catch (error) {
                    console.error('❌ Richads initialization failed:', error);
                }
            } else {
                console.warn('⚠️ Richads SDK not available');
            }
            
            // Check Adsgram SDK
            if (window.Adsgram) {
                console.log('✅ Adsgram SDK available');
            } else {
                console.warn('⚠️ Adsgram SDK not available');
            }
            
            // Check Adexium SDK
            if (window.AdexiumWidget) {
                console.log('✅ Adexium SDK available');
            } else {
                console.warn('⚠️ Adexium SDK not available');
            }
            
            // Check Monetag SDK
            if (typeof show_10113890 === 'function') {
                console.log('✅ Monetag SDK available');
            } else {
                console.warn('⚠️ Monetag SDK not available');
            }
            
            this.initialized = true;
            console.log('🎬 AdManager initialized successfully');
        } catch (error) {
            console.error('❌ Ad initialization error:', error);
            this.initialized = true; // Still mark as initialized to allow attempts
        }
    },
    
    async waitForSDKs() {
        // Wait up to 5 seconds for SDKs to load
        const maxWait = 5000;
        const startTime = Date.now();
        
        return new Promise((resolve) => {
            const checkSDKs = () => {
                const adsgramReady = !!window.Adsgram;
                const adexiumReady = !!window.AdexiumWidget;
                const monetagReady = typeof show_10113890 === 'function';
                const richadsReady = !!window.TelegramAdsController;
                
                if (adsgramReady && adexiumReady && monetagReady && richadsReady) {
                    console.log('✅ All ad SDKs loaded');
                    resolve();
                } else if (Date.now() - startTime >= maxWait) {
                    console.warn('⚠️ Timeout waiting for ad SDKs. Available:', {
                        adsgram: adsgramReady,
                        adexium: adexiumReady,
                        monetag: monetagReady,
                        richads: richadsReady
                    });
                    resolve(); // Proceed anyway
                } else {
                    setTimeout(checkSDKs, 100);
                }
            };
            
            checkSDKs();
        });
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
            console.log(`📞 Fetching ad config for placement: ${placement}`);
            const response = await fetch(`${API_URL}/ads.php?placement=${placement}&user_id=${userData.id}`);
            
            if (!response.ok) {
                console.error(`❌ HTTP error: ${response.status} ${response.statusText}`);
                return null;
            }
            
            const data = await response.json();
            console.log('📦 Ad config received:', data);
            
            if (data.success) {
                return data;
            } else {
                console.error('❌ Ad config error:', data.error);
                return null;
            }
        } catch (error) {
            console.error('❌ Get ad config error:', error);
            return null;
        }
    },
    
    async showAdexium(adUnit) {
        return new Promise((resolve, reject) => {
            try {
                console.log('🎬 Attempting to show Adexium ad:', adUnit);
                
                if (typeof AdexiumWidget !== 'undefined') {
                    let adCompleted = false;
                    let widgetId = adUnit.id;
                    
                    // Extract widget ID if it's embedded in code
                    if (widgetId && widgetId.includes('wid:')) {
                        const match = widgetId.match(/wid:\s*['"]([^'"]+)['"]/i);
                        if (match && match[1]) {
                            widgetId = match[1];
                            console.log('📝 Extracted Adexium widget ID:', widgetId);
                        }
                    }
                    
                    const widget = new AdexiumWidget({
                        wid: widgetId,
                        adFormat: adUnit.type || 'interstitial',
                        onComplete: () => {
                            console.log('✅ Adexium ad completed');
                            adCompleted = true;
                            resolve();
                        },
                        onError: (error) => {
                            console.error('❌ Adexium error:', error);
                            if (!adCompleted) {
                                reject(new Error('Adexium ad failed to load: ' + error));
                            }
                        },
                        onClose: () => {
                            console.log('🚪 Adexium ad closed');
                            if (adCompleted) {
                                resolve();
                            } else {
                                reject(new Error('Adexium ad closed without completion'));
                            }
                        }
                    });
                    
                    widget.show();
                    console.log('📺 Adexium ad show() called');
                    
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
                console.error('❌ Adexium critical error:', error);
                reject(error);
            }
        });
    },
    
    async showMonetag(adUnit) {
        return new Promise((resolve, reject) => {
            try {
                console.log('🎬 Attempting to show Monetag ad:', adUnit);
                
                if (typeof show_10113890 === 'function') {
                    let adCompleted = false;
                    
                    // Determine the ad type based on unit configuration
                    const adType = adUnit.type || 'inApp';
                    
                    if (adType === 'rewarded') {
                        // Rewarded ad format
                        show_10113890().then(() => {
                            console.log('✅ Monetag rewarded ad completed');
                            adCompleted = true;
                            resolve();
                        }).catch((error) => {
                            console.error('❌ Monetag rewarded error:', error);
                            if (!adCompleted) {
                                reject(new Error('Monetag rewarded ad failed: ' + error));
                            }
                        });
                    } else if (adType === 'interstitial' || adType === 'inApp') {
                        // In-App Interstitial format
                        show_10113890({
                            type: 'inApp',
                            inAppSettings: {
                                frequency: 1,
                                capping: 0,
                                interval: 0,
                                timeout: 0,
                                everyPage: false
                            }
                        }).then(() => {
                            console.log('✅ Monetag interstitial ad completed');
                            adCompleted = true;
                            resolve();
                        }).catch((error) => {
                            console.error('❌ Monetag interstitial error:', error);
                            if (!adCompleted) {
                                reject(new Error('Monetag interstitial ad failed: ' + error));
                            }
                        });
                    } else {
                        // Default to rewarded popup
                        show_10113890('pop').then(() => {
                            console.log('✅ Monetag popup ad completed');
                            adCompleted = true;
                            resolve();
                        }).catch((error) => {
                            console.error('❌ Monetag popup error:', error);
                            if (!adCompleted) {
                                reject(new Error('Monetag popup ad failed: ' + error));
                            }
                        });
                    }
                    
                    console.log('📺 Monetag ad show() called with type:', adType);
                    
                    // Timeout after 30 seconds
                    setTimeout(() => {
                        if (!adCompleted) {
                            reject(new Error('Monetag ad timeout'));
                        }
                    }, 30000);
                } else {
                    reject(new Error('Monetag SDK not loaded'));
                }
            } catch (error) {
                console.error('❌ Monetag critical error:', error);
                reject(error);
            }
        });
    },
    
    async showAdsgram(adUnit) {
        return new Promise((resolve, reject) => {
            try {
                console.log('🎬 Attempting to show Adsgram ad:', adUnit);
                
                // Adsgram SDK for Telegram Mini Apps
                if (window.Adsgram) {
                    let adCompleted = false;
                    let blockId = adUnit.id;
                    
                    // Clean up block ID (remove prefixes like 'int-', 'task-', etc.)
                    if (blockId && typeof blockId === 'string') {
                        // Remove common prefixes
                        blockId = blockId.replace(/^(int-|task-|reward-)/, '');
                        console.log('📝 Cleaned Adsgram block ID:', blockId);
                    }
                    
                    const AdController = window.Adsgram.init({ blockId: blockId });
                    console.log('📺 Adsgram controller initialized for block:', blockId);
                    
                    AdController.show().then(() => {
                        console.log('✅ Adsgram ad completed:', blockId);
                        adCompleted = true;
                        resolve();
                    }).catch((error) => {
                        console.error('❌ Adsgram error:', error);
                        if (!adCompleted) {
                            reject(new Error('Adsgram ad failed: ' + (error.message || error)));
                        }
                    });
                    
                    // Timeout after 30 seconds
                    setTimeout(() => {
                        if (!adCompleted) {
                            console.warn('⏱️ Adsgram ad timeout');
                            reject(new Error('Adsgram ad timeout'));
                        }
                    }, 30000);
                } else {
                    reject(new Error('Adsgram SDK not loaded'));
                }
            } catch (error) {
                console.error('❌ Adsgram critical error:', error);
                reject(error);
            }
        });
    },
    
    async showRichads(adUnit) {
        return new Promise((resolve, reject) => {
            try {
                console.log('🎬 Attempting to show Richads ad:', adUnit);
                
                if (this.networks.richads && window.TelegramAdsController) {
                    let adCompleted = false;
                    
                    // Show based on unit type
                    let unitId = adUnit.id;
                    
                    // Clean up unit ID (remove # prefix if present)
                    if (typeof unitId === 'string') {
                        unitId = unitId.replace('#', '');
                    }
                    unitId = parseInt(unitId);
                    
                    console.log('📝 Richads unit ID:', unitId, 'Type:', adUnit.type);
                    
                    // Use Richads SDK
                    this.networks.richads.showAd(unitId)
                        .then(() => {
                            console.log('✅ Richads ad completed');
                            adCompleted = true;
                            resolve();
                        })
                        .catch((error) => {
                            console.error('❌ Richads display error:', error);
                            if (!adCompleted) {
                                reject(new Error('Richads ad failed: ' + (error.message || error)));
                            }
                        });
                    
                    console.log('📺 Richads showAd() called');
                    
                    // Timeout after 30 seconds
                    setTimeout(() => {
                        if (!adCompleted) {
                            console.warn('⏱️ Richads ad timeout');
                            reject(new Error('Richads ad timeout'));
                        }
                    }, 30000);
                } else {
                    const error = !this.networks.richads ? 'Richads not initialized' : 'TelegramAdsController SDK not loaded';
                    console.error('❌', error);
                    reject(new Error(error));
                }
            } catch (error) {
                console.error('❌ Richads critical error:', error);
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
                const errorMessage = lastError?.message || 'Failed to load any advertisement';
                console.error('❌ All ads failed to load:', errorMessage);
                console.error('📊 Primary network:', adConfig?.network);
                console.error('📊 Fallback networks:', adConfig?.fallback?.map(f => f.network).join(', ') || 'None');
                
                this.showErrorOverlay(
                    errorMessage + '. Please check your connection and try again.'
                );
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
