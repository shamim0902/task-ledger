<template>
	<div class="page-header">
		<el-button
		    size="small"
		    type="primary"
		    @click="goBack"
		>Go Back</el-button>
	</div>
	<div class="page-content">
		<h2>The Child of {{ id }} ...</h2>
		<p>This component is child of the Draft Post Detail</p>
	</div>
</template>

<script>
	export default {
		name: 'd-child',
		props: {
	        id: {
	            type: [String, Number],
	            required: true,
	        },
	    },
		data() {
			return {
				
			}
		},
		methods: {
	        goBack() {
	            this.$router.push({
	            	name: 'posts.draft.detail',
	            	params: { id: this.id }
	            });
	        }
	    },
		mounted() {
		    console.log('DChild mounted with id:', this.id);
		    console.log('Current route:', this.$route.fullPath);
		}
	}
</script>