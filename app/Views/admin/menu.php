<?php /** @var string $slug */ ?>
<div id="<?php echo esc_attr($slug); ?>-app" class="warp fconnector_app">
    <div
        class="fframe_app"
        style="position: relative; width: 100%; min-height: 200px;"
    >
        <!-- Loading indicator centered on screen -->
        <div
            id="fluent-framework-loading"
            style="
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                background: rgba(255, 255, 255, 0.5);
                z-index: 9999;
            "
        >
            <!-- Gradient spinner SVG -->
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 200 200"
                width="60"
                height="60"
            >
                <radialGradient id="a5" cx=".66" fx=".66" cy=".3125" fy=".3125" gradientTransform="scale(1.5)">
                    <stop offset="0" stop-color="#888"></stop>
                    <stop offset=".3" stop-color="#888" stop-opacity=".9"></stop>
                    <stop offset=".6" stop-color="#888" stop-opacity=".6"></stop>
                    <stop offset=".8" stop-color="#888" stop-opacity=".3"></stop>
                    <stop offset="1" stop-color="#888" stop-opacity="0"></stop>
                </radialGradient>
                <circle
                    class="spinner-circle"
                    fill="none"
                    stroke="url(#a5)"
                    stroke-width="15"
                    stroke-linecap="round"
                    stroke-dasharray="200 1000"
                    stroke-dashoffset="0"
                    cx="100"
                    cy="100"
                    r="70"
                ></circle>
                <circle
                    fill="none"
                    opacity=".2"
                    stroke="#888"
                    stroke-width="15"
                    stroke-linecap="round"
                    cx="100"
                    cy="100"
                    r="70"
                ></circle>
            </svg>
            <div style="margin-top: 10px; font-size: 14px; color: #333;">Loading...</div>
        </div>

        <!-- Vue mounts here -->
        <div id="fluent-framework-app" style="display: none; width: 100%;"></div>
    </div>
</div>

<style>
/* eslint-disable */
.spinner-circle {
    transform-origin: 50% 50%;
    animation: spin 2s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}
/* eslint-enable */
</style>

<script>
(function() {
    const slug = '<?php echo esc_js($slug); ?>';
    
    // Modify submenu URLs to include hash fragments
    function modifySubmenuUrls() {
        const menuLinks = document.querySelectorAll(
            `#toplevel_page_${slug} .wp-submenu a`
        );
        
        menuLinks.forEach((link, index) => {
            const href = link.getAttribute('href');
            if (!href || !href.includes(`page=${slug}`)) return;
            
            // First link (Developers) -> /, Second link (Project Manager) -> /pm
            const route = index === 0 ? '/' : '/pm';
            const baseUrl = href.split('#')[0];
            link.setAttribute('href', baseUrl + '#' + route);
        });
    }
    
    // Intercept WordPress admin menu clicks to prevent page reload
    function interceptMenuClicks() {
        const menuLinks = document.querySelectorAll(
            `#toplevel_page_${slug} a, #toplevel_page_${slug} .wp-submenu a`
        );
        
        menuLinks.forEach(link => {
            // Remove existing listeners to avoid duplicates
            const newLink = link.cloneNode(true);
            link.parentNode.replaceChild(newLink, link);
            
            newLink.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (!href || !href.includes(`page=${slug}`)) return;
                
                e.preventDefault();
                e.stopPropagation();
                
                // Extract route from hash
                const hashMatch = href.match(/#(.+)$/);
                const route = hashMatch ? hashMatch[1] : '/';
                
                // Use Vue Router if available
                if (window.fluentFrameworkAdmin?.router) {
                    window.fluentFrameworkAdmin.router.push(route).catch(() => {
                        window.location.hash = route;
                    });
                } else {
                    // Update URL without reload
                    const newUrl = href.split('#')[0] + '#' + route;
                    window.history.pushState({}, '', newUrl);
                    window.location.hash = route;
                }
                
                // Update active menu state
                menuLinks.forEach(l => {
                    if (l !== newLink) {
                        l.parentElement?.classList.remove('current');
                    }
                });
                newLink.parentElement?.classList.add('current');
                
                return false;
            });
        });
    }
    
    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            modifySubmenuUrls();
            interceptMenuClicks();
        });
    } else {
        modifySubmenuUrls();
        interceptMenuClicks();
    }
    
    // Also run after a delay to catch dynamically added menus
    setTimeout(() => {
        modifySubmenuUrls();
        interceptMenuClicks();
    }, 100);
})();

document.addEventListener('DOMContentLoaded', () => {
    const app = document.querySelector('#fluent-framework-app');
    const loading = document.querySelector('#fluent-framework-loading');

    setTimeout(() => {
        loading.style.display = 'none';
        app.style.display = 'block';
    }, 500);
});
</script>
