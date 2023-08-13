<?php

namespace app\controller;

use support\Request;
use app\jwt\JwtToken;
use app\ErrorCode;
use app\model\Test;
use support\Db;

class IndexController
{
    /**
     * 不需要登录的方法
     */
    protected $noNeedLogin = ['index', 'test'];

    public function index(Request $request)
    {
        session(['token' => 'token.123456', 'ex' => 123]);
        session(['user2' => 'token.user2', 'ex' => 123]);
        static $readme;
        if (!$readme) {
            $readme = file_get_contents(base_path('README.md'));
        }

        return $readme;
    }

    public function view(Request $request)
    {
        return view('index/view', ['name' => 'webman']);
    }

    private function json(Request $request)
    {
        return json(['code' => 0, 'msg' => 'ok']);
    }

    public function test() {
        $user = [
            'id'  => 2022,
            'lang' => 'en',
        ];
        $token = JwtToken::generateToken($user);
        //Db::table('account')->where('_id', 1)->first();
        //Db::collection('test')->insert([1,2,3]);
        //return json(Db::collection('test')->get());
        return json(['code' => 0, 'msg' => 'ok', 'data' => $token]);
    }

    public function jwttest() {
        
        return response_success(['a' => 1, 'b' => 'bs']);
        //return json(['code' => 0, 'msg' => 'ok', 'data' => trans('hello')]);
    }

}
