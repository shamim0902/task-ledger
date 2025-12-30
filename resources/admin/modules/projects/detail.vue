<template>
    <div class="project-detail-page">
        <div class="page-header">
            <button @click="goBack" class="btn-back">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Projects
            </button>
            <h1 class="page-title">{{ project?.title || 'Project Details' }}</h1>
        </div>

        <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <p>Loading project details...</p>
        </div>

        <div v-else-if="project" class="project-content">
            <!-- Project Info Section -->
            <div class="detail-section">
                <h2 class="section-title">Project Information</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Title</label>
                        <p>{{ project.title }}</p>
                    </div>
                    <div v-if="project.description" class="info-item full-width">
                        <label>Description</label>
                        <p>{{ project.description }}</p>
                    </div>
                    <div class="info-item">
                        <label>Created</label>
                        <p>{{ formatDate(project.created_at) }}</p>
                    </div>
                    <div class="info-item">
                        <label>Stages</label>
                        <p>{{ project.stages?.length || 0 }}</p>
                    </div>
                    <div class="info-item">
                        <label>Members</label>
                        <p>{{ project.users?.length || 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Roles Management Section -->
            <div class="detail-section">
                <div class="section-header">
                    <h2 class="section-title">Role Management</h2>
                    <button @click="showRoleAssignment = true" class="btn-add-role">
                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Assign Role
                    </button>
                </div>

                <div v-if="roleAssignments.length === 0" class="empty-state">
                    <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <p>No roles assigned to this project yet.</p>
                    <button @click="showRoleAssignment = true" class="btn-primary">
                        Assign First Role
                    </button>
                </div>

                <div v-else class="assignments-table">
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Scope</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="assignment in roleAssignments" :key="assignment.id">
                                <td>
                                    <div class="user-info">
                                        <span class="user-name">
                                            {{ getUserName(assignment.user_id) }}
                                        </span>
                                        <span class="user-email">
                                            {{ getUserEmail(assignment.user_id) }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <span class="role-badge" :class="`role-${assignment.role?.slug || 'default'}`">
                                        {{ assignment.role?.name || 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="scope-badge">
                                        {{ assignment.board_id ? 'Project Specific' : 'Global' }}
                                    </span>
                                </td>
                                <td>
                                    <button
                                        @click="removeRole(assignment)"
                                        class="btn-remove"
                                        title="Remove role"
                                    >
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Role Assignment Modal -->
        <ProjectRoleAssignment
            v-if="showRoleAssignment"
            :project="project"
            :available-roles="availableRoles"
            :available-users="availableUsers"
            @close="showRoleAssignment = false"
            @assigned="handleRoleAssigned"
        />
    </div>
</template>

<script>
import ProjectRoleAssignment from './components/ProjectRoleAssignment.vue';

export default {
    name: 'ProjectDetailPage',
    components: {
        ProjectRoleAssignment,
    },
    data() {
        return {
            project: null,
            roleAssignments: [],
            availableRoles: [],
            availableUsers: [],
            loading: false,
            showRoleAssignment: false,
        };
    },
    computed: {
        projectId() {
            return this.$route.params.id;
        },
    },
    mounted() {
        this.loadProjectDetails();
    },
    watch: {
        '$route.params.id'() {
            this.loadProjectDetails();
        },
    },
    methods: {
        async loadProjectDetails() {
            if (!this.projectId) return;
            
            this.loading = true;
            try {
                const response = await this.$get(`projects/${this.projectId}/roles`);
                this.project = response.board;
                this.roleAssignments = response.assignments || [];
                this.availableRoles = response.roles || [];
                this.availableUsers = response.users || [];
            } catch (error) {
                console.error('Error loading project details:', error);
                this.$notify({
                    type: 'error',
                    text: 'Failed to load project details',
                });
                // Redirect back to projects list on error
                this.$router.push('/projects');
            } finally {
                this.loading = false;
            }
        },
        async removeRole(assignment) {
            if (!confirm(`Are you sure you want to remove ${this.getUserName(assignment.user_id)}'s ${assignment.role?.name} role?`)) {
                return;
            }

            try {
                await this.$post('user-roles/remove', {
                    user_id: assignment.user_id,
                    role_id: assignment.role_id,
                    board_id: assignment.board_id || null,
                });

                this.$notify({
                    type: 'success',
                    text: 'Role removed successfully',
                });

                this.loadProjectDetails();
            } catch (error) {
                console.error('Error removing role:', error);
                this.$notify({
                    type: 'error',
                    text: 'Failed to remove role',
                });
            }
        },
        handleRoleAssigned() {
            this.showRoleAssignment = false;
            this.loadProjectDetails();
        },
        getUserName(userId) {
            const user = this.availableUsers.find(u => u.ID === userId || u.id === userId);
            if (user) {
                return user.display_name || user.user_nicename || user.user_email || `User #${userId}`;
            }
            return `User #${userId}`;
        },
        getUserEmail(userId) {
            const user = this.availableUsers.find(u => u.ID === userId || u.id === userId);
            return user?.user_email || '';
        },
        formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
            });
        },
        goBack() {
            this.$router.push('/projects');
        },
    },
};
</script>

<style lang="scss" scoped>
.project-detail-page {
    padding: 1.5rem;
    max-width: 1400px;
    margin: 0 auto;
}

.page-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;

    .btn-back {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: white;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        cursor: pointer;
        transition: all 0.2s;

        .icon {
            width: 1.25rem;
            height: 1.25rem;
        }

        &:hover {
            background: #f9fafb;
            border-color: #9ca3af;
        }
    }

    .page-title {
        font-size: 1.875rem;
        font-weight: 700;
        color: #111827;
        margin: 0;
    }
}

.loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    color: #6b7280;
    text-align: center;

    .spinner {
        width: 2.5rem;
        height: 2.5rem;
        border: 3px solid #e5e7eb;
        border-top-color: #6366f1;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 1rem;
    }
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.project-content {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.detail-section {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    padding: 1.5rem;

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .section-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #111827;
        margin: 0;
    }

    .btn-add-role {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.625rem 1.25rem;
        background: #6366f1;
        color: white;
        border: none;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;

        .icon {
            width: 1rem;
            height: 1rem;
        }

        &:hover {
            background: #4f46e5;
        }
    }
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;

    .info-item {
        label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        p {
            font-size: 0.9375rem;
            color: #111827;
            margin: 0;
            line-height: 1.5;
        }

        &.full-width {
            grid-column: 1 / -1;
        }
    }
}

.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: #6b7280;

    .empty-icon {
        width: 4rem;
        height: 4rem;
        margin: 0 auto 1rem;
        color: #d1d5db;
    }

    p {
        font-size: 0.9375rem;
        margin: 0 0 1.5rem;
    }

    .btn-primary {
        padding: 0.625rem 1.25rem;
        background: #6366f1;
        color: white;
        border: none;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;

        &:hover {
            background: #4f46e5;
        }
    }
}

