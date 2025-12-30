<template>
    <div class="modal-overlay" @click.self="$emit('close')">
        <div class="modal-content role-assignment-modal">
            <div class="modal-header">
                <h3 class="modal-title">Assign Role to Project: {{ project?.title }}</h3>
                <button @click="$emit('close')" class="modal-close">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Select Role *</label>
                    <select v-model="selectedRoleId" class="form-select">
                        <option value="">Choose a role...</option>
                        <option
                            v-for="role in availableRoles"
                            :key="role.id"
                            :value="role.id"
                        >
                            {{ role.name }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Select Users *</label>
                    <div class="multiselect-wrapper">
                        <div class="multiselect-input" @click="toggleDropdown">
                            <div v-if="selectedUserIds.length === 0" class="placeholder">
                                Choose users...
                            </div>
                            <div v-else class="selected-count">
                                {{ selectedUserIds.length }} user(s) selected
                            </div>
                            <svg class="dropdown-icon" :class="{ open: showDropdown }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                        <div v-if="showDropdown" class="multiselect-dropdown">
                            <div class="dropdown-header">
                                <input
                                    v-model="userSearchQuery"
                                    type="text"
                                    placeholder="Search users..."
                                    class="search-input"
                                    @click.stop
                                />
                            </div>
                            <div class="dropdown-list">
                                <label
                                    v-for="user in filteredUsers"
                                    :key="user.ID || user.id"
                                    class="checkbox-item"
                                    @click.stop
                                >
                                    <input
                                        type="checkbox"
                                        :value="user.ID || user.id"
                                        v-model="selectedUserIds"
                                        @click.stop
                                    />
                                    <div class="user-info">
                                        <span class="user-name">
                                            {{ user.display_name || user.user_nicename || user.user_email }}
                                        </span>
                                        <span class="user-email">{{ user.user_email }}</span>
                                    </div>
                                </label>
                                <div v-if="filteredUsers.length === 0" class="empty-dropdown">
                                    No users found
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="selectedUserIds.length > 0" class="selected-users">
                        <div
                            v-for="userId in selectedUserIds"
                            :key="userId"
                            class="selected-user-tag"
                        >
                            {{ getUserName(userId) }}
                            <button
                                @click="removeUser(userId)"
                                class="remove-btn"
                                type="button"
                            >
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-hint">
                    <svg class="hint-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>This role will be assigned to selected users specifically for this project.</span>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" @click="$emit('close')" class="btn-cancel">Cancel</button>
                <button
                    @click="handleAssign"
                    class="btn-primary"
                    :disabled="selectedUserIds.length === 0 || !selectedRoleId || assigning"
                >
                    {{ assigning ? 'Assigning...' : `Assign Role to ${selectedUserIds.length} User(s)` }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ProjectRoleAssignment',
    props: {
        project: {
            type: Object,
            required: true,
        },
        availableRoles: {
            type: Array,
            default: () => [],
        },
        availableUsers: {
            type: Array,
            default: () => [],
        },
    },
    emits: ['close', 'assigned'],
    data() {
        return {
            selectedUserIds: [],
            selectedRoleId: '',
            assigning: false,
            showDropdown: false,
            userSearchQuery: '',
        };
    },
    computed: {
        filteredUsers() {
            if (!this.userSearchQuery) {
                return this.availableUsers;
            }
            const query = this.userSearchQuery.toLowerCase();
            return this.availableUsers.filter(user => {
                const name = (user.display_name || user.user_nicename || user.user_email || '').toLowerCase();
                const email = (user.user_email || '').toLowerCase();
                return name.includes(query) || email.includes(query);
            });
        },
    },
    mounted() {
        document.addEventListener('click', this.handleClickOutside);
    },
    beforeUnmount() {
        document.removeEventListener('click', this.handleClickOutside);
    },
    methods: {
        toggleDropdown() {
            this.showDropdown = !this.showDropdown;
        },
        handleClickOutside(event) {
            if (!this.$el.contains(event.target)) {
                this.showDropdown = false;
            }
        },
        removeUser(userId) {
            this.selectedUserIds = this.selectedUserIds.filter(id => id !== userId);
        },
        getUserName(userId) {
            const user = this.availableUsers.find(u => (u.ID || u.id) === userId);
            if (user) {
                return user.display_name || user.user_nicename || user.user_email || `User #${userId}`;
            }
            return `User #${userId}`;
        },
        async handleAssign() {
            if (this.selectedUserIds.length === 0 || !this.selectedRoleId) {
                this.$notify({
                    type: 'warning',
                    text: 'Please select at least one user and a role',
                });
                return;
            }

            this.assigning = true;
            const results = {
                success: [],
                errors: [],
            };

            try {
                // Assign role to all selected users
                const assignments = await Promise.allSettled(
                    this.selectedUserIds.map(userId =>
                        this.$post('user-roles/assign', {
                            user_id: userId,
                            role_id: this.selectedRoleId,
                            board_id: this.project.id,
                        })
                    )
                );

                assignments.forEach((result, index) => {
                    if (result.status === 'fulfilled' && !result.value.error) {
                        results.success.push({
                            userId: this.selectedUserIds[index],
                            assignment: result.value.assignment,
                        });
                    } else {
                        const error = result.status === 'rejected' 
                            ? result.reason?.message || 'Failed to assign role'
                            : result.value.error || 'Failed to assign role';
                        results.errors.push({
                            userId: this.selectedUserIds[index],
                            error: error,
                        });
                    }
                });

                // Show notification based on results
                if (results.success.length > 0 && results.errors.length === 0) {
                    this.$notify({
                        type: 'success',
                        text: `Role assigned successfully to ${results.success.length} user(s)`,
                    });
                    this.$emit('assigned', results);
                } else if (results.success.length > 0 && results.errors.length > 0) {
                    this.$notify({
                        type: 'warning',
                        text: `Role assigned to ${results.success.length} user(s), but ${results.errors.length} failed`,
                    });
                    this.$emit('assigned', results);
                } else {
                    this.$notify({
                        type: 'error',
                        text: `Failed to assign role to ${results.errors.length} user(s)`,
                    });
                }
            } catch (error) {
                console.error('Error assigning roles:', error);
                this.$notify({
                    type: 'error',
                    text: error.message || 'Failed to assign roles',
                });
            } finally {
                this.assigning = false;
            }
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
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2000;
    padding: 1rem;
    animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.role-assignment-modal {
    max-width: 600px;
}

.modal-content {
    background: white;
    border-radius: 0.75rem;
    width: 100%;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: slideUp 0.2s ease;
}

@keyframes slideUp {
    from {
        transform: translateY(20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
    flex-shrink: 0;

    .modal-title {
        font-size: 1.125rem;
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
        background: transparent;
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
}

.modal-body {
    padding: 1.5rem;
    overflow-y: auto;
    flex: 1;
    min-height: 0;
}

.form-group {
    margin-bottom: 1.25rem;

    &:last-child {
        margin-bottom: 0;
    }

    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
    }

    .form-select {
        width: 100%;
        padding: 0.625rem 0.875rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        transition: all 0.2s;
        background: white;
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.5rem center;
        background-size: 1.25rem;
        padding-right: 2.5rem;

        &:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
    }
}

.multiselect-wrapper {
    position: relative;
}

.multiselect-input {
    width: 100%;
    padding: 0.625rem 0.875rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    background: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: all 0.2s;
    min-height: 2.5rem;

    &:hover {
        border-color: #9ca3af;
    }

    .placeholder {
        color: #9ca3af;
    }

    .selected-count {
        color: #111827;
        font-weight: 500;
    }

    .dropdown-icon {
        width: 1.25rem;
        height: 1.25rem;
        color: #6b7280;
        transition: transform 0.2s;
        flex-shrink: 0;

        &.open {
            transform: rotate(180deg);
        }
    }
}

.multiselect-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    margin-top: 0.25rem;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    z-index: 1000;
    max-height: 300px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.dropdown-header {
    padding: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;

    .search-input {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        transition: all 0.2s;

        &:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
    }
}

.dropdown-list {
    overflow-y: auto;
    max-height: 250px;
}

.checkbox-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    cursor: pointer;
    transition: background 0.2s;
    border-bottom: 1px solid #f3f4f6;

    &:hover {
        background: #f9fafb;
    }

    &:last-child {
        border-bottom: none;
    }

    input[type="checkbox"] {
        width: 1.125rem;
        height: 1.125rem;
        cursor: pointer;
        accent-color: #6366f1;
        flex-shrink: 0;
    }

    .user-info {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        flex: 1;
        min-width: 0;

        .user-name {
            font-weight: 500;
            color: #111827;
            font-size: 0.875rem;
        }

        .user-email {
            font-size: 0.75rem;
            color: #6b7280;
        }
    }
}

.empty-dropdown {
    padding: 2rem;
    text-align: center;
    color: #6b7280;
    font-size: 0.875rem;
}

.selected-users {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 0.75rem;
}

.selected-user-tag {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.375rem 0.75rem;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 0.375rem;
    font-size: 0.8125rem;
    color: #1e40af;
    font-weight: 500;

    .remove-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 1.125rem;
        height: 1.125rem;
        padding: 0;
        background: transparent;
        border: none;
        color: #1e40af;
        cursor: pointer;
        border-radius: 0.125rem;
        transition: all 0.2s;
        flex-shrink: 0;

        svg {
            width: 0.875rem;
            height: 0.875rem;
        }

        &:hover {
            background: #bfdbfe;
        }
    }
}

.form-hint {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 0.5rem;
    font-size: 0.8125rem;
    color: #1e40af;

    .hint-icon {
        width: 1rem;
        height: 1rem;
        flex-shrink: 0;
    }
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1.25rem 1.5rem;
    border-top: 1px solid #e5e7eb;
    background: #f9fafb;
    flex-shrink: 0;
}

.btn-cancel,
.btn-primary {
    padding: 0.625rem 1.25rem;
    border-radius: 0.5rem;
    font-weight: 500;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
}

.btn-cancel {
    background: white;
    color: #374151;
    border: 1px solid #d1d5db;

    &:hover {
        background: #f9fafb;
        border-color: #9ca3af;
    }
}

.btn-primary {
    background: #6366f1;
    color: white;

    &:hover:not(:disabled) {
        background: #4f46e5;
    }

    &:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
}
</style>

