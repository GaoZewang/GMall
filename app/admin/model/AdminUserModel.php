<?php

namespace app\admin\model;

use support\Model;

class AdminUserModel extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'admin_user';

    /**
     * 重定义主键，默认是id
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 指示是否自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * 构建基础查询对象（用于消除重复代码）
     *
     * @param array       $where       查询条件
     * @param string      $orderField  排序字段
     * @param string      $order       排序方式 asc/desc
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function buildQuery(array $where = [], string $orderField = 'id', string $order = 'desc')
    {
        return $this->where($where)->orderBy($orderField, $order);
    }

    /**
     * 获取列表数据（不带分页）
     * @param array       $where       查询条件
     * @param array|mixed $fields      字段
     * @param string      $orderField  排序字段
     * @param string      $order       排序方式 asc/desc
     * @param int         $page        当前页
     * @param int         $perPage     每页条数
     *
     * @return array
     */
    public function getList(
        array  $where = [],
               $fields = ['*'],
        string $orderField = 'id',
        string $order = 'desc',
        int    $page = 1,
        int    $perPage = 10
    ): array {
        return $this->buildQuery($where, $orderField, $order)
            ->forPage($page, $perPage)
            ->get($fields)
            ->toArray();
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
    public function getListWithPage(
        array  $where = [],
               $fields = ['*'],
        string $orderField = 'id',
        string $order = 'desc',
        int    $page = 1,
        int    $perPage = 10
    ): array {
        // 使用公共方法构建基础查询
        $query = $this->buildQuery($where, $orderField, $order);
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
     * 获取单个管理员用户信息
     * @param array       $where 查询条件
     * @param array|mixed $field 查询字段
     * @return array
     */
    public function getAdminUserInfo(array $where, $field = ['*']): array
    {
        $field=['id','username','password','email','phone','role_id','status','last_login','last_ip'];
        return $this->where($where)->first($field)?->toArray() ?? [];
    }

    /**
     * 添加管理员用户
     * @param array $data 插入的数据
     * @return bool
     */
    public function addAdminUser(array $data): bool
    {
        return $this->insert($data);
    }

    /**
     * 编辑管理员用户
     * @param array $where 条件
     * @param array $data  更新数据
     * @return bool
     */
    public function editAdminUser(array $where, array $data): bool
    {
        return $this->where($where)->update($data);
    }
}
