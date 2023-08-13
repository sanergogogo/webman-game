<?php
/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

// TODO 部分中间件(比如限流等)放到nginx(openresty)中处理
return [
    '' => [
        app\middleware\AuthorizationMiddleware::class,
    ],

    'api' => [
        Tinywan\LimitTraffic\Middleware\LimitTrafficMiddleware::class,
    ]
];