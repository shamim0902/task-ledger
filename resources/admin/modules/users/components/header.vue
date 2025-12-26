<template>
    <div class="page-header">
        <strong>Users</strong>
        <div class="page-header-actions">
            <el-button type="primary" @click="user.showForm()">
                Create
            </el-button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'UserHeader',
    inject: ['user'],
};
</script>
