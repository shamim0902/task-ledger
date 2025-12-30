<template>
    <div class="modal-overlay" @click.self="$emit('close')">
        <div class="modal-content manager-member-modal">
            <div class="modal-header">
                <div class="header-content">
                    <div class="header-icon-wrapper">
                        <svg class="header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="modal-title">Manage Members</h3>
                        <p class="modal-subtitle">Assign members to managers</p>
                    </div>
                </div>
                <button @click="$emit('close')" class="modal-close">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="modal-body">
                <!-- Quick Assign Section -->
                <div class="quick-assign-section">
                    <div class="section-header">
                        <h4 class="section-title">Quick Assign</h4>
                    </div>
                    <div class="assign-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">
                                    <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Manager
                                </label>
                                <select v-model="selectedManagerId" class="form-select">
                                    <option value="">Select a manager...</option>
                                    <option v-for="managerUser in availableManagers" :key="managerUser.user.ID" :value="managerUser.user.ID">
                                        {{ managerUser.user.display_name || managerUser.user.user_nicename }}
                                    </option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">
                                    <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    User
                                </label>
                                <select v-model="selectedMemberId" class="form-select">
                                    <option value="">Select a user...</option>
                                    <option v-for="member in availableMembers" :key="member.user.ID" :value="member.user.ID">
                                        {{ member.user.display_name || member.user.user_nicename }} ({{ member.user.user_email }})
                                    </option>
                                </select>
                            </div>
                            <div class="form-group form-group-button">
                                <button @click="assignMember" class="btn-assign" :disabled="!selectedMemberId || !selectedManagerId || assigning">
                                    <svg v-if="!assigning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    <span v-if="assigning">Assigning...</span>
                                    <span v-else>Assign</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hierarchy View -->
                <div class="hierarchy-section">
                    <div class="section-header">
                        <h4 class="section-title">Current Hierarchy</h4>
                        <span class="section-count">{{ allMembers.length }} member{{ allMembers.length !== 1 ? 's' : '' }}</span>
                    </div>
                    <div v-if="loading" class="loading-state">
                        <div class="spinner"></div>
                        <p>Loading hierarchy...</p>
                    </div>
                    <div v-else class="hierarchy-tree">
                        <!-- Admin Level -->
                        <div v-if="admins.length > 0" class="hierarchy-level admin-level">
                            <div class="admin-card">
                                <div class="admin-badge">
                                    <svg class="badge-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    <div class="admin-info">
                                        <span class="admin-name">{{ admins[0].user.display_name || admins[0].user.user_nicename }}</span>
                                        <span class="admin-role">Admin</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Members under Admin -->
                            <div v-if="allMembers.length > 0" class="members-list">
                                <div
                                    v-for="member in allMembers"
                                    :key="member.user.ID"
                                    class="member-item"
                                >
                                    <div class="member-content">
                                        <svg class="connector-line" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                        <div class="member-badge">
                                            <svg class="badge-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            <div class="member-info">
                                                <span class="member-name">{{ member.user.display_name || member.user.user_nicename }}</span>
                                                <span class="member-email">{{ member.user.user_email }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="empty-members">
                                <p>No members yet</p>
                            </div>
                        </div>
                        <div v-else class="empty-state">
                            <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <p class="empty-text">No admin found</p>
                            <p class="empty-hint">Assign an admin role to see the hierarchy</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" @click="$emit('close')" class="btn-close">Close</button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ManagerMemberAssignment',
    props: {
        manager: {
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
            selectedMemberId: '',
            selectedManagerId: '',
            assignments: [],
            loading: false,
            assigning: false,
        };
    },
    computed: {
        availableMembers() {
            // Show all users except admins and managers
            return (this.users || []).filter(user => {
                if (!user.roles || user.roles.length === 0) {
                    // Users without roles are Members by default
                    return true;
                }
                // Exclude users who have Admin or Manager roles
                const hasAdminOrManager = user.roles.some(role => {
                    const slug = role.role?.slug;
                    return slug === 'admin' || slug === 'manager';
                });
                return !hasAdminOrManager;
            });
        },
        availableManagers() {
            // Filter users who have Manager role
            return (this.users || []).filter(user => {
                return user.roles && user.roles.some(role => role.role?.slug === 'manager');
            });
        },
        admins() {
            // Get all users with Admin role
            return (this.users || []).filter(user => {
                return user.roles && user.roles.some(role => role.role?.slug === 'admin');
            });
        },
        allMembers() {
            // Get all members (same as availableMembers but for display)
            return this.availableMembers;
        },
    },
    mounted() {
        this.loadAssignments();
    },
    methods: {
        async loadAssignments() {
            this.loading = true;
            try {
                // Load all manager-member assignments
                const managers = this.availableManagers;
                this.assignments = [];
                
                for (const managerUser of managers) {
                    try {
                        const response = await this.$get(`user-roles/manager/${managerUser.user.ID}/members`);
                        if (response.members && response.members.length > 0) {
                            response.members.forEach(member => {
                                this.assignments.push({
                                    id: `${managerUser.user.ID}-${member.ID}`,
                                    manager_id: managerUser.user.ID,
                                    member_id: member.ID,
                                });
                            });
                        }
                    } catch (error) {
                        console.error(`Error loading members for manager ${managerUser.user.ID}:`, error);
                    }
                }
            } catch (error) {
                console.error('Error loading assignments:', error);
            } finally {
                this.loading = false;
            }
        },
        async assignMember() {
            if (!this.selectedMemberId || !this.selectedManagerId) return;

            this.assigning = true;
            try {
                await this.$post('user-roles/assign-member', {
                    manager_id: this.selectedManagerId,
                    member_id: this.selectedMemberId,
                });

                this.$notify({
                    type: 'success',
                    text: 'Member assigned successfully',
                });

                this.selectedMemberId = '';
                this.selectedManagerId = '';
                this.loadAssignments();
            } catch (error) {
                console.error('Error assigning member:', error);
                this.$notify({
                    type: 'error',
                    text: error.response?.data?.error || 'Failed to assign member',
                });
            } finally {
                this.assigning = false;
            }
        },
        async removeMember(assignment) {
            if (!confirm(`Remove ${this.getUserName(assignment.member_id)} from ${this.getUserName(assignment.manager_id)}?`)) {
                return;
            }

            try {
                await this.$post('user-roles/remove-member', {
                    manager_id: assignment.manager_id,
                    member_id: assignment.member_id,
                });

                this.$notify({
                    type: 'success',
                    text: 'Assignment removed successfully',
                });

                this.loadAssignments();
            } catch (error) {
                console.error('Error removing member:', error);
                this.$notify({
                    type: 'error',
                    text: 'Failed to remove assignment',
                });
            }
        },
        getUserName(userId) {
            const user = this.users.find(u => u.user.ID === userId);
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

.manager-member-modal {
    background: white;
    border-radius: 0.75rem;
    width: 100%;
    max-width: 800px;
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
    background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
    flex-shrink: 0;
}

.header-content {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
}

.header-icon-wrapper {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.625rem;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.header-icon {
    width: 1.375rem;
    height: 1.375rem;
    color: white;
}

.modal-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
    line-height: 1.2;
}

.modal-subtitle {
    font-size: 0.8125rem;
    color: #6b7280;
    margin: 0.125rem 0 0 0;
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
    flex-shrink: 0;

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

.quick-assign-section {
    margin-bottom: 1.5rem;
    padding: 1.25rem;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 0.625rem;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.section-title {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.section-count {
    font-size: 0.75rem;
    color: #6b7280;
    font-weight: 500;
    background: white;
    padding: 0.25rem 0.625rem;
    border-radius: 0.375rem;
    border: 1px solid #e5e7eb;
}

.assign-form {
    margin-top: 1rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr auto;
    gap: 0.75rem;
    align-items: flex-end;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group-button {
    align-self: flex-end;
}

.form-label {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.label-icon {
    width: 0.875rem;
    height: 0.875rem;
    color: #6366f1;
}

.form-select {
    width: 100%;
    padding: 0.625rem 0.875rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    transition: all 0.2s;
    background: white;
    color: #111827;

    &:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
}

.btn-assign {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    border: none;
    border-radius: 0.5rem;
    font-weight: 600;
    font-size: 0.875rem;
    color: white;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;

    svg {
        width: 1rem;
        height: 1rem;
    }

    &:hover:not(:disabled) {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.3);
    }

    &:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
}

.hierarchy-section {
    margin-top: 1.5rem;
}

.hierarchy-tree {
    display: flex;
    flex-direction: column;
    gap: 0;
}

.hierarchy-level {
    display: flex;
    flex-direction: column;
    gap: 0;
}

.admin-level {
    gap: 0.75rem;
}

.admin-card {
    padding: 1rem 1.25rem;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    border-radius: 0.625rem;
    box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.3);
}

.admin-badge {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.admin-info {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.admin-name {
    font-size: 0.9375rem;
    font-weight: 600;
    color: white;
    line-height: 1.2;
}

.admin-role {
    font-size: 0.75rem;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.8);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-icon {
    width: 1.25rem;
    height: 1.25rem;
    color: white;
    flex-shrink: 0;
}

.members-list {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
    margin-left: 1.5rem;
    padding-left: 1rem;
    border-left: 2px solid #e5e7eb;
}

.member-item {
    display: flex;
    align-items: center;
    padding: 0.625rem 0.75rem;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    transition: all 0.2s;

    &:hover {
        border-color: #d1d5db;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        background: #f9fafb;
    }
}

.member-content {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    flex: 1;
    min-width: 0;
}

.connector-line {
    width: 1rem;
    height: 1rem;
    color: #d1d5db;
    flex-shrink: 0;
}

.member-badge {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    flex: 1;
    min-width: 0;
}

.member-info {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
    flex: 1;
    min-width: 0;
}

.member-name {
    font-size: 0.875rem;
    font-weight: 500;
    color: #111827;
    line-height: 1.2;
}

.member-email {
    font-size: 0.75rem;
    color: #6b7280;
    line-height: 1.2;
}

.member-badge .badge-icon {
    width: 1rem;
    height: 1rem;
    color: #10b981;
    flex-shrink: 0;
}

.empty-members {
    margin-left: 1.5rem;
    padding: 1rem;
    text-align: center;
    color: #9ca3af;
    font-size: 0.8125rem;
    font-style: italic;
}

.btn-remove {
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fee2e2;
    border: 1px solid #fecaca;
    border-radius: 0.375rem;
    color: #dc2626;
    cursor: pointer;
    transition: all 0.2s;
    flex-shrink: 0;

    svg {
        width: 1rem;
        height: 1rem;
    }

    &:hover {
        background: #fecaca;
        border-color: #fca5a5;
        transform: scale(1.05);
    }
}

.loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 1rem;
    color: #6b7280;

    .spinner {
        width: 2rem;
        height: 2rem;
        border: 3px solid #e5e7eb;
        border-top-color: #6366f1;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        margin-bottom: 1rem;
    }

    p {
        margin: 0;
        font-size: 0.875rem;
    }
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #6b7280;

    .empty-icon {
        width: 3rem;
        height: 3rem;
        margin: 0 auto 1rem;
        color: #d1d5db;
        opacity: 0.5;
    }

    .empty-text {
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        margin: 0 0 0.25rem 0;
    }

    .empty-hint {
        font-size: 0.8125rem;
        color: #9ca3af;
        margin: 0;
    }
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    padding: 1rem 1.5rem;
    border-top: 1px solid #e5e7eb;
    background: #f9fafb;
    flex-shrink: 0;
}

.btn-close {
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

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }

    .hierarchy-content {
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .arrow-icon {
        transform: rotate(90deg);
    }
}
</style>
