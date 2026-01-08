<?php

namespace app\admin\controller;

use app\service\BaseService;
use support\Request;
use support\Response;
use app\admin\service\SystemSettingService;
use app\admin\validate\SystemSettingValidate;
use app\validate\BaseValidate;

class SystemSettingController
{



    /**
     * 列表
     * @param Request $request
     * @return Response
     */
    public function getList(Request $request):response
    {
        $params=$request->all();
        BaseValidate::validate($params,'list');
        $filed=['id','set_tag','set_name'];
        $service=new BaseService('system_setting');
        $data=$service->getListWithPage(
            [],$filed,'id','asc',
            $params['page'],$params['per_page']
        );
        return success($data) ;
    }

    /**
     * 详情
     * @param Request $request
     * @return Response
     */
    public function getInfo(Request $request):response
    {
        $params=$request->all();
        SystemSettingValidate::validate($params,'info');
        $filed=['id','set_tag','set_name','set_content','set_template','create_time','update_time'];
        $service=new SystemSettingService();
        $data=$service->getInfo(['id'=>$params['id']],$filed);
        return success($data) ;
    }

    /**
     * 添加
     * @param Request $request
     * @return Response
     */
    public function createOperation(Request $request):response
    {
        $params=$request->post();
        SystemSettingValidate::validate($params,'add');

        $params['create_time']=date('Y-m-d H:i:s',time());
        $service=new SystemSettingService();
        if($service->add($params))  {
          return success() ;
        }
        return error() ;
    }

    /**
     * 编辑
     * @param Request $request
     * @return Response
     */
    public function updateOperation(Request $request):response
    {//"0": "n", "1": "u", "2": "l", "3": "l",
        $params=$request->post();
        $params['update_time']=date('Y-m-d H:i:s',time());
        SystemSettingValidate::validate($params,'edit');
        $service=new SystemSettingService();
        if($service->edit(['id'=>$params['id']],$params))  {
            return success() ;
        }
        return error() ;
    }

    /**
     * 删除
     * @param Request $request
     * @return Response
     */
    public function delOperation(Request $request):response
    {
        $params=$request->post();
        SystemSettingValidate::validate($params,'delete');
        $service=new SystemSettingService();
        if($service->delete(['id'=>$params['id']]))  {
            return success() ;
        }
        return error() ;
    }
}
