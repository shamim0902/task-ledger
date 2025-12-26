<template>
    <el-container class="layout">
        <!-- Top Navigation -->
        <el-header class="navbar">
            <!-- Left Section (logo + menu) -->
            <div class="navbar-left">
                <!-- Brand Logo -->
                <div class="brand-logo">
                    <a href="/">
                        <img :src="logoUrl" alt="Brand" class="brand-logo" />
                        <span>beta</span>
                    </a>
                </div>

                <!-- Left Menu -->
                <Nav
                    class="menu"
                    v-if="primaryMenu.length"
                    :routes="primaryMenu"
                />
            </div>

            <!-- Right Menu -->
            <div class="navbar-right">
                <ThemeSwitcher />
                <Nav
                    v-if="secondaryMenu.length"
                    :routes="secondaryMenu"
                    :key="secondaryMenu[0]?.meta?.label"
                    class="menu"
                />
            </div>
        </el-header>

        <!-- Page Content -->
        <el-main class="main">
            <router-view v-slot="{ Component, route }">
                <!-- Dynamically render the routed component -->
                <component
                    :is="Component"
                    :key="route.fullPath"
                    v-on="{ 'update:application': updateApplication }"
                />
            </router-view>

            <!--- Render Footer Menu -->
            <div class="footer-menu">
                <Nav
                    v-if="footerMenu.length"
                    :routes="footerMenu"
                />
            </div>
        </el-main>
    </el-container>
</template>

<script>
import Rest from '@/utils/http/Rest';
import Nav from '@/components/Menu/Menu';
import menu from '@/components/Menu/menu';
import ThemeSwitcher from '@/components/ThemeSwitcher';

export default {
    name: "Application",
    components: {
        Nav,
        ThemeSwitcher,
    },
    props: {
        logoUrl: {
            type: String,
            required: true
        },
        baseUrl: {
            type: String,
            required: true
        }
    },
    data() {
        return {
            primaryMenu: menu.get('primary') || [],
            secondaryMenu: menu.get('secondary') || [],
            footerMenu: menu.get('footer') || [],
        };
    },
    provide() {
        return {
            // ...
        };
    },
    computed: {
        // ...
    },
    methods: {
        registerRestRequestInterceptor() {
            Rest.use((options, next) => {
                options.headers = {
                    ...options.headers,
                    'X-Fluent': 'Fluent-Custom-Header',
                };
                
                return next(options);
            });
        },
        updateApplication(data = null) {
            // ...
        },
    },
    mounted() {
        this.registerRestRequestInterceptor();
    }
};
</script>

<style>
ul.el-pager {
    margin-top: 6px !important;
}
</style>
