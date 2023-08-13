<?php

namespace app;

use Jenssegers\Mongodb\Eloquent\Model;

class MongoModel extends Model {

    /**
     * 模型的连接名称.
     *
     * @var string
     */
    protected $connection = 'mongodb';

    /**
     * 模型关联的集合。
     *
     * @var string
     */
    protected $collection;

    /**
     * 主键.
     *
     * @var string
     */
    protected $primaryKey = '_id';

    /**
     * 主键类型.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * 模型的数据日期字段的保存格式。
     *
     * @var string
     */
    protected $dateFormat = 'U';

    /**
     * 指示是否自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = false;
    
}
