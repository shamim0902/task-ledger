<template>
    <div class="page-content">
        <el-table :data="user.users" v-loading="user.loading" stripe>
            <el-table-column prop="ID" label="ID"></el-table-column>
            <el-table-column prop="user_nicename" label="Name"></el-table-column>
            <el-table-column prop="user_email" label="Email"></el-table-column>
            <el-table-column align="right">
                <template #default="scope">
                    <el-button
                        size="small"
                        type="primary"
                        @click="user.showForm(scope.row)"
                    >
                        <el-icon><Edit /></el-icon>
                    </el-button>

                    <el-button
                        size="small"
                        type="info"
                        @click="goToView(scope.row.ID)"
                    >
                        <el-icon><View /></el-icon>
                    </el-button>

                    <Confirm #reference @yes="user.remove($event, scope.row)" />
                </template>
            </el-table-column>
        </el-table>

        <div class="pagination">
            <Pagination :pagination="user.pagination" @fetch="() => user.get()" />
        </div>
    </div>
</template>

<script>
import Confirm from '@/components/Confirm';
import Pagination from '@/components/Pagination';
import { Edit, View } from '@element-plus/icons-vue';

export default {
    name: 'UserBody',
    components: {
        Edit,
        View,
        Confirm,
        Pagination,
    },
    inject: ['user'],
    methods: {
        goToView(id) {
            this.$router.push({
                name: 'users.view',
                params: { id }
            });
        },
    },
};
</script>
