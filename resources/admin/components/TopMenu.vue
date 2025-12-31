<template>
    <div class="top-menu-container" v-if="visibleRoutes.length > 0" :style="{ display: userRole === 'manager' ? 'block' : 'none' }">
        <el-menu
            :default-active="activeRoute"
            mode="horizontal"
            :router="true"
            class="top-menu"
        >
            <el-menu-item
                v-for="route in visibleRoutes"
                :key="route.name || route.path"
                :index="route.path"
            >
                <el-icon v-if="route.meta?.icon" class="menu-icon">
                    <component
                        class="menu-icon"
                        :is="resolveIcon(route.meta.icon)"
                    />
                </el-icon>
                <span>{{ getRouteTitle(route) }}</span>
            </el-menu-item>
        </el-menu>
    </div>
</template>

<script setup>
import { computed, getCurrentInstance, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { resolveIcon } from './Menu/icons';
import menu from './Menu/menu';

const route = useRoute();
const instance = getCurrentInstance();

// Get user role from global config
const userRole = computed(() => {
    const config = window.fluentFrameworkAdmin || {};
    return config.userRole || 'member';
});

const isAdmin = computed(() => {
    const config = window.fluentFrameworkAdmin || {};
    return config.isAdmin || false;
});

const isManager = computed(() => {
    const config = window.fluentFrameworkAdmin || {};
    return config.isManager || false;
});

// Get routes from menu API or global properties
const allRoutes = ref([]);

onMounted(() => {
    // Try menu API first
    const primaryRoutes = menu.get('primary');
    if (Array.isArray(primaryRoutes) && primaryRoutes.length > 0) {
        allRoutes.value = primaryRoutes;
    } else if (instance?.appContext?.config?.globalProperties?.groupedRoutes) {
        // Fallback to global properties
        allRoutes.value = instance.appContext.config.globalProperties.groupedRoutes.primary || [];
    } else if (window.fluentFrameworkAdmin?.routes) {
        // Try from fluentFrameworkAdmin routes
        const routerObj = window.fluentFrameworkAdmin.routes;
        if (routerObj && routerObj.menus && routerObj.menus.primary) {
            allRoutes.value = routerObj.menus.primary;
        }
    }
});

// Filter routes based on user role
const visibleRoutes = computed(() => {
    if (!Array.isArray(allRoutes.value) || allRoutes.value.length === 0) {
        return [];
    }

    return allRoutes.value.filter(routeItem => {
        // Skip routes without name or with dynamic paths
        if (!routeItem.name || (routeItem.path && routeItem.path.includes(':'))) {
            return false;
        }

        // Skip notfound and unauthorized routes
        if (routeItem.name === 'notfound' || routeItem.name === 'unauthorized') {
            return false;
        }

        // Check if route is visible (meta.visible)
        if (routeItem.meta?.visible === false) {
            return false;
        }

        // Admin sees all routes
        if (isAdmin.value) {
            return true;
        }

        // Manager sees: Dashboard (/) and Submissions (review)
        if (isManager.value) {
            const path = routeItem.path || '';
            const name = routeItem.name || '';
            return path === '/' || name === 'review' || name === 'dashboard';
        }

        // Member sees only: Dashboard (/)
        const path = routeItem.path || '';
        const name = routeItem.name || '';
        return path === '/' || name === 'dashboard';
    });
});

const activeRoute = computed(() => route.path);

const getRouteTitle = (routeItem) => {
    return routeItem.meta?.title || routeItem.meta?.label || routeItem.title || routeItem.name || 'Menu';
};
</script>

<style lang="scss" scoped>
.top-menu-container {
    background: #fff;
    border-bottom: 1px solid #e5e7eb;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    position: sticky;
    top: 0;
    z-index: 100;
}

.top-menu {
    border-bottom: none;
    max-width: 100%;
    
    :deep(.el-menu-item) {
        height: 56px;
        line-height: 56px;
        padding: 0 20px;
        font-size: 14px;
        font-weight: 500;
        color: #6b7280;
        transition: all 0.2s;

        .menu-icon {
            margin-right: 8px;
            font-size: 16px;
        }

        &:hover {
            color: #6366f1;
            background-color: #f9fafb;
        }

        &.is-active {
            color: #6366f1;
            border-bottom-color: #6366f1;
            border-bottom-width: 2px;
            font-weight: 600;
        }
    }
}
</style>

