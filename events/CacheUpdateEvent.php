<?php
/**
 * @link https://github.com/consik/yii2-cachecleaner
 *
 * @author Sergey Poltaranin <consigliere.kz@gmail.com>
 * @copyright Copyright (c) 2016
 */

namespace consik\yii2cachecleaner\events;


use yii\base\Event;

/**
 * Class CacheUpdateEvent
 * @package consik\yii2cachecleaner\events
 */
class CacheUpdateEvent extends Event
{
    const ACTION_ADD = 'add';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';

    /**
     * @var string Action with key values
     */
    public $action = self::ACTION_UPDATE;
    /**
     * @var array Keys of cache values
     */
    public $keys = [];

}