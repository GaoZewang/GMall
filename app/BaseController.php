<?php

namespace app;

use support\Request;
use app\service\UploadService;
use support\Response;

class BaseController
{

    /**
     * 文件列表
     * @param Request $request
     * @param UploadService $service
     * @return Response
     */
    public function getFileList(Request $request ,UploadService $service):Response
    {
        $where=[];
        $params=$request->get();
        $where[]=['scene','=',$params['scene']];
        $page = $request->get('page', 1);
        $pageSize = $request->get('pageSize', 10);
        $data=$service->getFileList($where, $page, $pageSize);
        return success($data);
    }

    /**
     * 单文件上传
     * @param Request $request
     * @param UploadService $service
     * @return Response
     */
    public function single(Request $request,UploadService $service):Response
    {
        $file = $request->file('file'); // input name="file"
        $scene=  $request->post('scene');
        if (!$file) {
            return json(['code' => 1, 'msg' => '请上传文件']);
        }
        $data = $service->uploadSingleWithDedup($file, '', $scene);
        return success($data);
    }

    /**
     * 多文件上传
     * @param Request $request
     * @param UploadService $service
     * @return Response
     */
    public function imageMulti(Request $request,UploadService $service):Response
    {
        $files = $request->file('files') ?? [];
        $scene=  $request->post('scene');
        $data = $service->uploadMultipleWithDedup($files, $scene, $scene);
        return success($data);
    }
}