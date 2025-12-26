<template>
    <div class="page-content">
        <div v-if="!$route.params.id">
            <div class="page-header">
                <strong>Posts - {{ status }} {{ posts.length }}</strong>
            </div>
            <el-table :data="posts" style="cursor: pointer;">
                <el-table-column prop="ID" label="ID" />
                <el-table-column prop="post_title" label="Title" />
                <el-table-column label="Actions" align="right">
                    <template #default="{ row }">
                        <el-button
                            size="small"
                            type="primary"
                            @click="goToDetail(row.ID)"
                        >View Detail</el-button>
                    </template>
                </el-table-column>
            </el-table>
        </div>

        <router-view v-else />
    </div>
</template>

<script>
export default {
    name: 'Drafts',
    data() {
        return {
            status: 'draft',
            posts: [],
        };
    },
    methods: {
        get() {
            this.$get('posts', { status: this.status }).then(res => {
                this.posts = res.all();
            });
        },
        goToDetail(id) {
            this.$router.push({ name: 'posts.draft.detail', params: { id } });
        },
    },
    mounted() {
        this.get();
    },
};
</script>
