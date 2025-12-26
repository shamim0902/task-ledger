<template>
	<div>
		<div class="page-header">
			<strong>Posts - {{ status }} {{ posts.length }}</strong>
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
		name: 'Publish',
		data() {
			return {
				status: 'publish',
				posts: []
			};
		},
		methods: {
			get() {
				this.$get('posts', { status: this.status }).then(res => {
					this.posts = res.all();
				});
			}
		},
		mounted() {
			this.get();
		}
	};
</script>