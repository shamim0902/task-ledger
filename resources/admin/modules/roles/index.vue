<template>
    <div class="roles-management">
        <div class="roles-header">
            <div class="header-content">
                <div class="header-title">
                    <h1>People Management</h1>
                    <p>Manage roles and organizational hierarchy</p>
                </div>
            </div>
        </div>

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
    padding: 1.25rem;
    max-width: 1400px;
    margin: 0 auto;
}

.roles-header {
    margin-bottom: 1.5rem;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.header-title {
    h1 {
        font-size: 1.375rem;
        font-weight: 700;
        color: #111827;
        margin: 0 0 0.25rem 0;
        letter-spacing: -0.025em;
    }

    p {
        font-size: 0.8125rem;
        color: #6b7280;
        margin: 0;
    }
}

.roles-content {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}
</style>

