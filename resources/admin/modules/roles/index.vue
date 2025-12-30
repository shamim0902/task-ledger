<template>
    <div class="roles-management">
        <!-- Top Navigation Bar -->
        <nav class="app-navbar">
            <div class="navbar-brand">
                <div class="brand-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div class="brand-text">
                    <h1 class="brand-title">Task Ledger</h1>
                    <p class="brand-subtitle">People Management</p>
                </div>
            </div>
        </nav>

        <!-- Main Content Area -->
        <div class="app-content">
            <div class="roles-content">
            <!-- Role List -->
            <RoleList
                :roles="roles"
                :loading="loading"
                @assign-users="handleAssignUsers"
                @manage-members="handleManageMembers"
            />

            <!-- User Role Assignment Modal -->
            <UserRoleAssignment
                v-if="assigningRole"
                :role="assigningRole"
                :users="users"
                @close="closeUserAssignment"
                @save="handleSaveUserAssignment"
            />

            <!-- Manager Member Assignment Modal -->
            <ManagerMemberAssignment
                v-if="managingMembers"
                :manager="managingMembers"
                :users="users"
                @close="closeManagerMemberAssignment"
                @save="handleSaveManagerMemberAssignment"
            />
            </div>
        </div>
    </div>
</template>

<script>
import RoleList from './components/RoleList.vue';
import UserRoleAssignment from './components/UserRoleAssignment.vue';
import ManagerMemberAssignment from './components/ManagerMemberAssignment.vue';

export default {
    name: 'RolesManagement',
    components: {
        RoleList,
        UserRoleAssignment,
        ManagerMemberAssignment,
    },
    data() {
        return {
            loading: false,
            roles: [],
            users: [],
            assigningRole: null,
            managingMembers: null,
        };
    },
    mounted() {
        this.loadData();
    },
    methods: {
        async loadData() {
            this.loading = true;
            try {
                const [rolesData, usersData] = await Promise.all([
                    this.$get('roles'),
                    this.$get('user-roles/all'),
                ]);

                const allRoles = rolesData.all ? rolesData.all() : rolesData;
                // Filter out Member role from the list
                this.roles = allRoles.filter(role => role.slug !== 'member');
                this.users = usersData.all ? usersData.all() : usersData;
            } catch (error) {
                console.error('Error loading data:', error);
                this.$notify({
                    type: 'error',
                    text: 'Failed to load data',
                });
            } finally {
                this.loading = false;
            }
        },
        handleAssignUsers(role) {
            this.assigningRole = role;
        },
        closeUserAssignment() {
            this.assigningRole = null;
        },
        async handleSaveUserAssignment(assignments) {
            // This will be handled by the UserRoleAssignment component
            this.closeUserAssignment();
            this.loadData();
        },
        handleManageMembers(role) {
            this.managingMembers = role;
        },
        closeManagerMemberAssignment() {
            this.managingMembers = null;
        },
        async handleSaveManagerMemberAssignment() {
            this.closeManagerMemberAssignment();
            this.loadData();
        },
    },
};
</script>

<style lang="scss" scoped>
.roles-management {
    min-height: 100vh;
    background: linear-gradient(to bottom, #f8fafc 0%, #f1f5f9 100%);
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

// Top Navigation Bar
.app-navbar {
    background: white;
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 1rem;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.navbar-brand {
    max-width: 1800px;
    margin: 0 auto;
    padding: 0.625rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;

    .brand-icon {
        width: 2rem;
        height: 2rem;
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 2px 4px -1px rgba(99, 102, 241, 0.3);
        flex-shrink: 0;

        svg {
            width: 1.125rem;
            height: 1.125rem;
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

// Main Content
.app-content {
    max-width: 1800px;
    margin: 0 auto;
    padding: 1.5rem;
}

.roles-content {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}
</style>

