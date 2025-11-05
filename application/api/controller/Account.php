<?php

namespace app\api\controller;

use think\Controller;
use think\Db;
use think\Request;

class Account extends Controller
{
    public function index(Request $request)
    {
        header("Access-Control-Allow-Origin:*");
        $dat = input();
        $user_id = intval($dat['uid']);
        $res = sure_logout($user_id);
        return $res;
    }
}