<?php

namespace app\service;

use app\model\BaseModel;

class BaseService
{

    private  BaseModel $model ;
    public function __construct($tableName, $primaryKey = 'id',$timeStamp = false)
    {

        $this->model = BaseModel::make($tableName, $primaryKey, $timeStamp);
    }

    /**
     * 列表
     * @param array $where
     * @param array $field
     * @param string $orderFiled
     * @param string $order
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getList(
        array $where = [],
        array $field = ['*'],
        string $orderFiled = 'id',
        string $order = 'desc',
    ):array
    {
        return  $this->model->getList($where,$field,$orderFiled,$order);
    }

    /**
     * 带分页的列表
     * @param array $where
     * @param array $field
     * @param string $orderFiled
     * @param string $order
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getListWithPage(
        array $where = [],
        array $field = ['*'],
        string $orderFiled = 'id',
        string $order = 'desc',
        int $page = 1,
        int $perPage=10
    ):array
    {
        return  $this->model->getListWithPage($where,$field,$orderFiled,$order,$page,$perPage);
    }

    /**
     * 详情
     * @param array $where
     * @param array $field
     * @return array
     */
    public function getInfo(
        array $where = [],
        array $field = ['*']
    ):array
    {
        return $this->model->getInfo($where,$field);
    }

    /**
     * 添加
     * @param array $data
     * @return int
     */
    public function add(
        array $data
    ):int
    {
        return $this->model->add($data);
    }

    /**
     * 编辑
     * @param array $where
     * @param array $data
     * @return int
     */
    public function edit(
        array $where,
        array $data
    ):int
    {
        return $this->model->edit($where,$data);
    }
}