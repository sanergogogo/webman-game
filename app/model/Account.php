<?php

namespace app\model;

use app\MongoModel;
use RuntimeException;
use support\Db;
use support\Model;

/**
 * 提现绑定
 * @property string $type 提现绑定类型 bankcard alipay usdt topay okpay等
 * @property string $typename 绑定类型名 比如xx银行 支付宝等
 * @property string $name 玩家真实姓名
 * @property string $account 银行卡类型为银行卡号 支付宝为支付宝账号 usdt为usdt地址等
 * @property string $addr 银行卡为开户行地址 usdt为网络类型(TRC20 ERC20)
 */
interface AccountWithdrawBinding {
}

/**
 * account 账号集合
 * @property string $id
 * @property string $account 账号
 * @property int $accid 账号id
 * @property string $password 密码
 * @property string $nickname 昵称
 * @property int $sex 性别
 * @property int $headid 头像id
 * @property string $head_url 头像url
 * @property string $phone 手机号码
 * @property string $email email
 * @property int $vip vip等级
 * @property int $state 状态 0禁用 1正常
 * @property int $register_time 注册时间
 * @property string $register_ip 注册ip
 * @property string $register_device 注册设备
 * @property string $register_uuid 注册机器码
 * @property int $login_time 登陆时间
 * @property string $login_ip 登陆ip
 * @property string $channel 渠道
 * @property int $type 帐号类型 0正常帐号 1机器人 2游客 3直播号
 * @property int $permission 账号权限
 * @property string $remark 备注
 * @property int $score 分数
 * @property int $bank_score 银行分数
 * @property int $recharge_amount 充值金额
 * @property int $recharge_times 充值次数
 * @property int $withdraw_amount 提现金额
 * @property int $withdraw_times 提现金额
 * @property int $balance_score 总输赢分数
 * @property int $today_balance_score 今日输赢分数
 * @property string $withdraw_password 提现密码
 * @property AccountWithdrawBinding $withdraw_binding 提现绑定信息
 * @property string $realname 真实姓名
 * @property array $team 团队信息
 */
class Account extends MongoModel
{
    /**
     * 模型关联的集合。
     *
     * @var string
     */
    protected $collection = 'account';

    /**
     * 不可被批量赋值的字段.
     *
     * @var array<string>|bool
     */
    protected $guarded = [];

    /**
     * 更新关联的团队信息
     */
    public static function updateAssociatedTeamInfo(int $accid, array $bind_team) {
        // 所有上级的团队成员数量+1 今日新增会员数量+1
        if (!empty($bind_team['parents'])) {
            Account::raw(function ($collection) use($bind_team) {
                return $collection->updateMany(
                    array('accid' => array('$in' => $bind_team['parents'])),
                    array('$inc' => array('team.today_team_details.new_members_count' => 1, 'team.members_count' => 1)),
                    array('upsert' => false)
                );
            });
        }

        // 直接上级团队成员数量+1
        if ($bind_team['parent'] > 0) {
            Account::raw(function ($collection) use($bind_team, $accid) {
                return $collection->findOneAndUpdate(
                    array('accid' => $bind_team['parent']),
                    array(
                        '$inc' => array('team.direct_members_count' => 1, 'team.today_team_details.new_direct_members_count'=> 1),
                        '$push' => array('team.direct_members' => $accid)
                    ),
                    array('new' => false, 'upsert' => false)
                );
            });
        }
    }

    /**
     * 绑定团队
     */
    public static function bindTeam(int $accid, Account $bind_account) : array {
        $parents = $bind_account->team['parents'];
        array_push($parents, $bind_account->accid);

        $bind_team = array(
            'parent' => $bind_account->accid,
            'parents' => $parents,
            'members_count' => 0,
            'direct_members' => array(),
            'direct_members_count' => 0,
            'rebate_rate' => 0,
            'total_rebate' => 0,
            'can_withdraw_rebate' => 0,
            'today_team_details' => array(
                'new_members_count' => 0,
                'new_direct_members_count' => 0,
            ),
        );

        self::updateAssociatedTeamInfo($accid, $bind_team);

        return $bind_team;
    }

    /**
     * 创建账号
     */
    public static function createAccount(string $account, string $password, string $nickname, string $ip,
    string $device, string $uuid, string $realname, Account $bind_account) : Account {
        db_begin_transaction();
        try {
            $value = Db::collection('ids')->raw(function ($collection) {
                return $collection->findOneAndUpdate(
                    array('name' => 'account'),
                    array('$inc' => array('id' => 1)),
                    array('new' => true, 'upsert' => true)
                );
            });
            $accid_ret = Db::collection('accids')->find($value->id);
            if (!$accid_ret) {
                throw new RuntimeException('分配accid失败');
            }
    
            $account_data = [
                'account' => $account,
                'password' => $password,
                'nickname' => $nickname,
                'accid' => $accid_ret['accid'],
                'sex' => 0,
                'headid' => 0,
                'head_url' => '',
                'phone' => '',
                'email' => '',
                'vip' => 0,
                'state' => 1,
                'register_time' => time(),
                'register_ip' => $ip,
                'register_device' => $device,
                'register_uuid' => $uuid,
                'login_time' => time(),
                'login_ip' => $ip,
                'channel' => '',
                'type' => 0,
                'permission' => 0,
                'remark' => '',
                'score' => 0,
                'bank_score' => 0,
                'recharge_amount' => 0,
                'recharge_times' => 0,
                'withdraw_amount' => 0,
                'withdraw_times' => 0,
                'balance_score' => 0,
                'today_balance_score' => 0,
                'withdraw_password' => $password,
                'withdraw_binding' => (object)array(),
                'withdraw_password' => $realname,
            ];
    
            if ($bind_account) {
                $account_data['team'] = self::bindTeam($accid_ret['accid'], $bind_account);
            }
    
            $account_ret = Account::create($account_data);

            db_commit();

            return $account_ret;
        } catch (\Throwable $th) {
            db_rollBack();

            throw $th;
        }
        
    }
    
}
