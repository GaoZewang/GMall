<?php
/**
 * @Project   Gmall
 * @File      GoodsController.php
 * @Author    MrGao
 * @Date      2025/12/7 13:32
 */

namespace app\admin\controller;

use app\service\GoodsService;
use app\admin\validate\GoodsValidate;
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
    public function getList(Request $request,GoodsService $goodsService):Response
    {
        $params=$request->all();
        BaseValidate::validate($params,'list');
        $data=$goodsService->getList($params);
        return success($data);
    }

    /**
     * 商品详情
     * @param Request $request
     * @return Response
     */
    public function getInfo(Request $request,GoodsService $goodsService):Response
    {
        $params=$request->get();
        BaseValidate::validate($params,'info');
        $data=$goodsService->getGoodsInfo($params['id'],['*']);
        return success($data);
    }

    /**
     * 添加商品
     * @param Request $request
     * @return Response
     */
    public function createOption(Request $request,GoodsService $goodsService):Response
    {
        $params=$request->post();
        $params['merchant_id']=1;
        GoodsValidate::validate($params,'add');
        $res= $goodsService->createGoods($params['merchant_id'],$request->post());
        if($res){
            return success($res);
        }
        return error('添加失败');
    }

    /**
     * 修改商品
     * @param Request $request
     * @return Response
     */
    public function updateOption(Request $request,GoodsService $goodsService):Response
    {
        $params=$request->post();
        $params['merchant_id']=1;
        GoodsValidate::validate($params,'edit');
        $res= $goodsService->updateGoods($params['merchant_id'],$request->post());
        if($res){
            return success();
        }
        return error('编辑失败');
    }

    /**
     * 修改商品状态(上下架)
     * @param Request $request
     * @return Response
     */
    public function updateGoodsStatus(Request $request):Response
    {
        $params=$request->post();
        $goodsService=new BaseService('goods');
        GoodsValidate::validate($request->post(),'status');
        $res= $goodsService->edit(['id'=>$params['id']],['goods_status'=>$params['status']]);
        if($res){
            return success();
        }
        return error('编辑失败');
    }

    /**
     * 删除商品
     * @param Request $request
     * @return Response
     */
    public function deleteOption(Request $request,GoodsService $goodsService):Response
    {
        $params=$request->get();
        BaseValidate::validate($params,'info');
        $id=explode(',',$params['id']);
        $res= $goodsService->deleteGoods(['id'=>$id]);
        if($res){
            return success();
        }
        return error('删除失败');
    }
}