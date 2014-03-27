<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-25 19:36:05
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-03-27 15:35:50
*/

class AdminUserController extends AdminController
{

    public $leftNav;

    public function __construct(){
        parent::__construct();
        //set top nav
        $this->topNav['user']['class'] = 'active';
        View::share('topNav', $this->topNav);

        $this->leftNav = array(
            'user' => array(
                'name' => 'admin.all_user',
                'url' => 'admin/user',
                'class' => ''),
            'role' => array(
                'name' => 'admin.role_manage',
                'url' => 'admin/role',
                'class' => ''),
            'permission' => array(
                'name' => 'admin.permission_manage',
                'url' => 'admin/permission',
                'class' => ''),
        );
    }

    public function getUser(){
        $this->leftNav['user']['class'] = 'active';
        $users = User::paginate(Config::get('app.paginate'));
        return View::make('admin.user.user')
            ->with('title', Lang::get('admin.user'))
            ->with('leftNav', $this->leftNav)
            ->with('users', $users);
    }

    public function getRole(){
        $this->leftNav['role']['class'] = 'active';
        $roles = Role::paginate(Config::get('app.paginate'));
        return View::make('admin.user.role')
            ->with('title', Lang::get('admin.role_manage'))
            ->with('leftNav', $this->leftNav)
            ->with('roles', $roles);
    }

    public function getPermission(){
        $this->leftNav['permission']['class'] = 'active';
        $permissions = Permission::paginate(Config::get('app.paginate'));
        return View::make('admin.user.permission')
            ->with('title', Lang::get('admin.permission_manage'))
            ->with('leftNav', $this->leftNav)
            ->with('permissions', $permissions);
    }
}