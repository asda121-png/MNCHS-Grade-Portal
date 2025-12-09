/**
 * Grade Entry Lock System - OPTIMIZED
 * Locks/unlocks grade input fields based on active grading period
 * 
 * Usage: Call GradeEntryLock.init() on page load
 */

class GradeEntryLock {
    constructor() {
        this.checkInterval = 60000; // Check every 60 seconds (increased from 30s)
        this.isGradingAllowed = false;
        this.currentPeriod = null;
        this.lastCheckTime = 0;
        this.cacheExpiry = 60000; // Cache for 60 seconds
        this.cachedData = null;
        this.isInitialized = false;
        this.minResponseDelay = 2000; // Minimum 2 seconds before showing status
        this.checkStartTime = 0;
    }

    /**
     * Initialize the grade entry lock system
     */
    async init() {
        if (this.isInitialized) return; // Prevent duplicate initialization
        this.isInitialized = true;
        
        console.log('[GradeEntryLock] Initializing...');
        
        // Check immediately on page load (non-blocking)
        this.checkGradingPeriod();
        
        // Setup periodic check (reduced frequency)
        this.setupPeriodicCheck();
        this.setupUI();
    }

    /**
     * Check if grading period is active with caching
     */
    checkGradingPeriod() {
        // Record when this check started
        this.checkStartTime = Date.now();
        
        const now = Date.now();
        
        // Use cache if still valid
        if (this.cachedData && (now - this.lastCheckTime) < this.cacheExpiry) {
            console.log('[GradeEntryLock] Using cached data');
            this.applyGradingStatus(this.cachedData);
            return;
        }

        // Fetch new data
        fetch('../../server/api/check_grading_period.php', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Calculate elapsed time
                const elapsedTime = Date.now() - this.checkStartTime;
                
                // If response came back faster than 2 seconds, wait
                const remainingDelay = Math.max(0, this.minResponseDelay - elapsedTime);
                
                if (remainingDelay > 0) {
                    console.log('[GradeEntryLock] Waiting ' + remainingDelay + 'ms to reach minimum 2s response time');
                    setTimeout(() => {
                        // Cache the result
                        this.cachedData = data;
                        this.lastCheckTime = now;
                        
                        // Apply the status
                        this.applyGradingStatus(data);
                    }, remainingDelay);
                } else {
                    // Already took 2+ seconds, apply immediately
                    // Cache the result
                    this.cachedData = data;
                    this.lastCheckTime = now;
                    
                    // Apply the status
                    this.applyGradingStatus(data);
                }
            }
        })
        .catch(error => {
            console.warn('[GradeEntryLock] Error checking grading period:', error);
            // Keep showing last known state on error
        });
    }

    /**
     * Apply grading status to UI
     */
    applyGradingStatus(data) {
        this.isGradingAllowed = data.allowed;
        this.currentPeriod = data.current_period || data.next_period;
        
        console.log('[GradeEntryLock] Grading allowed:', this.isGradingAllowed);
        
        this.updateGradeInputs();
        this.displayStatusMessage(data.message, data.allowed);
    }

    /**
     * Update all grade input fields (lock/unlock by quarter)
     */
    updateGradeInputs() {
        const inputs = document.querySelectorAll('input[name^="q"]');
        
        // Determine which quarter column is active based on current period
        const activeQuarter = this.isGradingAllowed ? this.getActiveQuarter() : null;
        
        inputs.forEach((input, index) => {
            // Determine which quarter this input belongs to (q1, q2, q3, q4)
            // The order in the table is Q1, Q2, Q3, Q4
            const quarterIndex = index % 4; // 0=Q1, 1=Q2, 2=Q3, 3=Q4
            const inputQuarter = quarterIndex + 1; // Convert to 1-based (1=Q1, 2=Q2, etc.)
            
            // Check if this quarter is active
            const isThisQuarterActive = activeQuarter === inputQuarter;
            
            if (isThisQuarterActive) {
                // Enable this quarter's inputs
                input.removeAttribute('disabled');
                input.style.opacity = '1';
                input.style.cursor = 'text';
                input.style.backgroundColor = '#ffffff';
                input.style.borderColor = '#28a745';
                input.style.boxShadow = '0 0 0 2px rgba(40, 167, 69, 0.1)';
                
                // Remove lock icon if exists
                const lockIcon = input.parentNode.querySelector('.quarter-lock');
                if (lockIcon) lockIcon.remove();
            } else {
                // Lock other quarters' inputs
                input.setAttribute('disabled', 'disabled');
                input.style.opacity = '0.5';
                input.style.cursor = 'not-allowed';
                input.style.backgroundColor = '#f0f0f0';
                input.style.borderColor = '#ddd';
                input.value = '';
                
                // Add lock icon if not already present
                if (!input.parentNode.querySelector('.quarter-lock')) {
                    const lockIcon = document.createElement('span');
                    lockIcon.className = 'quarter-lock';
                    lockIcon.style.cssText = `
                        position: absolute;
                        color: #e74c3c;
                        font-size: 12px;
                        margin-left: -20px;
                        cursor: not-allowed;
                    `;
                    lockIcon.innerHTML = '🔒';
                    lockIcon.title = 'This quarter is locked. Only Q' + inputQuarter + ' is available.';
                    input.parentNode.style.position = 'relative';
                    input.parentNode.appendChild(lockIcon);
                }
            }
        });

        // Also disable the save button if not in active quarter
        const saveBtn = document.querySelector('.btn-save');
        if (saveBtn) {
            if (this.isGradingAllowed && activeQuarter) {
                saveBtn.removeAttribute('disabled');
                saveBtn.style.opacity = '1';
                saveBtn.style.cursor = 'pointer';
            } else {
                saveBtn.setAttribute('disabled', 'disabled');
                saveBtn.style.opacity = '0.5';
                saveBtn.style.cursor = 'not-allowed';
            }
        }
    }

    /**
     * Determine which quarter is currently active
     * Returns: 1, 2, 3, or 4 based on the current grading period
     */
    getActiveQuarter() {
        if (this.currentPeriod && this.currentPeriod.quarter) {
            return parseInt(this.currentPeriod.quarter);
        }
        return null;
    }

    /**
     * Display status message as a top pop-up that disappears after 5 seconds
     */
    displayStatusMessage(message, isAllowed) {
        // Remove existing popup if any
        const existingPopup = document.getElementById('grading-status-popup');
        if (existingPopup) {
            existingPopup.remove();
        }

        // Create popup container
        const popup = document.createElement('div');
        popup.id = 'grading-status-popup';
        popup.style.cssText = `
            position: fixed;
            top: 90px;
            right: 30px;
            padding: 16px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            z-index: 5000;
            animation: slideInRight 0.4s ease-out;
            max-width: 350px;
        `;

        if (isAllowed) {
            popup.style.backgroundColor = '#d4edda';
            popup.style.color = '#155724';
            popup.style.border = '1px solid #c3e6cb';
            popup.innerHTML = '<i class="fas fa-check-circle"></i> Grade entry is now open';
        } else {
            popup.style.backgroundColor = '#f8d7da';
            popup.style.color = '#721c24';
            popup.style.border = '1px solid #f5c6cb';
            popup.innerHTML = '<i class="fas fa-lock"></i> Grade entry is locked';
        }

        // Add animation keyframes if not already present
        if (!document.getElementById('grading-popup-styles')) {
            const style = document.createElement('style');
            style.id = 'grading-popup-styles';
            style.textContent = `
                @keyframes slideInRight {
                    from {
                        transform: translateX(400px);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
                @keyframes slideOutRight {
                    from {
                        transform: translateX(0);
                        opacity: 1;
                    }
                    to {
                        transform: translateX(400px);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }

        // Add popup to page
        document.body.appendChild(popup);

        // Remove after 5 seconds
        setTimeout(() => {
            popup.style.animation = 'slideOutRight 0.4s ease-out';
            setTimeout(() => {
                popup.remove();
            }, 400);
        }, 5000);
    }

    /**
     * Setup periodic check for grading period changes
     */
    setupPeriodicCheck() {
        setInterval(() => {
            this.checkGradingPeriod();
        }, this.checkInterval);
    }

    /**
     * Setup UI elements
     */
    setupUI() {
        // Prevent form submission if grading not allowed
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', (e) => {
                if (!this.isGradingAllowed) {
                    e.preventDefault();
                    alert('Grade entry is currently locked. Please wait for an active grading period.');
                    return false;
                }
            });
        }
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    const gradeEntryLock = new GradeEntryLock();
    gradeEntryLock.init();
});
