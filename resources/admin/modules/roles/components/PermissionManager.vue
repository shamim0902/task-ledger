<template>
    <div class="modal-overlay" @click.self="$emit('close')">
        <div class="modal-content permission-modal">
            <div class="modal-header">
                <h3 class="modal-title">Manage Permissions: {{ role.name }}</h3>
                <button @click="$emit('close')" class="modal-close">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="modal-body">
                <div v-if="loading" class="loading-state">
                    <p>Loading permissions...</p>
                </div>
                <div v-else class="permissions-groups">
                    <div v-for="(groupPermissions, group) in groupedPermissions" :key="group" class="permission-group">
                        <h4 class="group-title">{{ formatGroupName(group) }}</h4>
                        <div class="permissions-list">
                            <label
                                v-for="permission in groupPermissions"
                                :key="permission.id"
                                class="permission-item"
                            >
                                <input
                                    type="checkbox"
                                    :value="permission.id"
                                    v-model="selectedPermissions"
                                    class="permission-checkbox"
                                />
                                <div class="permission-info">
                                    <span class="permission-name">{{ permission.name }}</span>
                                    <span v-if="permission.description" class="permission-description">
                                        {{ permission.description }}
                                    </span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" @click="$emit('close')" class="btn-cancel">Cancel</button>
                <button type="button" @click="handleSave" class="btn-primary">Save Permissions</button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'PermissionManager',
    props: {
        role: {
            type: Object,
            required: true,
        },
        permissions: {
            type: Object,
            default: () => ({}),
        },
    },
    emits: ['close', 'save'],
    data() {
        return {
            loading: false,
            selectedPermissions: [],
        };
    },
    computed: {
        groupedPermissions() {
            if (!this.permissions || typeof this.permissions !== 'object') {
                return {};
            }

            // If permissions is already grouped
            if (this.permissions.grouped) {
                return this.permissions.grouped;
            }

            // If permissions is an array, group it
            if (Array.isArray(this.permissions)) {
                const grouped = {};
                this.permissions.forEach(perm => {
                    if (!grouped[perm.group]) {
                        grouped[perm.group] = [];
                    }
                    grouped[perm.group].push(perm);
                });
                return grouped;
            }

            return this.permissions;
        },
    },
    mounted() {
        this.loadRolePermissions();
    },
    methods: {
        async loadRolePermissions() {
            this.loading = true;
            try {
                const response = await this.$get(`roles/${this.role.id}/permissions`);
                if (response.permissions) {
                    this.selectedPermissions = response.permissions.map(p => p.id);
                }
            } catch (error) {
                console.error('Error loading role permissions:', error);
            } finally {
                this.loading = false;
            }
        },
        formatGroupName(group) {
            return group.charAt(0).toUpperCase() + group.slice(1).replace(/_/g, ' ');
        },
        handleSave() {
            this.$emit('save', this.selectedPermissions);
        },
    },
};
</script>

<style lang="scss" scoped>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 1rem;
}

.permission-modal {
    max-width: 700px;
    max-height: 90vh;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.modal-close {
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f9fafb;
    border: none;
    border-radius: 0.375rem;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s;

    svg {
        width: 1.25rem;
        height: 1.25rem;
    }

    &:hover {
        background: #f3f4f6;
        color: #111827;
    }
}

.modal-body {
    padding: 1.5rem;
    max-height: calc(90vh - 200px);
    overflow-y: auto;
}

.loading-state {
    text-align: center;
    padding: 2rem;
    color: #6b7280;
}

.permissions-groups {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.permission-group {
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 1rem;
    background: #f9fafb;
}

.group-title {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #374151;
    margin: 0 0 0.75rem 0;
    text-transform: capitalize;
}

.permissions-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.permission-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.75rem;
    background: white;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: all 0.2s;

    &:hover {
        background: #f9fafb;
    }
}

.permission-checkbox {
    width: 1.125rem;
    height: 1.125rem;
    margin-top: 0.125rem;
    cursor: pointer;
    flex-shrink: 0;
}

.permission-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.permission-name {
    font-size: 0.875rem;
    font-weight: 500;
    color: #111827;
}

.permission-description {
    font-size: 0.75rem;
    color: #6b7280;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1.25rem 1.5rem;
    border-top: 1px solid #e5e7eb;
}

.btn-cancel {
    padding: 0.625rem 1.25rem;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-weight: 500;
    font-size: 0.875rem;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s;

    &:hover {
        background: #f9fafb;
        border-color: #9ca3af;
    }
}

.btn-primary {
    padding: 0.625rem 1.25rem;
    background: #6366f1;
    border: none;
    border-radius: 0.5rem;
    font-weight: 500;
    font-size: 0.875rem;
    color: white;
    cursor: pointer;
    transition: all 0.2s;

    &:hover {
        background: #4f46e5;
    }
}
</style>

