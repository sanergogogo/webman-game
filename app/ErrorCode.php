<?php

namespace app;

// 错误码
class ErrorCode {
    // 0-999 系统相关
    // 成功
    const Success = 0;

// #region 1000-1999 jwt相关
    // 内部错误
    const JWTInternalError = 1000;
    // 令牌无效
    const JWTTokenInvalid = 1001;
    // 令牌签名无效
    const JWTTokenSignatureInvalid = 1002;
    // 令牌尚未生效
    const JWTTokenBeforeValid = 1003;
    // 令牌会话已过期，请再次登录！
    const JWTTokenExpired = 1001;
    // 令牌获取的扩展字段不存在
    const JWTTokenUnexpectedValue = 1004;
    // 请求未携带authorization信息
    const JWTTokenNoAuthorization = 1005;
    // 非法的authorization信息
    const JWTTokenAuthorizationError = 1006;
    
    // 刷新令牌无效
    const JWTRefreshTokenInvalid = 1010;
    // 刷新令牌签名无效
    const JWTRefreshTokenSignatureInvalid = 1012;
    // 刷新令牌尚未生效
    const JWTRefreshTokenBeforeValid = 1013;
    // 刷新令牌会话已过期，请再次登录！
    const JWTRefreshTokenExpired = 1014;
    // 刷新令牌获取的扩展字段不存在
    const JWTRefreshTokenUnexpectedValue = 1015;

    // 身份验证令牌已失效
    const JWTCacheTokenInvalid = 1020;
    // 该账号已在其他设备登录，强制下线
    const JWTCacheTokenReplace = 1021;
// #endregion

// #region 2000-2999 参数错误
    const RequestParamError = 2000;
// #endregion

// 100000-499999 大厅接口------------------------------

// #region 100000-100099 登陆相关
    // 账号或者密码错误
    const HallLoginAccountOrPasswordError = 100000;
    // 封号
    const HallLoginAccountForbid = 100001;
    // 错误的邀请码
    const HallLoginRegisterWrongPcode = 100002;
    // 账号已经存在
    const HallLoginRegisterAccountExists = 100003;
    // 账号注册失败 比如账号赢存在
    const HallLoginRegisterAccountFailed = 100004;
// #endregion

// #region 100100-100199 个人信息相关
    const HallProfileError = 100100;
// #endregion


// 500000-999999 api接口------------------------------

// #region 500000-500099 登陆相关
// #endregion

// #region 500100-500199 个人信息相关
// #endregion

// >=1000000 作为子游戏接口------------------------------

// #region 1001000-1001999 麻将胡了
    const GameMjhlError = 1001000;
// #endregion

// #region 1002000-1002999 水果机
    const GameSlotError = 1002000;
// #endregion

}
