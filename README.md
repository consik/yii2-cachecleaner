# Yii2 CacheCleaner Extension

[![Latest Stable Version](https://poser.pugx.org/consik/yii2-cachecleaner/v/stable)](https://packagist.org/packages/consik/yii2-cachecleaner)
[![Total Downloads](https://poser.pugx.org/consik/yii2-cachecleaner/downloads)](https://packagist.org/packages/consik/yii2-cachecleaner)
[![License](https://poser.pugx.org/consik/yii2-cachecleaner/license)](https://packagist.org/packages/consik/yii2-cachecleaner)

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require consik/yii2-cachecleaner
```

or add

```json
"consik/yii2-cachecleaner": "^1.0"
```

## CacheCleanerBehavior class description

Deletes cache after defined events.

### Properties

1. ``` array $events = []``` - Associative array where key is event name, value(array|string|null) is a cache key(s) to delete
2. ``` string $cacheComponent = 'cache'``` - Name of the application cache component

### Public methods

1. ``` boolean deleteCache(string|array $key)``` - Deletes cache value(s)
2. ``` boolean flushCache()``` - Deletes all cache values

## ARUpdateCCBehavior class description. [#docs](/ARUpdateCCBehavior.php#L18)

Deletes cache values after updating ActiveRecord attributes

### Properties

1. ``` array $attributes = []``` - Associative array, where array keys is attributes name, array value is cache value(s) keys
2. ``` string $cacheComponent = 'cache'``` - Name of the application cache component

See [DocBlock](/ARUpdateCCBehavior.php#L18) for usage examples.

## Examples

### Autodelete cache after AR update

Simple use case:
We have cached AR object somewhere in our app:
```php
<?php
...
if (!$model = Yii::$app->cache->get('cachedModel')) {
	$model = ARModel::findOne($modelID);
	Yii::$app->cache->set('cachedModel', $model);
}
...
```

So, if somewhere in applicaton we change or delete this AR data, we have to delete our cache value.

Just use CacheCleanerBehavior in your ARModel:

```php
<?php
public function behaviors()
{
    return [[
        'class' => CacheCleanerBehavior::className(),
        //'cacheComponent' => 'cache',  //you can define your app cache component
        'events' => [
            ActiveRecord::EVENT_AFTER_UPDATE => 'cachedModel'
            ActiveRecord::EVENT_BEFORE_DELETE => 'cachedModel'
        ]
    ]];
}
```

NOTE! When component initialize behaviors there is no attributes!
To set cache keys with object attributes use callable param for defining. [#DocBlock](/CacheCleanerBehavior.php#L65)

### Using [CacheUpdateEvent](/events/CacheUpdateEvent.php)

There is a special class in package for triggering events when you change cache values, where you can send to handler action changed cache key(s).

You can use this class with CacheCleanerBehavior if you want delete different keys using one event name.

Example:

```php
<?php
public function behaviors()
{
    return [[
        'class' => CacheCleanerBehavior::className(),
        'events' => [
            YOUR_EVENT_NAME => null
        ]
    ]];
}

...

function someComponentAction()
{
...
	$this->trigger(YOUR_EVENT_NAME, new CacheUpdateEvent([
		'keys' => ['keyName1', 'keyName2'],
	]));
...
}
```

### Other options for defining $events property

See the doc block for [CacheCleanerBehavior::$events](/CacheCleanerBehavior.php#L47)
