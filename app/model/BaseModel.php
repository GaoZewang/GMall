<?php

namespace app\model;

use support\Db;
use support\Model;

class BaseModel extends Model
{
    // 默认不启用时间戳
    public $timestamps = false;

    /**
     * 这里一定要兼容父类：第一个参数必须是 $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * 工厂方法：传表名、主键、时间戳，返回一个配置好的模型实例
     */
    public static function make(string $table, string $primaryKey = 'id', bool $timestamps = false): static
    {
        $model = new static();          // 这里必须无参，框架才不会炸
        $model->setTable($table);      // 设置表名
        $model->primaryKey = $primaryKey;
        $model->timestamps = $timestamps;
        return $model;
    }


    public function getTableInfo(): array
    {
        $fieldTypes = Db::select("
        SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE ,CHARACTER_MAXIMUM_LENGTH,COLUMN_DEFAULT,COLUMN_KEY,EXTRA,NUMERIC_PRECISION
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_NAME = :table", ['table' => 'gm_'.$this->table]);
        return $fieldTypes;
    }


    /**
     * 带分页的列表
     * @param array       $where       查询条件
     * @param array|mixed $fields      字段
     * @param string      $orderField  排序字段
     * @param string      $order       排序方式 asc/desc
     * @param int         $page        当前页
     * @param int         $perPage     每页条数
     *
     * @return array
     */
    public  function getListWithPage(
        array  $where = [],
               $fields = ['*'],
        string $orderField = 'id',
        string $order = 'desc',
        int    $page = 1,
        int    $perPage = 10
    ): array {
        // 构建基础查询
        $query = $this->where($where)->orderBy($orderField, $order);
        // 总数用 clone 出来的查询计算，避免被分页影响
        $total = (clone $query)->count();
        // 当前页数据
        $list = $query->forPage($page, $perPage)->get($fields)->toArray();
        // 计算总页数
        $lastPage = $perPage > 0 ? (int)ceil($total / $perPage) : 0;
        return [
            'list' => $list,
            'pagination' => [
                'total'        => $total,
                'per_page'     => $perPage,
                'current_page' => $page,
                'last_page'    => $lastPage,
            ],
        ];
    }


    /**
     * 列表
     */
    public function getList($where, $fields, $orderField, $order): array
    {
        return $this->where($where)
            ->orderBy($orderField, $order)
            ->get($fields)
            ->toArray();
    }

    /**
     * 详情
     */
    public function getInfo($where, $fields): array
    {
        $info = $this->where($where)->first($fields);
        return $info ? $info->toArray() : [];
    }

    /**
     * 创建
     */
    public function add(array $attributes): int
    {
        return $this->insert($attributes);
    }

    /**
     * 修改
     */
    public function edit(array $where, array $attributes): int
    {
        return $this->where($where)->update($attributes);
    }

    /**
     * 删除
     * @param array $where
     * @return int
     */
    public function del(array $where): int
    {
        return $this->where($where)->delete();
    }
}
