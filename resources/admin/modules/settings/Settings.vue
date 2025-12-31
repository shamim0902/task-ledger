<template>
    <div class="settings-wrapper">
        <!-- Compact Header -->
        <div class="settings-header">
            <div class="header-content">
                <div class="header-brand">
                    <div class="brand-icon">
                        <SettingIcon />
                    </div>
                    <div class="brand-text">
                        <h1 class="brand-title">Settings</h1>
                        <p class="brand-subtitle">Manage your Task Ledger configuration</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="settings-content-wrapper">
            <!-- Show settings grid when on base /settings route -->
            <div v-if="$route.path === '/settings'" class="settings-grid-container">
                <div class="settings-grid">
                    <router-link
                        to="/settings/roles"
                        class="settings-card"
                    >
                        <div class="card-icon-wrapper">
                            <div class="card-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="card-content">
                            <h3 class="card-title">Roles & Permissions</h3>
                            <p class="card-description">Manage user roles, permissions, and team assignments</p>
                        </div>
                        <div class="card-arrow">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </router-link>
                    
                    <router-link
                        to="/settings/email-notifications"
                        class="settings-card"
                    >
                        <div class="card-icon-wrapper">
                            <div class="card-icon email-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                        <div class="card-content">
                            <h3 class="card-title">Email Notifications</h3>
                            <p class="card-description">Configure email templates and notification preferences</p>
                        </div>
                        <div class="card-arrow">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </router-link>
                </div>
            </div>
            
            <!-- Render child routes with breadcrumb -->
            <div v-else class="settings-child-wrapper">
                <div class="breadcrumb-nav">
                    <router-link to="/settings" class="breadcrumb-link">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Settings</span>
                    </router-link>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-current">{{ currentPageTitle }}</span>
                </div>
                <router-view />
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Settings',
    components: {
        SettingIcon: {
            template: `
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 22 22"
                    fill="none"
                >
                    <path
                        d="M20.3175 6.14139L19.8239 5.28479C19.4506 4.63696 19.264 4.31305 18.9464 4.18388C18.6288 4.05472 18.2696 4.15664 17.5513 4.36048L16.3311 4.70418C15.8725 4.80994 15.3913 4.74994 14.9726 4.53479L14.6357 4.34042C14.2766 4.11043 14.0004 3.77133 13.8475 3.37274L13.5136 2.37536C13.294 1.71534 13.1842 1.38533 12.9228 1.19657C12.6615 1.00781 12.3143 1.00781 11.6199 1.00781H10.5051C9.81078 1.00781 9.4636 1.00781 9.20223 1.19657C8.94085 1.38533 8.83106 1.71534 8.61149 2.37536L8.27753 3.37274C8.12465 3.77133 7.84845 4.11043 7.48937 4.34042L7.15249 4.53479C6.73374 4.74994 6.25259 4.80994 5.79398 4.70418L4.57375 4.36048C3.85541 4.15664 3.49625 4.05472 3.17867 4.18388C2.86109 4.31305 2.67445 4.63696 2.30115 5.28479L1.80757 6.14139C1.45766 6.74864 1.2827 7.05227 1.31666 7.37549C1.35061 7.69871 1.58483 7.95918 2.05326 8.48012L3.0843 9.63282C3.3363 9.95185 3.51521 10.5078 3.51521 11.0077C3.51521 11.5078 3.33636 12.0636 3.08433 12.3827L2.05326 13.5354C1.58483 14.0564 1.35062 14.3168 1.31666 14.6401C1.2827 14.9633 1.45766 15.2669 1.80757 15.8741L2.30114 16.7307C2.67443 17.3785 2.86109 17.7025 3.17867 17.8316C3.49625 17.9608 3.85542 17.8589 4.57377 17.655L5.79394 17.3113C6.25263 17.2055 6.73387 17.2656 7.15267 17.4808L7.4895 17.6752C7.84851 17.9052 8.12464 18.2442 8.2775 18.6428L8.61149 19.6403C8.83106 20.3003 8.94085 20.6303 9.20223 20.8191C9.4636 21.0078 9.81078 21.0078 10.5051 21.0078H11.6199C12.3143 21.0078 12.6615 21.0078 12.9228 20.8191C13.1842 20.6303 13.294 20.3003 13.5136 19.6403L13.8476 18.6428C14.0004 18.2442 14.2765 17.9052 14.6356 17.6752L14.9724 17.4808C15.3912 17.2656 15.8724 17.2055 16.3311 17.3113L17.5513 17.655C18.2696 17.8589 18.6288 17.9608 18.9464 17.8316C19.264 17.7025 19.4506 17.3785 19.8239 16.7307L20.3175 15.8741C20.6674 15.2669 20.8423 14.9633 20.8084 14.6401C20.7744 14.3168 20.5402 14.0564 20.0718 13.5354L19.0407 12.3827C18.7887 12.0636 18.6098 11.5078 18.6098 11.0077C18.6098 10.5078 18.7888 9.95185 19.0407 9.63282L20.0718 8.48012C20.5402 7.95918 20.7744 7.69871 20.8084 7.37549C20.8423 7.05227 20.6674 6.74864 20.3175 6.14139Z"
                        stroke="currentColor"
                        stroke-width="1.5"
                        stroke-linecap="round"
                    />
                    <path
                        d="M14.5195 11C14.5195 12.933 12.9525 14.5 11.0195 14.5C9.08653 14.5 7.51953 12.933 7.51953 11C7.51953 9.067 9.08653 7.5 11.0195 7.5C12.9525 7.5 14.5195 9.067 14.5195 11Z"
                        stroke="currentColor"
                        stroke-width="1.5"
                    />
                </svg>
            `
        }
    },
    computed: {
        currentPageTitle() {
            const route = this.$route.path;
            if (route.includes('roles')) return 'Roles & Permissions';
            if (route.includes('email-notifications')) return 'Email Notifications';
            return 'Settings';
        }
    }
};
</script>

