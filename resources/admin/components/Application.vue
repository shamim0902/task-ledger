<template>
    <div class="app-container">
        <TopMenu />
        <div class="app-content">
            <router-view />
        </div>
    </div>
</template>
  
<script setup>
import TopMenu from './TopMenu.vue';
</script>
  
<style lang="scss" scoped>
.app-container {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    background: #f9fafb;
}

.app-content {
    flex: 1;
    padding: 0;
}
</style>