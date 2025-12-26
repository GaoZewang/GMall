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
        <el-menu-item v-for="m in menus" :key="m.path" :index="m.path">
          <span>{{ m.title }}</span>
        </el-menu-item>
      </el-menu>
    </el-aside>

    <!-- 右侧内容 -->
    <el-container>
      <el-header class="header">
        <div class="left">
          <div class="title">{{ pageTitle }}211</div>
        </div>

        <div class="right">
          <el-dropdown trigger="click">
            <span class="user">当前用户：{{ userLabel }}</span>
            <template #dropdown>
              <el-dropdown-menu>
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
  goLogin,
  onLogout,
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
  margin: 10px 5px;
  height: 10%;
}

.title { font-weight: 900; }

.user { cursor: pointer; color: var(--text); font-weight: 700; }

/* main */
.main { padding: 16px; background: var(--bg); }
</style>
