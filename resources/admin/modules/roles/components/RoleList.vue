<template>
    <div class="role-list">
        <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <p>Loading roles...</p>
        </div>
        <div v-else-if="roles.length === 0" class="empty-state">
            <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <p>No roles found</p>
        </div>
        <div v-else class="roles-grid">
            <div 
                v-for="role in roles" 
                :key="role.id" 
                class="role-card"
                :class="`role-${role.slug}`"
            >
                <div class="role-card-header">
                    <div class="role-info">
                        <div class="role-title-row">
                            <h3 class="role-name">{{ role.name }}</h3>
                            <span v-if="role.is_system" class="system-badge">System</span>
                        </div>
                        <p v-if="role.description" class="role-description">{{ role.description }}</p>
                    </div>
                    <div class="role-stats">
                        <div class="stat-item">
                            <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span class="stat-value">{{ role.users_count || 0 }}</span>
                            <span class="stat-label">user{{ (role.users_count || 0) !== 1 ? 's' : '' }}</span>
                        </div>
                    </div>
                </div>
                <div class="role-actions">
                    <button @click="$emit('assign-users', role)" class="btn-action btn-assign" title="Assign Users">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Assign Users</span>
                    </button>
                    <button 
                        v-if="role.slug === 'manager'" 
                        @click="$emit('manage-members', role)" 
                        class="btn-action btn-manage" 
                        title="Manage Members"
                    >
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>Manage Members</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'RoleList',
    props: {
        roles: {
            type: Array,
            default: () => [],
        },
        loading: {
            type: Boolean,
            default: false,
        },
    },
    emits: ['assign-users', 'manage-members'],
};
</script>

<style lang="scss" scoped>
.role-list {
    width: 100%;
}

.loading-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #6b7280;

    .spinner {
        border: 3px solid #f3f3f3;
        border-top: 3px solid #6366f1;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        animation: spin 1s linear infinite;
        margin: 0 auto 1rem;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    p {
        margin: 0;
        font-size: 0.875rem;
    }
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #6b7280;

    .empty-icon {
        width: 3.5rem;
        height: 3.5rem;
        margin: 0 auto 1rem;
        opacity: 0.4;
        color: #9ca3af;
    }

    p {
        margin: 0;
        font-size: 0.875rem;
    }
}

.roles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1rem;
}

.role-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.625rem;
    padding: 1rem;
    transition: all 0.2s;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    display: flex;
    flex-direction: column;
    gap: 0.875rem;

    &:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        transform: translateY(-1px);
        border-color: #d1d5db;
    }

    &.role-admin {
        border-left: 3px solid #6366f1;
    }

    &.role-manager {
        border-left: 3px solid #10b981;
    }

    &.role-member {
        border-left: 3px solid #6b7280;
    }
}

.role-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
}

.role-info {
    flex: 1;
    min-width: 0;
}

.role-title-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.375rem;
    flex-wrap: wrap;
}

.role-name {
    font-size: 1rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
    letter-spacing: -0.025em;
}

.system-badge {
    display: inline-block;
    padding: 0.125rem 0.375rem;
    background: #f3f4f6;
    color: #6b7280;
    border-radius: 0.25rem;
    font-size: 0.625rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.role-description {
    font-size: 0.8125rem;
    color: #6b7280;
    margin: 0;
    line-height: 1.4;
}

.role-stats {
    flex-shrink: 0;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.625rem;
    background: #f9fafb;
    border-radius: 0.375rem;
    border: 1px solid #e5e7eb;
}

.stat-icon {
    width: 0.875rem;
    height: 0.875rem;
    color: #6b7280;
}

.stat-value {
    font-size: 0.875rem;
    font-weight: 600;
    color: #111827;
}

.stat-label {
    font-size: 0.75rem;
    color: #6b7280;
}

.role-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    padding-top: 0.5rem;
    border-top: 1px solid #f3f4f6;
}

.btn-action {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.5rem 0.875rem;
    border: none;
    border-radius: 0.375rem;
    font-size: 0.8125rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    flex: 1;
    justify-content: center;
    min-width: 0;

    svg {
        width: 0.875rem;
        height: 0.875rem;
        flex-shrink: 0;
    }

    span {
        white-space: nowrap;
    }

    &:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
}

.btn-assign {
    background: #6366f1;
    color: white;

    &:hover {
        background: #4f46e5;
    }
}

.btn-manage {
    background: #10b981;
    color: white;

    &:hover {
        background: #059669;
    }
}
</style>

