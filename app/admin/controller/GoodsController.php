<?php
/**
 * @Project   Gmall
 * @File      GoodsController.php
 * @Author    MrGao
 * @Date      2025/12/7 13:32
 */

namespace app\admin\controller;

use app\admin\service\GoodsService;
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
    public function getList(Request $request):Response
    {
        $where=[];
        $params=$request->all();
        $where[]=['is_deleted','=',0];
        if(!empty($params['goods_name'])){
            $where[]=['goods_name','like',"%{$params['goods_name']}%"];
        }
        if(!empty($params['category_id'])){
            $where[]=['category_id','=',$params['category_id']];
        }
        if(isset($params['goods_status'])&&in_array($params['goods_status'],[0,1])){
            $where[]=['goods_status','=',$params['goods_status']];
        }
        BaseValidate::validate($params,'list');
        $service=new BaseService('goods');
        $filed=['id','merchant_id','goods_code','goods_name','subtitle','category_id','cover_image','goods_status'];
        $data=$service->getListWithPage($where,$filed,'id','desc',$params['page'],$params['per_page']);
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

    /**
     * 添加商品
     * @param Request $request
     * @return Response
     */
    public function createOption(Request $request):Response
    {
        $goodService=new GoodsService;
        $params=$request->post();
        $params['merchant_id']=1;
        GoodsValidate::validate($params,'add');
        $res= $goodService->createGoods($params['merchant_id'],$request->post());
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
    public function updateOption(Request $request):Response
    {
        $goodService=new GoodsService;
        $params=$request->post();
        $params['merchant_id']=1;
        GoodsValidate::validate($params,'edit');
        $res= $goodService->updateGoods($params['merchant_id'],$request->post());
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
    public function deleteOption(Request $request):Response
    {
        $goodService=new GoodsService;
        $params=$request->get();
        BaseValidate::validate($params,'info');
        $id=explode(',',$params['id']);
        $res= $goodService->deleteGoods(['id'=>$id]);
        if($res){
            return success();
        }
        return error('删除失败');
    }
}