<template>
    <el-dialog
        :close-on-click-modal="false"
        width="30%"
        :title="user.title"
        v-model="user.visible"
    >
        <el-form>
            <el-form-item label="Name">
                <el-input v-model="user.form.user_nicename" />
                <Error :field="user.errors.user_nicename" />
            </el-form-item>
            <el-form-item label="Email">
                <el-input v-model="user.form.user_email" />
                <Error :field="user.errors.user_email" />
            </el-form-item>
        </el-form>

        <template #footer>
            <el-button @click="user.visible = false">Close</el-button>
            <el-button
                :loading="user.saving"
                @click="user.save()"
                type="primary"
            >
                Save
            </el-button>
        </template>
    </el-dialog>
</template>

<script>
import Error from '@/components/Error.vue';

export default {
    name: 'UserForm',
    components: {
        Error,
    },
    inject: ['user'],
};
</script>
