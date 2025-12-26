<template>
    <div class="page-header" v-if="$route.name === 'posts.draft.detail'">
        <el-button
                size="small"
                type="primary"
                icon="el-icon-arrow-left"
                @click="goBackToList"
                style="margin-bottom: 1rem;"
            >
                Back to Drafts List
            </el-button>
    </div>
    <div class="page-content">
        <div v-if="$route.name === 'posts.draft.detail'">
            <!-- Show only on parent route -->
            <div class="page-header">
                <h1>Draft Post Detail - ID: {{ id }}</h1>
            </div>

            <div v-if="post" class="page-content">
                <h2>{{ post.post_title }}</h2>
                <p><strong>Content: </strong></p>
                <div>{{ plainTextExcerpt(post.post_content, 300) }}</div>

                <br/>
                <el-button
                    style="float: right;"
                    size="small"
                    type="primary"
                    @click="goToChild(id)"
                >Goto Child</el-button>
            </div>

            <div v-else>
                <p>Loading post details...</p>
            </div>
        </div>

        <!-- Show child route component -->
        <router-view v-else />
    </div>
</template>

<script>
export default {
    name: 'DraftDetail',
    props: {
        id: {
            type: [String, Number],
            required: true,
        },
    },
    data() {
        return {
            post: null,
        };
    },
    methods: {
        fetchPost() {
            this.$get(`posts/${this.id}`).then(response => {
                this.post = response;
            }).catch(() => {
                this.post = null;
            });
        },
        goToChild(id) {
            this.$router.push({
                name: 'posts.draft.detail.child',
                params: { id }
            });
        },
        goBackToList() {
            this.$router.push({ name: 'posts.draft' });
        },
        plainTextExcerpt(html, limit = 300) {
            const div = document.createElement('div');
            div.innerHTML = html;
            return div.textContent.slice(0, limit) + '…';
        }
    },
    mounted() {
        this.fetchPost();
    },
    watch: {
        id(newId) {
            this.fetchPost();
        },
    },
};
</script>

<style scoped>
.page-header {
    margin-bottom: 1rem;
}
</style>
