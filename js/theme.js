/**
 * Theme Switcher Logic
 * Supports: Light, Dark, System
 */

const Theme = {
    LIGHT: 'light',
    DARK: 'dark',
    SYSTEM: 'system'
};

function getSystemTheme() {
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? Theme.DARK : Theme.LIGHT;
}

function applyTheme(theme) {
    const root = document.documentElement;
    const icon = document.getElementById('theme-icon');
    
    // Remove previous classes if any (optional, we use data attribute)
    
    let effectiveTheme = theme;
    if (theme === Theme.SYSTEM) {
        effectiveTheme = getSystemTheme();
    }
    
    root.setAttribute('data-theme', effectiveTheme);
    localStorage.setItem('theme', theme);
    
    // Update Icon
    if (icon) {
        icon.className = '';
        if (theme === Theme.LIGHT) icon.className = 'fa-solid fa-sun';
        else if (theme === Theme.DARK) icon.className = 'fa-solid fa-moon';
        else icon.className = 'fa-solid fa-desktop';
    }
}

function rotateTheme() {
    const current = localStorage.getItem('theme') || Theme.SYSTEM;
    let next = Theme.SYSTEM;
    
    if (current === Theme.SYSTEM) next = Theme.LIGHT;
    else if (current === Theme.LIGHT) next = Theme.DARK;
    else if (current === Theme.DARK) next = Theme.SYSTEM;
    
    applyTheme(next);
}

// Init
(function() {
    const saved = localStorage.getItem('theme') || Theme.SYSTEM;
    applyTheme(saved);
    
    // Listen for system changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
        if (localStorage.getItem('theme') === Theme.SYSTEM) {
            applyTheme(Theme.SYSTEM); // Re-apply system
        }
    });
})();
