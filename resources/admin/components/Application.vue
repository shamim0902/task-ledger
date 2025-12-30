<template>
    <div class="top-menu">
        <!-- <nav class="top-menu">
      <ul>
        <li
          v-for="item in menuItems"
          :key="item.name"
          :class="{ active: isActive(item.path) }"
        >
          <router-link :to="item.path" class="menu-link">
            <i :class="item.icon"></i>
            <span>{{ item.meta.label }}</span>
          </router-link>
        </li>
      </ul>
    </nav> -->
    <router-view />
    </div>
  </template>
  
  <script setup>
  import { ref } from 'vue';
  import { useRoute } from 'vue-router';
  
  // Define your menu items
  const menuItems = ref([
    {
      name: 'Developers',
      path: '/',
      icon: 'el-icon-s-home',
      meta: { label: 'Developers' }
    },
    {
      name: 'Projects Manager',
      path: '/pm',
      icon: 'el-icon-user',
      meta: { label: 'Projects Manager' }
    }
  ]);
  
  const route = useRoute();
  
  // Highlight the active route
  const isActive = (path) => {
    return route.path === path;
  };
  </script>
  
  <style lang="scss" scoped>
  .top-menu {
    background-color: #fff;
    border-bottom: 1px solid #e6e6e6;
  
    ul {
      display: flex;
      align-items: center;
      list-style: none;
      margin: 0;
      padding: 0;
  
      li {
        margin-right: 30px;
  
        &.active {
          .menu-link {
            color: #409EFF;
            font-weight: bold;
          }
        }
  
        .menu-link {
          display: flex;
          align-items: center;
          text-decoration: none;
          color: #333;
          font-size: 14px;
          padding: 15px 0;
  
          i {
            margin-right: 8px;
            font-size: 18px;
          }
  
          &:hover {
            color: #409EFF;
          }
        }
      }
    }
  }
  </style>