<?php
/**
 * @desc AuthorizationMiddleware
 * @author Tinywan(ShaoBo Wan)
 * @email 756684177@qq.com
 * @date 2022/2/21 22:18
 */

declare(strict_types=1);

namespace app\middleware;

use app\ErrorCode;
use app\jwt\JwtCacheTokenException;
use app\jwt\JwtToken;
use app\jwt\JwtTokenException;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;
use ReflectionClass;

class AuthorizationMiddleware implements MiddlewareInterface
{
    /**
     * @param Request $request
     * @param callable $handler
     * @return Response
     * @throws JwtTokenException
     */
    public function process(Request $request, callable $handler): Response
    {
        // 通过反射获取控制器哪些方法不需要登录
        $controller = new ReflectionClass($request->controller);
        $noNeedLogin = $controller->getDefaultProperties()['noNeedLogin'] ?? [];

        $request->uid = 0;

        // 访问的方法需要登录
        if (!in_array($request->action, $noNeedLogin)) {
            try {
                $request->uid = JwtToken::getCurrentId();
                if (0 === $request->uid) {
                    return response_json(ErrorCode::JWTTokenUnexpectedValue);
                }
            } catch (JwtTokenException | JwtCacheTokenException $e) {
                return response_json($e->getCode(), config('app.debug', false) ? $e->getMessage() : '');
            }

            locale(JwtToken::getExtendVal('lang') ?? 'zh_CN');
        }

        return $handler($request);
    }
}