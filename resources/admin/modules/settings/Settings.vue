<template>
    <div class="settings-wrapper">
        <!-- Top Navigation Bar -->
        <nav class="app-navbar">
            <div class="navbar-brand">
                <div class="brand-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div class="brand-text">
                    <h1 class="brand-title">Task Ledger</h1>
                    <p class="brand-subtitle">Settings</p>
                </div>
            </div>
        </nav>

        <!-- Main Content Area -->
        <div class="app-content">
            <!-- Show settings grid when on base /settings route -->
            <div v-if="$route.path === '/settings'" class="settings-content">
                <div class="settings-grid">
                    <router-link
                        to="/settings/email-notifications"
                        class="settings-card"
                    >
                        <div class="card-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="card-title">Email Notifications</h3>
                        <p class="card-description">Manage email notification templates and settings</p>
                    </router-link>
                </div>
            </div>
            <!-- Render child routes -->
            <router-view v-else />
        </div>
    </div>
</template>

<script>
export default {
    name: 'Settings',
};
</script>

<style lang="scss" scoped>
.settings-wrapper {
    min-height: 100vh;
    background: #f9fafb;
}

.app-navbar {
    background: white;
    border-bottom: 1px solid #e5e7eb;
    padding: 0.75rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.navbar-brand {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.brand-icon {
    width: 2rem;
    height: 2rem;
    color: #6366f1;
    display: flex;
    align-items: center;
    justify-content: center;
}

.brand-text {
    .brand-title {
        font-size: 1rem;
        font-weight: 600;
        color: #111827;
        margin: 0;
        line-height: 1.2;
    }

    .brand-subtitle {
        font-size: 0.75rem;
        color: #6b7280;
        margin: 0;
        line-height: 1.2;
    }
}

.app-content {
    padding: 1.5rem;
}

.settings-content {
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    padding: 2rem;
}

.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
}

.settings-card {
    display: flex;
    flex-direction: column;
    padding: 1.5rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    text-decoration: none;
    color: inherit;
    transition: all 0.2s ease;
    background: white;

    &:hover {
        border-color: #6366f1;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .card-icon {
        width: 3rem;
        height: 3rem;
        color: #6366f1;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #eef2ff;
        border-radius: 0.5rem;
    }

    .card-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #111827;
        margin: 0 0 0.5rem 0;
    }

    .card-description {
        font-size: 0.875rem;
        color: #6b7280;
        margin: 0;
        line-height: 1.5;
    }
}
</style>

