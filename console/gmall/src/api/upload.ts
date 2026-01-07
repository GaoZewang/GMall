import { http } from './http'

type UploadResp = {
  url: string // 这里对应后端 data.url
}

/** 把相对路径变成完整可访问 URL */
function toAbsUrl(url: string) {
  if (!url) return ''
  if (/^https?:\/\//i.test(url)) return url
  const base = 'http://127.0.0.1:8787'
  return base.replace(/\/$/, '') + (url.startsWith('/') ? url : '/' + url)
}

export async function adminUploadSingle(file: File, scene = 'goods') {
  const fd = new FormData()
  fd.append('file', file)       // ✅ 字段名 file
  fd.append('scene', scene)     // ✅ 固定 goods

  // 你的 http.ts 应该会把 {code,msg,data} 解包成 data
  const data = await http.post('/admin/upload/single', fd, {
    headers: { 'Content-Type': 'multipart/form-data' },
  }) as UploadResp

  // 后端返回相对路径：/upload/...
  return toAbsUrl(data.url)
}
