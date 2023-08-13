<?php

namespace app\hall\controller;

use app\ErrorCode;
use app\jwt\JwtToken;
use app\model\Account;
use support\Request;
use taoser\exception\ValidateException;
use app\validate\facade\Validate;
use support\Log;

/**
 * 账号相关接口
 */
class AccountController
{
    /**
     * 不需要登录的方法
     */
    protected $noNeedLogin = ['register', 'login'];

    /**
     * 注册账号
     */
    public function register(Request $request)
    {
        $locale = $request->get('lang', 'zh_CN');
        locale($locale);
        try {
            Validate::check($request->get(), [
                'lang' => 'require',
                'account' => 'require|min:6|max:16|alphaDash',
                'password' => 'require|length:32|alphaNum',
                'nickname' => 'require|min:6|max:16|chsDash',
                'sdk' => 'max:32|alphaNum',
                'device' => 'require|max:32|alphaNum',
                'uuid' => 'require|max:32|alphaDash',
                'pcode' => 'integer|length:6',
                //'smscode' => 'require|integer|length:6',
                //'realname' => 'require|chs|max:12',
                
            ]);
        } catch (ValidateException $e) {
            Log::error(sprintf("%s(%s) %s validate failed. %s", $request->uid, $request->getRealIp(), $request->path(), $e->getMessage()));
            return response_json(ErrorCode::RequestParamError, config('app.debug', false) ? $e->getMessage() : '');
        }

        // 账号已经存在
        $exists = Account::where([
            'account' => $request->get('account'),
        ])->exists();
        if ($exists) {
            return response_json(ErrorCode::HallLoginRegisterAccountExists);
        }

        // 邀请码绑定上级
        $pcode = intval($request->get('pcode', 0));
        if ($pcode > 0) {
            $bind_account = Account::where([
                'accid' => $pcode,
            ])->first([
                '_id', 'accid', 'team.parents'
            ]);
            if (!$bind_account or !$bind_account->team) {
                return response_json(ErrorCode::HallLoginRegisterWrongPcode);
            }
        }

        // 创建账号
        try {
            $create_ret = Account::createAccount($request->get('account'), $request->get('password'), $request->get('nickname'),
            $request->getRealIp(), $request->get('device'), $request->get('uuid'), $request->get('realname'), $bind_account);
        } catch (\Throwable $th) {
            Log::error(sprintf("%s(%s) %s register account failed. %s", $request->uid, $request->getRealIp(), $request->path(), $th->getMessage()));
            return response_json(ErrorCode::HallLoginRegisterAccountFailed, config('app.debug', false) ? $th->getMessage() : '');
        }

        // 生成token
        $token = JwtToken::generateToken([
            'id' => $create_ret->id,
            'lang' => $locale
        ]);
        return response_success([
            'token' => $token
        ]);
    }

    /**
     * 登陆
     */
    public function login(Request $request)
    {
        $locale = $request->get('lang', 'zh_CN');
        locale($locale);
        try {
            Validate::check($request->get(), [
                'account' => 'require|min:6|max:16|alphaDash',
                'password' => 'require|length:32|alphaNum',
                'lang' => 'require',
            ]);
        } catch (ValidateException $e) {
            Log::error(sprintf("%s(%s) %s validate failed. %s", $request->uid, $request->getRealIp(), $request->path(), $e->getMessage()));
            return response_json(ErrorCode::RequestParamError, config('app.debug', false) ? $e->getMessage() : '');
        }

        $account = Account::where([
            'account' => $request->get('account'),
            'password' => $request->get('password')
        ])->first();

        if (!$account) {
            return response_json(ErrorCode::HallLoginAccountOrPasswordError);
        }

        if ($account->state == 0) {
            return response_json(ErrorCode::HallLoginAccountForbid);
        }

        $account->login_time = time();
        $account->save();

        // 生成token
        $token = JwtToken::generateToken([
            'id' => $account->id,
            'lang' => $locale
        ]);
        return response_success([
            'token' => $token
        ]);
    }

}
