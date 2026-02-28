<template>
  <el-container class="wrap">
    <!-- 左侧菜单 -->
    <el-aside width="220px" class="aside">
      <div class="brand">
        <div class="logo">GM</div>
        <div class="txt">
          <div class="name">Gmall</div>
          <div class="sub">{{ platformLabel }}</div>
        </div>
      </div>

      <el-menu router :default-active="active" class="menu">
        <template v-for="m in menus" :key="m.path">
          <el-menu-item v-if="!m.children" :index="m.path">
            <span>{{ m.title }}</span>
          </el-menu-item>
          <el-sub-menu v-else :index="m.path">
            <template #title>
              <span>{{ m.title }}</span>
            </template>
            <el-menu-item v-for="child in m.children" :key="child.path" :index="child.path">
              <span>{{ child.title }}</span>
            </el-menu-item>
          </el-sub-menu>
        </template>
      </el-menu>
    </el-aside>

    <!-- 右侧内容 -->
    <el-container>
      <el-header class="header">
        <div class="left">
          <!-- 条件显示图标：返回页面显示箭头左图标可点击，首页显示home图标，列表页显示list图标 -->
          <template v-if="showBackButton">
            <span @click="handleBack" class="back-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-arrow-left">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M5 12l14 0"/>
                <path d="M5 12l6 6"/>
                <path d="M5 12l6 -6"/>
              </svg>
            </span>
          </template>
          <template v-else-if="route.path === '/'">
            <span class="page-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-home">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M5 12l-2 0l9 -9l9 9l-2 0"/>
                <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7"/>
                <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6"/>
              </svg>
            </span>
          </template>
          <template v-else>
            <span class="page-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-list">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M8 6h12"/>
                <path d="M8 12h12"/>
                <path d="M8 18h12"/>
                <path d="M4 6v.01"/>
                <path d="M4 12v.01"/>
                <path d="M4 18v.01"/>
              </svg>
            </span>
          </template>
          <div class="title">{{ pageTitle }}</div>
        </div>

        <div class="right">
          <el-dropdown trigger="click">
            <span class="user">当前用户：{{ userLabel }}</span>
            <template #dropdown>
              <el-dropdown-menu>
                <el-dropdown-item @click="onRefreshToken">刷新Token</el-dropdown-item>
                <el-dropdown-item divided @click="onLogout">退出登录</el-dropdown-item>
              </el-dropdown-menu>
            </template>
          </el-dropdown>
        </div>
      </el-header>

      <el-main class="main">
        <router-view />
      </el-main>
    </el-container>
  </el-container>
</template>

<script setup lang="ts">
import { useLayout } from './useLayout'

const {
  menus,
  platformLabel,
  pageTitle,
  active,
  userLabel,
  route,
  goLogin,
  onLogout,
  onRefreshToken,
  handleBack,
  showBackButton,
} = useLayout()
</script>

<style scoped>
.wrap { height: 100vh; }

/* aside */
.aside { 
  background: #fff; 
  border-right: 1px solid var(--line);   
  border-radius: 10px; 
  box-shadow: 10px #888888;
  margin: 10px 5px;
  height: 98%;
}

.brand { display: flex; align-items: center; gap: 12px; padding: 16px; }

.logo {
  width: 40px; height: 40px; border-radius: 12px;
  display: grid; place-items: center;
  background: #FF6700; color: #fff; font-weight: 900; letter-spacing: .5px;
}

.txt .name { font-weight: 900; line-height: 1; }
.txt .sub { font-size: 12px; color: var(--sub); margin-top: 4px; }

.menu { border-right: none; }

/* header */
.header {
  background: #fff; border-bottom: 1px solid var(--line);
  display: flex; align-items: center; justify-content: space-between;
  border-radius: 10px;
  box-shadow: 10px #888888;
  margin: 10px 12px;
  height: 10%;
  width: 98.5%;
}

.left {
  display: flex;
  align-items: center;
  height: 100%;
}

.title { font-weight: 900; font-size: 18px; margin-left: 0px;margin-top: -3px; line-height: 1; }

.back-icon,
.page-icon {
  color: #1a1919;
  transition: color 0.3s ease;
  display: inline-block;
  cursor: pointer;
  padding: 6px;
  margin-right: 4px;
  border-radius: 4px;
}

.back-icon:hover {
  color: #409eff;
  background-color: rgba(64, 158, 255, 0.1);
}

.page-icon {
  cursor: default;
  color: #909399;
}

/* 确保图标居中对齐 */
.back-icon svg,
.page-icon svg {
  display: block;
  vertical-align: middle;
}

.user { cursor: pointer; color: var(--text); font-weight: 700; }

/* main */
.main { padding: 16px; background: var(--bg); }
</style>
