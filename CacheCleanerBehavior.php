<?php
/**
 * @link https://github.com/consik/yii2-cachecleaner
 *
 * @author Sergey Poltaranin <consigliere.kz@gmail.com>
 * @copyright Copyright (c) 2016
 */

namespace consik\yii2cachecleaner;

use consik\yii2cachecleaner\events\CacheUpdateEvent;
use yii\base\Behavior;
use yii\base\Event;
use yii\caching\Cache;

/**
 * Class CacheCleanerBehavior
 * Behavior for deleting cache values on component events
 *
 * simple use case: Deleting cache values after updating AR
 * AR model code:
 * ```
 * public function behaviors()
 * {
 *  return [[
 *      'class' => CacheCleanerBehavior::className(),
 *      //'cacheComponent' => 'cache',
 *      'events' => [
 *          ActiveRecord::EVENT_AFTER_UPDATE => 'cachedModel' . $this->id,
 *          ActiveRecord::EVENT_BEFORE_DELETE => 'cachedModel' . $this->id
 *      ]
 *  ]];
 * }
 * ```
 *
 * @package consik\yii2cachecleaner
 */
class CacheCleanerBehavior extends Behavior
{
    const ALL_CACHE_VALUES = '*';

    /**
     * @var string name of the application cache component
     */
    public $cacheComponent = 'cache';

    /**
     * @var array Associative array where key is event name, value(array|string|null) is a cache key(s) to delete
     *
     * deleting single cache value:
     * [
     *  YOUR_EVENT_NAME => 'cacheValue'
     * ]
     *
     * deleting multiple cache values:
     * [
     *  YOUR_EVENT_NAME => [ 'cacheValue1', 'cacheValue2', ... ]
     * ]
     *
     * deleting ALL cache values:
     * [
     *  YOUR_EVENT_NAME => CacheClearBehaviour::ALL_CACHE_VALUES
     * ]
     *
     * if value is NULL && triggered event instance of CacheUpdateEvent
     * than will be deleted cache with keys from Event's $keys attribute
     */
    public $events = [];
    /**
     * @var Cache
     */
    protected $_cache;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (\Yii::$app->{$this->cacheComponent} instanceof Cache) {
            $this->_cache =  \Yii::$app->{$this->cacheComponent};
        }
    }

    /**
     * @inheritdoc
     */
    public function events()
    {
        return array_fill_keys(
            array_keys($this->events),
            'clearCacheHandler'
        );
    }

    /**
     * Handles events from component for deleting cache values
     *
     * @param Event $event
     */
    public function clearCacheHandler(Event $event)
    {
        $keys = $this->events[$event->name];
        switch ($keys) {
            case self::ALL_CACHE_VALUES:
                $this->flushCache();
                break;

            case null:
                if ($event instanceof CacheUpdateEvent) {
                    $this->deleteCache($event->keys);
                }
                break;

            default:
                $this->deleteCache($keys);
        }
    }

    /**
     * Deletes cache value(s)
     *
     * @param string|array $key
     *
     * @return boolean
     */
    public function deleteCache($key)
    {
        if ($this->_cache) {
            if (is_string($key)) {
                return $this->_cache->delete($key);
            } elseif (is_array($key)) {
                $result = true;
                foreach ($key as $arKey) {
                    $result = ($this->_cache->delete($arKey) && $result);
                }
                return $result;
            }
        }

        return false;
    }

    /**
     * Deletes all values from cache.
     *
     * @return bool
     */
    public function flushCache()
    {
        return $this->_cache && $this->_cache->flush();
    }
}