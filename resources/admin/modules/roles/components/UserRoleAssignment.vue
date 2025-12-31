<template>
    <div class="modal-overlay" @click.self="$emit('close')">
        <div class="modal-content user-assignment-modal">
            <div class="modal-header">
                <h3 class="modal-title">Assign Users: {{ role.name }}</h3>
                <button @click="$emit('close')" class="modal-close">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <label>Select User</label>
                    <select v-model="selectedUserId" class="form-select">
                        <option value="">Choose a user...</option>
                        <option v-for="user in availableUsers" :key="user.user.ID" :value="user.user.ID">
                            {{ user.user.display_name || user.user.user_nicename }} ({{ user.user.user_email }})
                        </option>
                    </select>
                </div>

                <button @click="assignUserRole" class="btn-primary" :disabled="!selectedUserId">
                    Assign Role
                </button>

                <div class="current-assignments">
                    <h4>Current Assignments</h4>
                    <div v-if="currentUserAssignments.length === 0" class="empty-state">
                        <p>No users assigned to this role</p>
                    </div>
                    <div v-else class="assignments-list">
                        <div
                            v-for="assignment in currentUserAssignments"
                            :key="assignment.id"
                            class="assignment-item"
                        >
                            <div class="assignment-info">
                                <span class="user-name">{{ getUserName(assignment.user_id) }}</span>
                            </div>
                            <button @click="removeUserRole(assignment)" class="btn-remove">
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" @click="$emit('close')" class="btn-cancel">Close</button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'UserRoleAssignment',
    props: {
        role: {
            type: Object,
            required: true,
        },
        users: {
            type: Array,
            default: () => [],
        },
    },
    emits: ['close', 'save'],
    data() {
        return {
            selectedUserId: '',
            currentUserAssignments: [],
            loading: false,
        };
    },
    computed: {
        availableUsers() {
            return this.users || [];
        },
    },
    mounted() {
        this.loadCurrentAssignments();
    },
    methods: {
        async loadCurrentAssignments() {
            this.loading = true;
            try {
                const response = await this.$get(`user-roles/role/${this.role.id}`);
                if (response.users) {
                    // Transform the response to match our structure
                    this.currentUserAssignments = response.users.map(userData => ({
                        id: userData.id,
                        user_id: userData.user_id || userData.user.ID,
                        role_id: userData.role_id || this.role.id,
                    }));
                }
            } catch (error) {
                console.error('Error loading assignments:', error);
                this.$notify({
                    type: 'error',
                    text: 'Failed to load current assignments'
                });
            } finally {
                this.loading = false;
            }
        },
        async assignUserRole() {
            if (!this.selectedUserId) return;

            try {
                await this.$post('user-roles/assign', {
                    user_id: this.selectedUserId,
                    role_id: this.role.id,
                });

                this.$notify({
                    type: 'success',
                    text: 'Role assigned successfully',
                });

                this.selectedUserId = '';
                this.loadCurrentAssignments();
            } catch (error) {
                console.error('Error assigning role:', error);
                this.$notify({
                    type: 'error',
                    text: 'Failed to assign role',
                });
            }
        },
        async removeUserRole(assignment) {
            if (!confirm('Are you sure you want to remove this role assignment?')) {
                return;
            }

            this.loading = true;
            try {
                // Ensure we have valid user_id and role_id
                const userId = parseInt(assignment.user_id);
                const roleId = parseInt(assignment.role_id || this.role.id);
                
                if (!userId || !roleId) {
                    throw new Error('Invalid assignment data: missing user_id or role_id');
                }
                
                console.log('Removing role assignment:', { userId, roleId, assignment });
                
                const response = await this.$post('user-roles/remove', {
                    user_id: userId,
                    role_id: roleId,
                });

                console.log('Remove response:', response);

                const userName = this.getUserName(assignment.user_id);
                const roleName = this.role.name || 'role';
                this.$notify({
                    type: 'success',
                    text: `${roleName} removed successfully from ${userName}`,
                });

                // Reload assignments to reflect the change
                await this.loadCurrentAssignments();
            } catch (error) {
                console.error('Error removing role assignment:', error);
                
                // Extract error message from various response formats
                let errorMessage = 'Failed to remove role assignment. Please try again.';
                
                try {
                    // Check if error has responseJSON (ResponseProxyItr format)
                    if (error.responseJSON) {
                        const responseData = error.responseJSON;
                        if (responseData.message) {
                            errorMessage = responseData.message;
                        } else if (responseData.error) {
                            errorMessage = responseData.error;
                        } else if (typeof responseData === 'string') {
                            errorMessage = responseData;
                        }
                    }
                    // Check if error has response property (Axios-style)
                    else if (error.response) {
                        const responseData = error.response.data || error.response;
                        if (responseData && responseData.message) {
                            errorMessage = responseData.message;
                        } else if (responseData && responseData.error) {
                            errorMessage = responseData.error;
                        } else if (typeof responseData === 'string') {
                            errorMessage = responseData;
                        }
                    }
                    // Check if error has data property directly
                    else if (error.data) {
                        if (error.data.message) {
                            errorMessage = error.data.message;
                        } else if (error.data.error) {
                            errorMessage = error.data.error;
                        } else if (typeof error.data === 'string') {
                            errorMessage = error.data;
                        } else if (Array.isArray(error.data) && error.data.length > 0) {
                            errorMessage = typeof error.data[0] === 'string' ? error.data[0] : error.data[0].message || errorMessage;
                        }
                    }
                    // Check if error has message property
                    else if (error.message) {
                        errorMessage = error.message;
                    }
                    // Try to get message from all() method if available
                    else if (typeof error.all === 'function') {
                        const allData = error.all();
                        if (allData.message) {
                            errorMessage = allData.message;
                        } else if (allData.error) {
                            errorMessage = allData.error;
                        }
                    }
                } catch (parseError) {
                    console.error('Error parsing error message:', parseError);
                    // Fall back to default message
                }
                
                this.$notify({
                    type: 'error',
                    text: errorMessage,
                });
            } finally {
                this.loading = false;
            }
        },
        getUserName(userId) {
            const user = this.availableUsers.find(u => u.user.ID === userId);
            return user ? (user.user.display_name || user.user.user_nicename) : `User #${userId}`;
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
    z-index: 1000;
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

.user-assignment-modal {
    background: white;
    border-radius: 0.75rem;
    width: 100%;
    max-width: 600px;
    max-height: 90vh;
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
    overflow-y: auto;
    flex: 1;
    min-height: 0;
}

.form-group {
    margin-bottom: 1.25rem;

    label {
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

        &:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
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
    margin-bottom: 2rem;

    &:hover:not(:disabled) {
        background: #4f46e5;
    }

    &:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
}

.current-assignments {
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e7eb;

    h4 {
        font-size: 1rem;
        font-weight: 600;
        color: #111827;
        margin: 0 0 1rem 0;
    }
}

.empty-state {
    text-align: center;
    padding: 2rem;
    color: #6b7280;
    font-size: 0.875rem;
}

.assignments-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.assignment-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
}

.assignment-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.user-name {
    font-weight: 500;
    color: #111827;
    font-size: 0.875rem;
}

.btn-remove {
    padding: 0.375rem 0.75rem;
    background: #fee2e2;
    border: 1px solid #fecaca;
    border-radius: 0.375rem;
    font-size: 0.8125rem;
    font-weight: 500;
    color: #dc2626;
    cursor: pointer;
    transition: all 0.2s;

    &:hover {
        background: #fecaca;
        border-color: #fca5a5;
    }
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    padding: 1.25rem 1.5rem;
    border-top: 1px solid #e5e7eb;
    background: #f9fafb;
    flex-shrink: 0;
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
</style>
