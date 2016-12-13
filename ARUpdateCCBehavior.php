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
use yii\behaviors\AttributeBehavior;
use yii\caching\Cache;
use yii\db\ActiveRecord;

/**
 * Class ARUpdateCCBehavior
 * Behavior for deleting cache values when AR attributes has changed
 *
 * NOTE: parent attribute $events not used!
 *
 * Example:
 * ```
 * public function behaviors()
 * {
 *  return [[
 *      'class' => ARUpdateCCBehavior::className(),
 *      //'cacheComponent' => 'cache',
 *      'attributes' => [
 *          'comment_text' => 'cachedValue'
 *      ]
 *  ]];
 * }
 * ```
 *
 * @package consik\yii2cachecleaner
 */
class ARUpdateCCBehavior extends CacheCleanerBehavior
{
    /**
     * @var array old attributes values from owner
     */
    private $oldAttributes = [];

    /**
     * Associative array, where array keys is attributes name, array value is cache value(s) keys
     *
     * For example deleting cache value 'comment{id}', when attribute "comment_text" is changed
     * [
     *  'comment_text' => 'comment'
     * ]
     * ... or multiple values ...
     * [
     *  'your_attribute' => ['cacheValue1', 'cacheValue2', ...]
     * ]
     *
     * NOTE! In behaviors() method model doesn't have attributes and you can't set 'cache'.$this->id as cache value
     * You may use callable value for $attributes
     * example:
     * In AR class:
     * #definition of behavior's attributes
     * 'attributes' => [
     *  'name' => 'cacheValueName'
     * ]
     * #callable function  that returns string|array cache value(s) key
     * public function cacheValueName()
     * {
     * return 'cachedValue' . $this->id;
     * }
     *
     * Inline definition:
     * 'attributes' => [
     *      'name' => function ($model) { return 'cachedValue' . $model->id; }
     * ]
     * @var array
     */
    public $attributes = [];

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'deleteAttributesCache',
            ActiveRecord::EVENT_AFTER_UPDATE => 'deleteAttributesCache',
            ActiveRecord::EVENT_AFTER_DELETE => 'deleteAttributesCache',
            ActiveRecord::EVENT_AFTER_FIND => 'saveOldAttributes',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'saveOldAttributes',
            ActiveRecord::EVENT_AFTER_REFRESH => 'saveOldAttributes',
        ];
    }

    /**
     * @param Event $event
     * @return void
     */
    public function deleteAttributesCache(Event $event)
    {
        $deleting = $event->name == ActiveRecord::EVENT_AFTER_DELETE;
        foreach ($this->attributes as $attribute => $cache) {
            if (
                $deleting
                || empty($this->oldAttributes[$attribute])
                || $this->oldAttributes[$attribute] != $this->owner->attributes[$attribute]
            ) {
                if (is_callable($cache)) {
                    $cache = $cache($this->owner);
                } else if (is_callable([$this->owner, $cache])) {
                    $cache = call_user_func([$this->owner, $cache], $this->owner);
                }

                $this->deleteCache($cache);
            }
        }
        $this->saveOldAttributes();
    }

    /**
     * Saves old attributes in private variable
     * @param Event $event
     * @return void
     */
    public function saveOldAttributes(Event $event = null)
    {
        $this->oldAttributes = $this->owner->oldAttributes;
    }
}