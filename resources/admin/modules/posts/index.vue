<template>
	<div>
		<div class="page-header">
			<strong>Posts - All {{ posts.length }}</strong>
		</div>

		<div class="page-content">
			<el-table :data="posts">
				<el-table-column prop="ID" label="ID" />
				<el-table-column prop="post_title" label="Title" />
			</el-table>
		</div>
	</div>
</template>

<script>
	export default {
		name: 'Posts',
		data() {
			return {
				posts: [],
			};
		},
		methods: {
			get() {
				this.$get('posts').then(res => {
					this.posts = res.all();
				});
			}
		},
		mounted() {
			this.get();
		}
	};
</script>
