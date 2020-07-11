<?php

namespace app\common\library;

use addons\auth\app\admin\model\AuthRule;
use easyadmin\Tree;
use think\Exception;

class Menu
{

    /**
     * 创建菜单
     * @param array $menu
     * @param mixed $parent 父类的name或pid
     * @param boolean $type 是否为安装应用
     */
    public static function create($menu, $parent = 0 , $type = false)
    {
        if (!is_numeric($parent)) {
            $parentRule = AuthRule::getByName($parent);
            $pid = $parentRule ? $parentRule['id'] : 0;
            //更新$menu为name的分类状态,首次传入
            if ($pid) {
                AuthRule::update(['ismenu'=>1,'status'=>'normal'],['id'=>$pid]);
            }
        } else {
            $pid = $parent;
        }
        $allow = array_flip(['file', 'name', 'title', 'icon', 'condition', 'remark', 'ismenu','weigh']);
        foreach ($menu as $k => $v) {
            $hasChild = isset($v['sublist']) && $v['sublist'] ? true : false;

            $data = array_intersect_key($v, $allow);

            $data['ismenu'] = isset($data['ismenu']) ? $data['ismenu'] : ($hasChild ? $type? 2 : 1 : 0);
            $data['icon'] = isset($data['icon']) ? $data['icon'] : ($hasChild ? 'fa fa-list' : 'fa fa-circle-o');
            $data['pid'] = $pid;
            $data['status'] = 'normal';
            try {
                $rule = AuthRule::getByName($data['name']);
                if (!$rule) {
                    $menu = AuthRule::create($data);
                    if ($hasChild) {
                        self::create($v['sublist'], $menu->id);
                    }
                }else{
                    if ($hasChild) {
                        self::create($v['sublist'], $rule->id);
                    }
                }
            } catch (PDOException $e) {
                throw new Exception($e->getMessage());
            }
        }
    }

    /**
     * 删除菜单
     * @param string $name 规则name
     * @return boolean
     */
    public static function delete($name)
    {
        $ids = self::getAuthRuleIdsByName($name);
        if (!$ids) {
            return false;
        }
        AuthRule::destroy($ids);
        return true;
    }

    /**
     * 启用菜单
     * @param string $name
     * @return boolean
     */
    public static function enable($name)
    {
        $ids = self::getAuthRuleIdsByName($name);
        if (!$ids) {
            return false;
        }
        AuthRule::where('id', 'in', $ids)->update(['status' => 'normal']);
        return true;
    }

    /**
     * 禁用菜单
     * @param string $name
     * @return boolean
     */
    public static function disable($name)
    {
        $ids = self::getAuthRuleIdsByName($name);
        if (!$ids) {
            return false;
        }
        AuthRule::where('id', 'in', $ids)->update(['status' => 'hidden']);
        return true;
    }

    /**
     * 导出指定名称的菜单规则
     * @param string $name
     * @return array
     */
    public static function export($name)
    {
        $ids = self::getAuthRuleIdsByName($name);
        if (!$ids) {
            return [];
        }
        $menuList = [];
        $menu = AuthRule::getByName($name);
        if ($menu) {
            $ruleList = collection(AuthRule::where('id', 'in', $ids)->select())->toArray();
            $menuList = Tree::instance()->init($ruleList)->getTreeArray($menu['id']);
        }
        return $menuList;
    }

    /**
     * 导出指定名称的菜单规则2
     * @param string $name
     * @param boolean $withself 是否包含自身
     * @return array
     */
    public static function export2($name,$withself=true)
    {
        $ids = self::getAuthRuleIdsByName($name,$withself);
        if (!$ids) {
            return [];
        }
        $menu = AuthRule::getByName($name);
        if ($menu) {
            $ruleList = collection(AuthRule::whereIn('id', $ids)->where('status', 'normal')->where('ismenu', 2)->select())->toArray();
        }
        return $ruleList;
    }

    /**
     * 根据名称获取规则IDS
     * @param string $name
     * @param boolean $withself 是否包含自身
     * @return array
     */
    public static function getAuthRuleIdsByName($name,$withself=true)
    {
        $ids = [];
        $menu = AuthRule::getByName($name);
        if ($menu) {
            // 必须将结果集转换为数组
            $ruleList = collection(AuthRule::order('weigh', 'desc')->field('id,pid,name')->select())->toArray();
            // 构造菜单数据
            $ids = Tree::instance()->init($ruleList)->getChildrenIds($menu['id'], $withself);
        }
        return $ids;
    }

    /**
     * 根据名称获取规则ID
     * @param string $name
     * @return integer
     */
    public static function getAuthRuleIdByName($name)
    {
        $item = AuthRule::where(['name'=>$name])->find();
        if (!$item) {
            return 0;
        }
        return $item['pid'];
    }

}
