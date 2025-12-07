<?php

namespace app;

use support\Request;
use app\service\UploadService;
class BaseController
{
    // 单文件上传
    public function single(Request $request)
    {
        $file = $request->file('file'); // input name="file"
        $scene=  $request->post('scene');
        if (!$file) {
            return json(['code' => 1, 'msg' => '请上传文件']);
        }
        $service = new UploadService();
        $data = $service->uploadSingleWithDedup($file, '', $scene);

        return json(['code' => 0, 'msg' => '上传成功', 'data' => $data]);
    }

    // 多文件上传
    // 多图片上传（带去重）
    public function imageMulti(Request $request)
    {
        // input name="images[]" multiple
        $files = $request->file('files') ?? [];
        $scene=  $request->post('scene');
        $service = new UploadService();
        $data = $service->uploadMultipleWithDedup($files, $scene, $scene);

        return json([
            'code' => 0,
            'msg'  => '上传完成',
            'data' => $data,
        ]);
    }
}