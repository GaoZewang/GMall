// 简单的组件语法检查脚本
import fs from 'fs';
import path from 'path';

// 检查文件是否存在且为Vue文件
function checkVueFile(filePath) {
  if (!fs.existsSync(filePath)) {
    console.error(`文件不存在: ${filePath}`);
    return false;
  }
  
  if (path.extname(filePath) !== '.vue') {
    console.error(`不是Vue文件: ${filePath}`);
    return false;
  }
  
  return true;
}

// 检查Vue文件的基本结构
function checkVueStructure(filePath) {
  if (!checkVueFile(filePath)) return;
  
  const content = fs.readFileSync(filePath, 'utf8');
  
  // 检查是否有<template>, <script>, <style>标签
  const hasTemplate = /<template[\s\S]*?>[\s\S]*?<\/template>/.test(content);
  const hasScript = /<script[\s\S]*?>[\s\S]*?<\/script>/.test(content);
  
  if (!hasTemplate) {
    console.error(`${filePath}: 缺少<template>标签`);
  }
  
  if (!hasScript) {
    console.error(`${filePath}: 缺少<script>标签`);
  }
  
  // 检查script setup语法
  if (content.includes('<script setup')) {
    // 检查是否有正确的闭合
    if (!/<\/script>/.test(content)) {
      console.error(`${filePath}: <script setup>缺少闭合标签`);
    }
  }
  
  console.log(`${filePath}: 基本结构检查通过`);
}

// 检查所有组件和页面
const __filename = new URL(import.meta.url).pathname;
const __dirname = path.dirname(__filename);
const componentsDir = path.join(__dirname, 'src', 'components');
const pagesDir = path.join(__dirname, 'src', 'pages');

// 检查我们创建的组件
console.log('检查组件...');
checkVueStructure(path.join(componentsDir, 'PageHeader.vue'));
checkVueStructure(path.join(componentsDir, 'SearchToolbar.vue'));
checkVueStructure(path.join(componentsDir, 'ImageUpload.vue'));

// 检查我们修改的页面
console.log('\n检查页面...');
checkVueStructure(path.join(pagesDir, 'goods', 'Goods.vue'));
checkVueStructure(path.join(pagesDir, 'goods', 'GoodsCreate.vue'));

console.log('\n检查完成!');