<style lang="scss" scoped>
.settings-wrapper {
    min-height: 100vh;
    background: #f8fafc;
}

.settings-header {
    background: white;
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 1rem;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.header-content {
    max-width: 1800px;
    margin: 0 auto;
    padding: 0.625rem 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.header-brand {
    display: flex;
    align-items: center;
    gap: 0.5rem;

    .brand-icon {
        width: 2rem;
        height: 2rem;
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 2px 4px -1px rgba(139, 92, 246, 0.3);
        flex-shrink: 0;
        padding: 0.5rem;

        svg {
            width: 100%;
            height: 100%;
        }
    }

    .brand-text {
        .brand-title {
            font-size: 0.9375rem;
            font-weight: 700;
            color: #111827;
            margin: 0;
            line-height: 1.2;
        }

        .brand-subtitle {
            font-size: 0.6875rem;
            color: #6b7280;
            margin: 0;
            font-weight: 500;
        }
    }
}

.settings-content-wrapper {
    max-width: 1800px;
    margin: 0 auto;
    padding: 0 1rem 1rem;
}

.settings-grid-container {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
}

.settings-card {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    padding: 1.5rem;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    text-decoration: none;
    color: inherit;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;

    &::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.3s ease;
    }

    &:hover {
        border-color: #667eea;
        box-shadow: 0 10px 25px -5px rgba(102, 126, 234, 0.15), 0 4px 6px -2px rgba(102, 126, 234, 0.1);
        transform: translateY(-2px);

        &::before {
            transform: scaleX(1);
        }

        .card-icon-wrapper {
            transform: scale(1.05);
        }

        .card-arrow {
            opacity: 1;
            transform: translateX(4px);
        }
    }

    &:active {
        transform: translateY(0);
    }
}

.card-icon-wrapper {
    flex-shrink: 0;
    transition: transform 0.2s ease;
}

.card-icon {
    width: 3rem;
    height: 3rem;
    color: #667eea;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #eef2ff 0%, #f3e8ff 100%);
    border-radius: 0.625rem;
    padding: 0.75rem;

    svg {
        width: 100%;
        height: 100%;
    }

    &.email-icon {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #d97706;
    }
}

.card-content {
    flex: 1;
    min-width: 0;
}

.card-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 0.375rem 0;
    line-height: 1.3;
    letter-spacing: -0.01em;
}

.card-description {
    font-size: 0.875rem;
    color: #64748b;
    margin: 0;
    line-height: 1.5;
}

.card-arrow {
    flex-shrink: 0;
    width: 1.25rem;
    height: 1.25rem;
    color: #94a3b8;
    opacity: 0;
    transform: translateX(0);
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;

    svg {
        width: 100%;
        height: 100%;
    }
}

.settings-child-wrapper {
    animation: fadeIn 0.3s ease-in;
}

.breadcrumb-nav {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    padding: 0.75rem 1rem;
    background: white;
    border-radius: 0.5rem;
    border: 1px solid #e2e8f0;
    font-size: 0.875rem;
}

.breadcrumb-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #64748b;
    text-decoration: none;
    transition: color 0.2s ease;

    svg {
        width: 1rem;
        height: 1rem;
    }

    &:hover {
        color: #667eea;
    }
}

.breadcrumb-separator {
    color: #cbd5e1;
}

.breadcrumb-current {
    color: #1e293b;
    font-weight: 500;
}

// Responsive design
@media (max-width: 768px) {
    .header-content {
        padding: 0.625rem 0.75rem;
    }

    .header-brand {
        .brand-icon {
            width: 1.75rem;
            height: 1.75rem;
            padding: 0.375rem;
        }

        .brand-text {
            .brand-title {
                font-size: 0.875rem;
            }

            .brand-subtitle {
                font-size: 0.625rem;
            }
        }
    }

    .settings-content-wrapper {
        padding: 1.5rem 1rem;
    }

    .settings-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .settings-card {
        padding: 1.25rem;
        gap: 1rem;
    }

    .card-icon {
        width: 2.5rem;
        height: 2.5rem;
        padding: 0.625rem;
    }

    .card-title {
        font-size: 1rem;
    }

    .card-description {
        font-size: 0.8125rem;
    }
}
</style>
