<?php
namespace app\service;

use app\model\BaseModel;
use support\Model;
use Webman\Http\UploadFile;
use support\Db;

class UploadService
{
    protected string $root;
    protected string $url;
    protected string $table = 'upload_file';

    public function __construct(?array $config = null)
    {
        $cfg        = $config ?? config('upload', []);
        $this->root = rtrim($cfg['root'] ?? public_path() . '/upload', '/');
        $this->url  = rtrim($cfg['url'] ?? '/upload', '/');
    }


    /**
     * 返回文件列表
     * @param array $where
     * @param int $page
     * @param int $pageSize
     * @return array
     */
    public function getFileList(array $where, int $page, int $pageSize)
    {
        $model=BaseModel::make($this->table);
        return $model->getListWithPage($where, $page, $pageSize);

    }

    /**
     * === 带去重 + 自动按类型分目录的单文件上传 ===
     *
     * @param UploadFile $file
     * @param string|null $dir   为空则自动根据文件类型决定目录（images/videos/docs 等）
     * @param string $scene      业务场景：goods/avatar/banner...
     */
    public function uploadSingleWithDeduce(UploadFile $file, ?string $dir = null, string $scene = ''): array
    {
        if (!$file->isValid()) {
            throw new \RuntimeException('上传文件无效');
        }

        $storage = 'local';
        $tmpPath = $file->getPathname();

        // 1. 计算文件 hash（内容去重）
        $hash = hash_file('sha256', $tmpPath);

        // 2. 是否已有相同文件
        $exist = Db::table($this->table)
            ->where('hash', $hash)
            ->where('storage', $storage)
            ->first();

        if ($exist) {
            Db::table($this->table)
                ->where('id', $exist->id)
                ->update([
                    'ref_count'  => Db::raw('ref_count + 1'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            return [
                //'id'          => $exist->id,
                //'storage'     => $exist->storage,
                //'path'        => $exist->path,
                'url'         => $exist->url,
                //'hash'        => $exist->hash,
                //'size'        => $exist->size,
                //'mime_type'   => $exist->mime_type,
                //'width'       => $exist->width,
                //'height'      => $exist->height,
                //'scene'       => $exist->scene,
                //'ref_count'   => $exist->ref_count + 1,
                //'from_cache'  => true,
            ];
        }

        // 3. 自动判断目录：如果没指定 $dir，就按文件类型自动选择
        if (empty($dir)) {
            $dir = $this->resolveDirByFile($file);
        }

        // 4. 判断图片宽高（图片的话）
        $width = null;
        $height = null;
        $imageInfo = @getimagesize($tmpPath);
        if ($imageInfo) {
            $width = $imageInfo[0] ?? null;
            $height = $imageInfo[1] ?? null;
        }

        // 5. 生成保存路径
        [$relativePath, $absolutePath] = $this->buildPath($file, $dir);
        $this->ensureDir(dirname($absolutePath));

        // 6. 移动文件
        $file->move($absolutePath);

        $size      = filesize($absolutePath);
        $mimeType  = $file->getUploadMimeType() ?: ($imageInfo['mime'] ?? 'application/octet-stream');
        $extension = $file->getUploadExtension() ?: pathinfo($absolutePath, PATHINFO_EXTENSION);

        $url = $this->url . '/' . $relativePath;

        // 7. 入库
        $now = date('Y-m-d H:i:s');
        $id = Db::table($this->table)->insertGetId([
            'hash'       => $hash,
            'size'       => $size,
            'mime_type'  => $mimeType,
            'extension'  => $extension,
            'width'      => $width,
            'height'     => $height,
            'storage'    => $storage,
            'path'       => $relativePath,
            'url'        => $url,
            'scene'      => $scene,
            'ref_count'  => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [
            //'id'          => $id,
            //'storage'     => $storage,
            //'path'        => $relativePath,
            'url'         => $url,
            //'hash'        => $hash,
            //'size'        => $size,
            //'mime_type'   => $mimeType,
            //'width'       => $width,
            //'height'      => $height,
            //'scene'       => $scene,
            //'ref_count'   => 1,
            //'from_cache'  => false,
        ];
    }

    /**
     * 多文件上传（自动按类型分目录 + 去重）
     */
    public function uploadMultipleWithDeduce(array $files, ?string $dir = null, string $scene = ''): array
    {
        $result = [];
        foreach ($files as $file) {
            if (!$file instanceof UploadFile) {
                continue;
            }
            if (!$file->isValid()) {
                continue;
            }

            $result[] = $this->uploadSingleWithDeduce($file, $dir, $scene);
        }
        return $result;
    }

    /* ========== 关键新增：根据文件类型自动选择目录 ========== */

    /**
     * 根据文件的 MIME 类型 / 扩展名，返回对应目录名
     *
     * 规则示例：
     *   image/*                      -> images
     *   video/*                      -> videos
     *   audio/*                      -> audios
     *   pdf/doc/xls/ppt/txt          -> docs
     *   压缩包 zip/rar/7z/tar/gz     -> archives
     *   其他                         -> others
     */
    protected function resolveDirByFile(UploadFile $file): string
    {
        $tmpPath = $file->getPathname();
        $mime    = $file->getUploadMimeType();
        $ext     = strtolower($file->getUploadExtension() ?: pathinfo($file->getUploadName(), PATHINFO_EXTENSION));

        // 若框架没给 mime，则用 finfo 再测一次
        if (!$mime && is_file($tmpPath)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime = finfo_file($finfo, $tmpPath) ?: '';
                finfo_close($finfo);
            }
        }
        $mime = strtolower((string)$mime);

        // 图片
        if (str_starts_with($mime, 'image/')) {
            return 'images';
        }

        // 视频
        if (str_starts_with($mime, 'video/')) {
            return 'videos';
        }

        // 音频
        if (str_starts_with($mime, 'audio/')) {
            return 'audios';
        }

        // 文档类
        $docExt = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'md', 'csv'];
        if (in_array($ext, $docExt, true)) {
            return 'docs';
        }
        if (in_array($mime, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            'text/markdown',
        ], true)) {
            return 'docs';
        }

        // 压缩包 / 归档
        $archiveExt = ['zip', 'rar', '7z', 'tar', 'gz', 'bz2'];
        if (in_array($ext, $archiveExt, true)) {
            return 'archives';
        }
        if (in_array($mime, [
            'application/zip',
            'application/x-rar-compressed',
            'application/x-7z-compressed',
            'application/x-tar',
            'application/gzip',
        ], true)) {
            return 'archives';
        }

        // 其他类型
        return 'others';
    }

    /* ========== 下边是之前就有的路径工具方法 ========== */

    protected function buildPath(UploadFile $file, string $dir): array
    {
        $date     = date('Ymd');
        $ext      = $file->getUploadExtension() ?: 'bin';
        $filename = date('His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        $relative = trim($dir, '/') . '/' . $date . '/' . $filename;
        $absolute = $this->root . '/' . $relative;

        return [$relative, $absolute];
    }

    protected function ensureDir(string $dir): void
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }
}
