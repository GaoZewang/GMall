<?php
/**
 * @Project   Gmall
 * @File      GoodsController.php
 * @Author    MrGao
 * @Date      2025/12/7 13:32
 */

namespace app\admin\controller;

use app\admin\service\GoodsService;
use app\service\BaseService;
use app\validate\BaseValidate;
use support\Request;
use support\Response;

class GoodsController
{
    /**
     * 商品列表
     * @param Request $request
     * @return Response
     */
    public function getList(Request $request):Response
    {
        $where=[];
        $params=$request->all();
        $where[]=['is_deleted','=',0];
        BaseValidate::validate($params,'list');
        $service=new BaseService('goods');
        $filed=['id','merchant_id','goods_name','subtitle','category_id','goods_status'];
        $data=$service->getListWithPage($where,$filed);
        return success($data);
    }

    /**
     * 商品详情
     * @param Request $request
     * @return Response
     */
    public function getInfo(Request $request):Response
    {
        $params=$request->get();
        BaseValidate::validate($params,'info');
        $service=new GoodsService;
        $data=$service->getGoodsInfo($params['id'],['*']);
        return success($data);
    }

    public function createOption(Request $request):Response
    {
        $goodService=new GoodsService;
        $res= $goodService->createGoods(0,$request->post());
        if($res){
            return success($res);
        }
        return error('添加失败');
    }

    public function updateOption(Request $request):Response
    {
        $goodService=new GoodsService;
        $res= $goodService->updateGoods(0,$request->post());
        if($res){
            return success($res);
        }
        return error('编辑失败');
    }

    public function deleteOption()
    {

    }
}