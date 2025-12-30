<template>
    <div class="roles-management">
        <div class="roles-header">
            <div class="header-content">
                <div class="header-title">
                    <h1>Role Management</h1>
                    <p>Manage user roles and permissions</p>
                </div>
                <button @click="showRoleForm = true" class="btn-primary" v-if="!editingRole">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Create Role</span>
                </button>
            </div>
        </div>

        <div class="roles-content">
            <!-- Role List -->
            <RoleList
                :roles="roles"
                :loading="loading"
                @edit="handleEditRole"
                @delete="handleDeleteRole"
                @manage-permissions="handleManagePermissions"
                @assign-users="handleAssignUsers"
            />

            <!-- Role Form Modal -->
            <RoleForm
                v-if="showRoleForm"
                :role="editingRole"
                @close="closeRoleForm"
                @save="handleSaveRole"
            />

            <!-- Permission Manager Modal -->
            <PermissionManager
                v-if="selectedRole"
                :role="selectedRole"
                :permissions="permissions"
                @close="closePermissionManager"
                @save="handleSavePermissions"
            />

            <!-- User Role Assignment Modal -->
            <UserRoleAssignment
                v-if="assigningRole"
                :role="assigningRole"
                :users="users"
                :boards="boards"
                @close="closeUserAssignment"
                @save="handleSaveUserAssignment"
            />
        </div>
    </div>
</template>

<script>
import RoleList from './components/RoleList.vue';
import RoleForm from './components/RoleForm.vue';
import PermissionManager from './components/PermissionManager.vue';
import UserRoleAssignment from './components/UserRoleAssignment.vue';

export default {
    name: 'RolesManagement',
    components: {
        RoleList,
        RoleForm,
        PermissionManager,
        UserRoleAssignment,
    },
    data() {
        return {
            loading: false,
            roles: [],
            permissions: [],
            users: [],
            boards: [],
            showRoleForm: false,
            editingRole: null,
            selectedRole: null,
            assigningRole: null,
        };
    },
    mounted() {
        this.loadData();
    },
    methods: {
        async loadData() {
            this.loading = true;
            try {
                const [rolesData, permissionsData, usersData, boardsData] = await Promise.all([
                    this.$get('roles'),
                    this.$get('permissions'),
                    this.$get('user-roles/all'),
                    this.$get('pm/boards'),
                ]);

                this.roles = rolesData.all ? rolesData.all() : rolesData;
                this.permissions = permissionsData.grouped || {};
                this.users = usersData.all ? usersData.all() : usersData;
                this.boards = boardsData.all ? boardsData.all() : boardsData;
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
        handleEditRole(role) {
            this.editingRole = role;
            this.showRoleForm = true;
        },
        handleDeleteRole(role) {
            if (confirm(`Are you sure you want to delete the role "${role.name}"?`)) {
                this.deleteRole(role.id);
            }
        },
        async deleteRole(roleId) {
            try {
                await this.$delete(`roles/${roleId}`);
                this.$notify({
                    type: 'success',
                    text: 'Role deleted successfully',
                });
                this.loadData();
            } catch (error) {
                console.error('Error deleting role:', error);
                this.$notify({
                    type: 'error',
                    text: 'Failed to delete role',
                });
            }
        },
        handleManagePermissions(role) {
            this.selectedRole = role;
        },
        handleAssignUsers(role) {
            this.assigningRole = role;
        },
        closeRoleForm() {
            this.showRoleForm = false;
            this.editingRole = null;
        },
        closePermissionManager() {
            this.selectedRole = null;
        },
        closeUserAssignment() {
            this.assigningRole = null;
        },
        async handleSaveRole(roleData) {
            try {
                if (roleData.id) {
                    await this.$patch(`roles/${roleData.id}`, roleData);
                    this.$notify({
                        type: 'success',
                        text: 'Role updated successfully',
                    });
                } else {
                    await this.$post('roles', roleData);
                    this.$notify({
                        type: 'success',
                        text: 'Role created successfully',
                    });
                }
                this.closeRoleForm();
                this.loadData();
            } catch (error) {
                console.error('Error saving role:', error);
                this.$notify({
                    type: 'error',
                    text: 'Failed to save role',
                });
            }
        },
        async handleSavePermissions(permissionIds) {
            try {
                await this.$post(`roles/${this.selectedRole.id}/permissions`, {
                    permission_ids: permissionIds,
                });
                this.$notify({
                    type: 'success',
                    text: 'Permissions updated successfully',
                });
                this.closePermissionManager();
                this.loadData();
            } catch (error) {
                console.error('Error saving permissions:', error);
                this.$notify({
                    type: 'error',
                    text: 'Failed to save permissions',
                });
            }
        },
        async handleSaveUserAssignment(assignments) {
            // This will be handled by the UserRoleAssignment component
            this.closeUserAssignment();
            this.loadData();
        },
    },
};
</script>

<style lang="scss" scoped>
.roles-management {
    padding: 1.5rem;
    max-width: 1400px;
    margin: 0 auto;
}

.roles-header {
    margin-bottom: 2rem;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.header-title {
    h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
        margin: 0 0 0.25rem 0;
    }

    p {
        font-size: 0.875rem;
        color: #6b7280;
        margin: 0;
    }
}

.btn-primary {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    background: #6366f1;
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;

    svg {
        width: 1.25rem;
        height: 1.25rem;
    }

    &:hover {
        background: #4f46e5;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.3);
    }
}

.roles-content {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}
</style>

