<?php

namespace app\model;

use app\MongoModel;

/**
 * mail
 * @property object $_id _id
 * @property string $id _id的string形式
 * @property integer $create_time create_time
 * @property string $title title
 * @property string $content content
 * @property integer $state state
 * @property integer $id id
 */
class Mail extends MongoModel
{
    /**
     * 模型关联的集合。
     *
     * @var string
     */
    protected $collection = 'mail';
    

}
