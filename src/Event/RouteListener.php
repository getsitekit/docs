<?php

namespace Sitekit\Docs\Event;

use Sitekit\Docs\UrlResolver;
use Pagekit\Application as App;
use Pagekit\Event\EventSubscriberInterface;

class RouteListener implements EventSubscriberInterface
{
    /**
     * Adds cache breaker to router.
     */
    public function onAppRequest()
    {
        App::router()->setOption('docs.permalink', UrlResolver::getPermalink());
    }

    /**
     * Registers permalink route alias.
     *
     * @param $event
     * @param $route
     */
    public function onConfigureRoute($event, $route)
    {
        if ($route->getName() == '@docs/id' && UrlResolver::getPermalink()) {
            App::routes()->alias(dirname($route->getPath()).'/'.ltrim(UrlResolver::getPermalink(), '/'), '@docs/id', ['_resolver' => 'Sitekit\Docs\UrlResolver']);
        }
    }

    /**
     * Clears resolver cache.
     */
    public function clearCache()
    {
        App::cache()->delete(UrlResolver::CACHE_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function subscribe()
    {
        return [
            'request' => ['onAppRequest', 130],
            'route.configure' => 'onConfigureRoute',
            // 模型事件名取类短名：Docs → model.docs.saved / model.docs.deleted
            'model.docs.saved' => 'clearCache',
            'model.docs.deleted' => 'clearCache'
        ];
    }
}
