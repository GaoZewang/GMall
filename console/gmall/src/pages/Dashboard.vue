<template>
  <div class="dash">
    <!-- KPI：手机2列 / 平板4列 -->
    <el-row :gutter="12">
      <el-col :xs="12" :sm="12" :md="6" v-for="k in kpis" :key="k.key">
        <el-card shadow="never" class="kpi">
          <div class="kpiTitle">{{ k.title }}</div>
          <div class="kpiValue">{{ k.value }}</div>
          <div class="kpiSub" v-if="k.sub">{{ k.sub }}</div>
        </el-card>
      </el-col>
    </el-row>

    <!-- 趋势图（ECharts 双折线） -->
    <el-row :gutter="12" class="mt12">
      <el-col :xs="24">
        <el-card shadow="never">
          <div class="cardTop">
            <div class="cardTitle">趋势</div>
            <div class="chartOps">
              <el-segmented v-model="range" :options="rangeOptions" />
            </div>
          </div>

          <div ref="chartRef" class="chart" />
        </el-card>
      </el-col>
    </el-row>

    <el-row :gutter="12" class="mt12">
      <!-- 待办：小屏全宽 / 中大屏占一列 -->
      <el-col :xs="24" :sm="24" :md="10" :lg="8">
        <el-card shadow="never">
          <div class="cardTitle">待办</div>
          <div class="todoList">
            <div class="todoItem" v-for="t in todos" :key="t.key" @click="go(t.path)">
              <div class="todoName">{{ t.title }}</div>
              <el-tag type="warning" v-if="t.count">{{ t.count }}</el-tag>
              <el-tag v-else>0</el-tag>
            </div>
          </div>
        </el-card>
      </el-col>

      <!-- Top 商户 -->
      <el-col :xs="24" :sm="12" :md="7" :lg="8">
        <el-card shadow="never" class="h100">
          <div class="cardTop">
            <div class="cardTitle">Top 商户</div>
            <div class="more" @click="go('/merchants')">更多</div>
          </div>
          <div class="topList">
            <div class="topRow" v-for="(m, i) in topMerchants" :key="m.id">
              <div class="rank">{{ i + 1 }}</div>
              <div class="name">{{ m.name }}</div>
              <div class="val">{{ m.orders }}</div>
            </div>
          </div>
        </el-card>
      </el-col>

      <!-- Top 店铺 -->
      <el-col :xs="24" :sm="12" :md="7" :lg="8">
        <el-card shadow="never" class="h100">
          <div class="cardTop">
            <div class="cardTitle">Top 店铺</div>
            <div class="more" @click="go('/stores')">更多</div>
          </div>
          <div class="topList">
            <div class="topRow" v-for="(s, i) in topStores" :key="s.id">
              <div class="rank">{{ i + 1 }}</div>
              <div class="name">{{ s.name }}</div>
              <div class="val">{{ s.orders }}</div>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import * as echarts from 'echarts'

const router = useRouter()

/** KPI（mock） */
const kpis = ref([
  { key: 'newUsers', title: '新增用户', value: '128', sub: '今日' },
  { key: 'newMerchants', title: '新增商品', value: '4', sub: '今日' },
  { key: 'newStores', title: '新增店铺', value: '11', sub: '今日' },
  { key: 'orders', title: '订单数', value: '326', sub: '今日' },
])

/** 趋势：7/30 天切换（mock） */
const range = ref<'近7天' | '近30天'>('近7天')
const rangeOptions = ['近7天', '近30天'] as const

function formatNumber(n: number) {
  return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
}
function money(v: number) {
  return `¥ ${formatNumber(v)}`
}

// 7天 mock
const x7 = ['12/21', '12/22', '12/23', '12/24', '12/25', '12/26', '12/27']
const orders7 = [210, 238, 198, 260, 288, 312, 326]
const gmv7 = [9800, 11200, 8900, 13500, 14800, 16200, 17780]

// 30天 mock（简单生成，后续换接口）
const x30 = Array.from({ length: 30 }, (_, i) => `11/${String(i + 1).padStart(2, '0')}`)
const orders30 = Array.from({ length: 30 }, (_, i) => 160 + Math.round(Math.sin(i / 3) * 40) + i * 2)
const gmv30 = Array.from({ length: 30 }, (_, i) => 7200 + Math.round(Math.cos(i / 4) * 900) + i * 120)

const xDays = computed(() => (range.value === '近7天' ? x7 : x30))
const orders = computed(() => (range.value === '近7天' ? orders7 : orders30))
const gmv = computed(() => (range.value === '近7天' ? gmv7 : gmv30))

/** 待办（mock） */
const todos = ref([
  { key: 'merchantAudit', title: '待审核商户', count: 2, path: '/merchants' },
  { key: 'storeAudit', title: '待处理店铺', count: 1, path: '/stores' },
  { key: 'refund', title: '退款/售后', count: 6, path: '/orders' },
  { key: 'exception', title: '异常订单', count: 3, path: '/orders' },
])

