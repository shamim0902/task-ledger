<template>
    <div class="role-list">
        <div v-if="loading" class="loading-state">
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
            <div v-for="role in roles" :key="role.id" class="role-card">
                <div class="role-card-header">
                    <div class="role-info">
                        <h3 class="role-name">{{ role.name }}</h3>
                        <span v-if="role.is_system" class="system-badge">System</span>
                    </div>
                    <div class="role-actions">
                        <button @click="$emit('manage-permissions', role)" class="btn-icon" title="Manage Permissions">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </button>
                        <button @click="$emit('assign-users', role)" class="btn-icon" title="Assign Users">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </button>
                        <button v-if="!role.is_system" @click="$emit('edit', role)" class="btn-icon" title="Edit">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                        <button v-if="!role.is_system" @click="$emit('delete', role)" class="btn-icon btn-danger" title="Delete">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
                <p v-if="role.description" class="role-description">{{ role.description }}</p>
                <div class="role-permissions">
                    <span class="permissions-count">{{ role.permissions?.length || 0 }} permission{{ (role.permissions?.length || 0) !== 1 ? 's' : '' }}</span>
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
    emits: ['edit', 'delete', 'manage-permissions', 'assign-users'],
};
</script>

<style lang="scss" scoped>
.role-list {
    width: 100%;
}

.loading-state,
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #6b7280;

    .empty-icon {
        width: 4rem;
        height: 4rem;
        margin: 0 auto 1rem;
        opacity: 0.4;
        color: #9ca3af;
    }

    p {
        margin: 0;
        font-size: 0.9375rem;
    }
}

.roles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1rem;
}

.role-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    padding: 1.25rem;
    transition: all 0.2s;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);

    &:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }
}

.role-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.75rem;
    gap: 1rem;
}

.role-info {
    flex: 1;
    min-width: 0;
}

.role-name {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 0.5rem 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.system-badge {
    display: inline-block;
    padding: 0.125rem 0.5rem;
    background: #eef2ff;
    color: #6366f1;
    border-radius: 0.25rem;
    font-size: 0.6875rem;
    font-weight: 600;
}

.role-actions {
    display: flex;
    gap: 0.5rem;
    flex-shrink: 0;
}

.btn-icon {
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s;

    svg {
        width: 1rem;
        height: 1rem;
    }

    &:hover {
        background: #f3f4f6;
        border-color: #d1d5db;
        color: #374151;
    }

    &.btn-danger:hover {
        background: #fee2e2;
        border-color: #fecaca;
        color: #dc2626;
    }
}

.role-description {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0 0 0.75rem 0;
    line-height: 1.5;
}

.role-permissions {
    padding-top: 0.75rem;
    border-top: 1px solid #e5e7eb;
}

.permissions-count {
    font-size: 0.8125rem;
    color: #9ca3af;
    font-weight: 500;
}
</style>

