<?php

declare(strict_types=1);

namespace app\game\controller;

use support\Request;
use app\ErrorCode;

// 麻将胡了
class MjhlController {

    /**
     * 不需要登录的方法
     */
    protected $noNeedLogin = ['test'];

    public function test() {

        return response_success();
    }

}