/** Top（mock） */
const topMerchants = ref([
  { id: 1, name: '极简鞋业（官方）', orders: '1,240' },
  { id: 2, name: '轻跑运动', orders: '980' },
  { id: 3, name: '潮流工厂', orders: '760' },
])

const topStores = ref([
  { id: 11, name: '极简鞋业-南京店', orders: '420' },
  { id: 12, name: '轻跑运动-苏州店', orders: '368' },
  { id: 13, name: '潮流工厂-杭州店', orders: '340' },
])

function go(path: string) {
  router.push(path)
}

/** ECharts */
const chartRef = ref<HTMLDivElement | null>(null)
let chart: echarts.ECharts | null = null

function buildOption(): echarts.EChartsOption {
  return {
    grid: { left: 24, right: 24, top: 34, bottom: 24, containLabel: true },
    tooltip: {
      trigger: 'axis',
      formatter: (params: any) => {
        const arr: any[] = Array.isArray(params) ? params : []
        const label = arr?.[0]?.axisValue ?? ''
        const pOrders = arr.find((p) => p.seriesName === '订单数')
        const pGmv = arr.find((p) => p.seriesName === '成交额')
        const o = pOrders ? `<br/>订单数：<b>${formatNumber(Number(pOrders.data))}</b>` : ''
        const g = pGmv ? `<br/>成交额：<b>${money(Number(pGmv.data))}</b>` : ''
        return `${label}${o}${g}`
      },
    },
    legend: {
      top: 0,
      left: 0,
      icon: 'circle',
      itemWidth: 8,
      itemHeight: 8,
      textStyle: { color: '#8a8f99' },
    },
    xAxis: {
      type: 'category',
      boundaryGap: false,
      data: xDays.value,
      axisTick: { show: false },
      axisLine: { show: false },
      axisLabel: { color: '#8a8f99' },
    },
    yAxis: [
      {
        type: 'value',
        nameTextStyle: { color: '#8a8f99' },
        axisLine: { show: false },
        axisTick: { show: false },
        axisLabel: { color: '#8a8f99', formatter: (v: number) => formatNumber(v) },
        splitLine: { show: true, lineStyle: { color: '#eef0f3' } },
      },
      {
        type: 'value',
        nameTextStyle: { color: '#8a8f99' },
        axisLine: { show: false },
        axisTick: { show: false },
        axisLabel: { color: '#8a8f99', formatter: (v: number) => `¥${formatNumber(v)}` },
        splitLine: { show: false },
      },
    ],
    series: [
      {
        name: '订单数',
        type: 'line',
        smooth: true,
        data: orders.value,
        yAxisIndex: 0,
        symbol: 'circle',
        symbolSize: 6,
        lineStyle: { width: 2 },
        areaStyle: { opacity: 0.08 },
      },
      {
        name: '成交额',
        type: 'line',
        smooth: true,
        data: gmv.value,
        yAxisIndex: 1,
        symbol: 'circle',
        symbolSize: 6,
        lineStyle: { width: 2 },
        areaStyle: { opacity: 0.08 },
      },
    ],
  }
}

function renderChart() {
  if (!chartRef.value) return
  if (!chart) chart = echarts.init(chartRef.value)
  chart.setOption(buildOption(), true)
  chart.resize()
}

function handleResize() {
  chart?.resize()
}

onMounted(async () => {
  await nextTick()
  renderChart()
  window.addEventListener('resize', handleResize)
})

watch(range, async () => {
  await nextTick()
  renderChart()
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', handleResize)
  chart?.dispose()
  chart = null
})
</script>

<style scoped>
.dash{display:flex;flex-direction:column;gap:12px;}
.mt12{margin-top:12px;}
.h100{height:100%;}

/* KPI */
.kpi{border-radius:16px;}
.kpiTitle{color:var(--sub);font-size:12px;font-weight:700;}
.kpiValue{margin-top:10px;font-size:22px;font-weight:900;}
.kpiSub{margin-top:6px;color:var(--sub);font-size:12px;}

/* Header */
.cardTop{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;}
.cardTitle{font-weight:900;}
.more{color:#FF6700;font-weight:800;cursor:pointer;}
.chartOps{display:flex;gap:10px;align-items:center;flex-wrap:wrap;}

/* Chart */
.chart{
  height: 300px;
  width: 100%;
  margin-top: 10px;
}

/* Todo */
.todoList{margin-top:10px;display:flex;flex-direction:column;gap:10px;}
.todoItem{
  display:flex;justify-content:space-between;align-items:center;
  padding:10px;border:1px solid var(--line);border-radius:12px;
  cursor:pointer;background:#fff;
}
.todoItem:hover{border-color:#FF6700;}
.todoName{font-weight:700;}

/* Top list */
.topList{margin-top:10px;display:flex;flex-direction:column;gap:10px;}
.topRow{
  display:grid;grid-template-columns:22px 1fr auto;gap:10px;align-items:center;
  padding:10px;border:1px solid var(--line);border-radius:12px;
}
.rank{font-weight:900;color:var(--sub);}
.name{font-weight:700;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.val{font-weight:900;}
</style>
