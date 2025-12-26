<template>
    <el-sub-menu
        v-if="hasNestedItems(route)"
        :index="computedSubmenuIndex"
    >
        <template #title>
            <el-icon v-if="route.meta?.icon" class="menu-icon">
                <component
                    class="menu-icon"
                    :is="resolveIcon(route.meta.icon)"
                />
            </el-icon>
            {{ computedTitle }}
        </template>

        <MenuItem
            v-for="child in visibleChildren(route)"
            :key="child.name"
            :route="child"
        />

        <template
            v-for="sub in submenuItems(route)"
            :key="getSubKey(sub)"
        >
            <MenuItem
                v-if="hasSubmenuItems(sub)"
                :route="sub"
            />
            <el-menu-item
                v-else
                :index="getSubIndex(sub)"
            >
                <el-icon v-if="sub.meta?.icon" class="menu-icon">
                    <component
                        class="menu-icon"
                        :is="resolveIcon(sub.meta.icon)"
                    />
                </el-icon>
                {{ sub.meta?.label || sub.label }}
            </el-menu-item>
        </template>
    </el-sub-menu>

    <el-menu-item v-else :index="computedIndex">
        <el-icon v-if="route.meta?.icon" class="menu-icon">
            <component
                class="menu-icon"
                :is="resolveIcon(route.meta.icon)"
            />
        </el-icon>
        {{ computedTitle }}
    </el-menu-item>
</template>

<script setup>
import { computed } from 'vue';
import MenuItem from './MenuItem';
import { useMenu } from './useMenu';
import { resolveIcon } from './icons';

const props = defineProps({
    route: { type: Object, required: true },
});

const { hasVisibleChildren, visibleChildren } = useMenu(props.route);

const computedIndex = computed(() =>
    props.route.path ||
    props.route.name ||
    `item-${Math.random().toString(36).slice(2, 6)}`
);

const computedSubmenuIndex = computed(() =>
    `submenu-${Math.random().toString(36).slice(2, 6)}`
);

const computedTitle = computed(() =>
    props.route.label || props.route.meta.label || props.route.name
);

function hasNestedItems(route) {
    return hasVisibleChildren(route) || hasSubmenuItems(route);
}

function hasSubmenuItems(route) {
    return (
        route?.meta?.submenu?.items &&
        Array.isArray(route.meta.submenu.items) &&
        route.meta.submenu.items.length > 0
    );
}

function submenuItems(route) {
    return hasSubmenuItems(route) ? route.meta.submenu.items : [];
}

function getSubIndex(sub) {
    return (
        sub.path ||
        sub.name ||
        `item-${Math.random().toString(36).slice(2, 6)}`
    );
}

function getSubKey(sub) {
    return (
        sub.name ||
        sub.meta?.label ||
        sub.label ||
        `key-${Math.random().toString(36).slice(2, 8)}`
    );
}
</script>

<style>
.menu-icon {
    width: 1em;
    height: 1em;
    font-size: 18px;
    vertical-align: middle;
    display: inline-block;
}
</style>
