<template>
    <div class="roles-management">

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

