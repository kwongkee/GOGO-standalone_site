<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Session;

class User extends Controller {

    public function register() {
        if (request()->isPost()) {
            $username = input('post.username');
            $password = input('post.password');
            $data = [
                'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT), // 修复：bcrypt
            ];
            Db::name('user')->insert($data);
            return $this->success('注册成功');
        }
        return $this->fetch();
    }

    public function logout() {
        Session::clear();
        setcookie('user_token', '', time() - 3600, '/');
        session_regenerate_id(true); // 修复：安全退出
        $this->redirect('/');
    }
}
