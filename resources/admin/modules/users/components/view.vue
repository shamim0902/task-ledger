<template>
    <el-dialog
        v-model="visible"
        width="30%"
        title="View User"
        :close-on-click-modal="false"
        @close="onClose"
    >
        <template v-if="user.selectedUser">
            <el-descriptions border :column="1">
                <el-descriptions-item label="ID">
                    {{ user.selectedUser.ID }}
                </el-descriptions-item>
                <el-descriptions-item label="Name">
                    {{ user.selectedUser.user_nicename }}
                </el-descriptions-item>
                <el-descriptions-item label="Email">
                    {{ user.selectedUser.user_email }}
                </el-descriptions-item>
            </el-descriptions>
        </template>

        <template #footer>
            <el-button @click="closeDialog">Close</el-button>
        </template>
    </el-dialog>
</template>

<script>
export default {
    name: 'UserView',
    props: ['id'],
    inject: ['user'],
    data() {
        return {
            visible: false,
        };
    },
    watch: {
        id: {
            immediate: true,
            handler(id) {
                if (id) {
                    const numericId = parseInt(id, 10);
                    this.user.load(numericId).then(response => {
                        this.user.selectedUser = response;
                        this.visible = true;
                    });
                }
            },
        },
    },
    methods: {
        closeDialog() {
            this.visible = false;
            this.user.selectedUser = null;
            this.$router.push('/users');
        },
        onClose() {
            this.user.selectedUser = null;
            this.$router.push('/users');
        },
    },
};
</script>