.assignments-table {
    overflow-x: auto;

    table {
        width: 100%;
        border-collapse: collapse;

        thead {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;

            th {
                padding: 0.75rem 1rem;
                text-align: left;
                font-size: 0.75rem;
                font-weight: 600;
                color: #6b7280;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
        }

        tbody {
            tr {
                border-bottom: 1px solid #f3f4f6;

                &:hover {
                    background: #f9fafb;
                }

                td {
                    padding: 1rem;
                    font-size: 0.875rem;
                }
            }
        }
    }
}

.user-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;

    .user-name {
        font-weight: 500;
        color: #111827;
    }

    .user-email {
        font-size: 0.75rem;
        color: #6b7280;
    }
}

.role-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.8125rem;
    font-weight: 500;
    background: #e5e7eb;
    color: #374151;

    &.role-admin {
        background: #fee2e2;
        color: #991b1b;
    }

    &.role-manager {
        background: #dbeafe;
        color: #1e40af;
    }

    &.role-member {
        background: #dcfce7;
        color: #166534;
    }
}

.scope-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.8125rem;
    font-weight: 500;
    background: #f3f4f6;
    color: #6b7280;
}

.btn-remove {
    padding: 0.375rem;
    background: transparent;
    border: none;
    border-radius: 0.375rem;
    color: #dc2626;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;

    svg {
        width: 1rem;
        height: 1rem;
    }

    &:hover {
        background: #fee2e2;
    }
}
</style>

