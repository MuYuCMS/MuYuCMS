<?php
/**
 * Auth权限验证类
 */
namespace auth;
use think\Db;
use think\facade\Session;
use think\facade\Config;
class Auth
{
    protected $_config = [
        'auth_on'           =>  true,                // 认证开关
        'auth_type'         =>  1,                   // 认证方式，1为实时认证；2为登录认证。
        'auth_group'        =>  'role',            // 权限组数据表名
        'auth_rule'         =>  'rule',             // 权限规则表
        'auth_user'         =>  'admin',            // 管理员信息表
        'auth_user_id_field'=>  'id',                // 管理员表ID字段名
    ];

    public function __construct()
    {
        if (Config::pull('auth')) {
            $this->_config = array_merge($this->_config, Config::pull('auth'));
        }
    }

    /**
     * 检查权限
     * @param  string|array  $name     需要验证的规则列表，支持逗号分隔的权限规则或索引数组
     * @param  integer  $uid      认证用户ID
     * @param  string   $relation 如果为 'or' 表示满足任一条规则即通过验证;如果为 'and' 则表示需满足所有规则才能通过验证
     * @param  string   $mode     执行check的模式
     * @param  integer  $type     规则类型
     * @return boolean           通过验证返回true;失败返回false
     */
    public function check($name, $uid, $relation = 'or', $mode = 'url', $type = 1)
    {
        if (!$this->_config['auth_on']) {
            return true;
        }

        $authList = $this->getAuthList($uid, $type);
        if (is_string($name)) {
            $name = strtolower($name);
            if (strpos($name, ',') !== false) {
                $name = explode(',', $name);
            } else {
                $name = [$name];
            }
        }
        $list = [];
        if ($mode === 'url') {
            $REQUEST = unserialize(strtolower(serialize($_REQUEST)));
        }

        foreach ($authList as $auth) {
            $query = preg_replace('/^.+\?/U', '', $auth);
            if ($mode === 'url' && $query != $auth) {
                parse_str($query, $param); // 解析规则中的param
                $intersect = array_intersect_assoc($REQUEST, $param);
                $auth = preg_replace('/\?.*$/U', '', $auth);
                if (in_array($auth, $name) && $intersect == $param) {
                    $list[] = $auth;
                }
            } elseif (in_array($auth, $name)) {
                $list[] = $auth;
            }
        }

        if ($relation === 'or' && !empty($list)) {
            return true;
        }

        $diff = array_diff($name, $list);

        if ($relation === 'and' && empty($diff)) {
            return true;
        }
        return false;
    }

    /**
     * 根据用户ID获取用户组，返回值为数组
     * @param  integer $uid 用户ID
     * @return array      用户所属用户组 ['uid'=>'用户ID', 'rid'=>'用户组ID', 'name'=>'用户组名', 'jurisdiction'=>'用户组拥有的规则ID，多个用英文,隔开']
     */
    public function getGroups($uid)
    {
        static $groups = [];

        if (isset($groups[$uid])) {
            return $groups[$uid];
        }

        /*$user_groups = Db::name($this->_config['auth_group_access'])
            ->alias('a')
            ->where('a.uid', $uid)
            ->where('g.status', 1)
            ->join($this->_config['auth_group'].' g', "a.group_id = g.id")
            ->field('uid,group_id,name,rules')
            ->select();*/
        $user_groups = Db::name($this->_config['auth_user'])
            ->alias('a')
            ->where('a.id',$uid)
            ->join($this->_config['auth_group'].' g',"a.roles=g.id")
            ->where('g.status',1)
            ->field('a.id as uid,g.id as rid,jurisdiction')
            ->select();
        $groups[$uid] = $user_groups ?: [];
        return $groups[$uid];
    }

    /**
     * 获得权限列表
     * @param  integer $uid  用户ID
     * @param  integer $type 规则类型
     * @return array       权限列表
     */
    protected function getAuthList($uid, $type)
    {
        static $_authList = [];

        $t = implode(',', (array)$type);
        if (isset($_authList[$uid.$t])) {
            return $_authList[$uid.$t];
        }

        if ($this->_config['auth_type'] == 2 && Session::has('_AUTH_LIST_'.$uid.$t)) {
            return Session::get('_AUTH_LIST_'.$uid.$t);
        }

        // 读取用户所属用户组
        $groups = $this->getGroups($uid);
        $ids = []; // 保存用户所属用户组设置的所有权限规则ID
        foreach ($groups as $g) {
            $ids = array_merge($ids, explode(',', trim($g['jurisdiction'], ',')));
        }

        $ids = array_unique($ids);
        if (empty($ids)) {
            $_authList[$uid.$t] = [];
            return [];
        }

        $map = [
            ['id', 'in', $ids],
            ['type', '=', $type],
            ['status', '=', 1],
            ['level', '=', 2],
        ];

        // 读取用户组所有权限规则
        $rules = Db::name($this->_config['auth_rule'])->where($map)->field('condition,href')->select();

        // 循环规则，判断结果。
        $authList = [];

        foreach ($rules as $rule) {
            if (!empty($rule['condition'])) { // 根据condition进行验证
                $user = $this->getUserInfo($uid); // 获取用户信息,一维数组

                $command = preg_replace('/\{(\w*?)\}/', '$user[\'\\1\']', $rule['condition']);
                // dump($command); // debug
                @(eval('$condition=('.$command.');'));
                if ($condition) {
                    $authList[] = strtolower($rule['href']);
                }
            } else {
                // 只要存在就记录
                $authList[] = strtolower($rule['href']);
            }
        }

        $_authList[$uid.$t] = $authList;

        if ($this->_config['auth_type'] == 2) {
            Session::set('_AUTH_LIST_'.$uid.$t, $authList);
        }
        return array_unique($authList);
    }

    /**
     * 获得用户资料,根据自己的情况读取数据库
     */
    protected function getUserInfo($uid) {
        static $userinfo = [];
        if (!isset($userinfo[$uid])) {
            $userinfo[$uid] = Db::name($this->_config['auth_user'])->where((string)$this->_config['auth_user_id_field'], $uid)->find();
        }
        return $userinfo[$uid];
    }
}