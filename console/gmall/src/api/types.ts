// API类型定义

export type ApiResp<T = any> = {
  code: number
  msg: string
  data: T
}

// 分页参数
export type PageParams = {
  page: number
  pageSize: number
}

// 分页响应
export type PageResp<T = any> = {
  list: T[]
  total: number
  page: number
  pageSize: number
}

// 商品相关类型
export type Goods = {
  id: string
  name: string
  price: number
  originalPrice: number
  stock: number
  status: number
  categoryId: string
  merchantId: string
  createdAt: string
  updatedAt: string
}

// 分类相关类型
export type Category = {
  id: string
  name: string
  parentId: string
  level: number
  sort: number
  status: number
  createdAt: string
  updatedAt: string
  children?: Category[]
}

// 用户相关类型
export type User = {
  id: string
  username: string
  email: string
  phone: string
  role: string
  status: number
  createdAt: string
  updatedAt: string
}
