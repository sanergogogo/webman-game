<?php
/**
 * Here is your custom functions.
 */

use support\Response;
use app\ErrorCode;
use support\Db;

/**
 * @desc: json 请求响应数据
 *
 * @param int    $code      状态码 0 为成功，其他为失败
 * @param string $msg       错误消息
 * @param array $data      消息体
 * @return Response
 */
function response_json(int $code = ErrorCode::Success, string $msg = '', array $data = null): Response
{
    $result = ['code' => $code, 'msg' => empty($msg) ? trans(strval($code)) : $msg];
    if (!empty($data)) {
        $result['data'] = $data;
    }

    return json($result);
}

/**
 * @desc: 成功 json 请求响应数据
 *
 * @param array $data      消息体
 * @return Response
 */
function response_success(array $data = null): Response
{
    $result = ['code' => ErrorCode::Success, 'msg' => trans(strval(ErrorCode::Success))];
    if (!empty($data)) {
        $result['data'] = $data;
    }

    return json($result);
}

/**
 * mongodb事务需要MongoDB版本^4.0以及部署副本集或分片集群
 */
function db_begin_transaction() {
    $transaction_support = config('app.transaction_support', false);
    if ($transaction_support) {
        Db::beginTransaction();
    }
}

/**
 * mongodb事务需要MongoDB版本^4.0以及部署副本集或分片集群
 */
function db_commit() {
    $transaction_support = config('app.transaction_support', false);
    if ($transaction_support) {
        Db::commit();
    }
}

/**
 * mongodb事务需要MongoDB版本^4.0以及部署副本集或分片集群
 */
function db_rollBack() {
    $transaction_support = config('app.transaction_support', false);
    if ($transaction_support) {
        Db::rollBack();
    }
}
