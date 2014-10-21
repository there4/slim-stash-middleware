<?php
namespace There4\Slim\Middleware;

class StashCache extends \Slim\Middleware
{
    protected $handler;

    public function __construct($stash)
    {
        $this->stash = $stash;
    }

    public function call()
    {
        $req   = $this->app->request();
        $resp  = $this->app->response();
        $stash = $this->stash;

        if (!$this->app->request->isGet()) {
            $this->next->call();
        }

        $key = $req->getResourceUri();

        $stashItem = $stash->getItem('routes' . $key);
        $data = $stashItem->get(\Stash\Item::SP_PRECOMPUTE, 300);
        if (!$stashItem->isMiss()) {
            //$this->app->lastModified($data['last_modified']);
            $resp['Content-Type'] = $data['content_type'];
            $resp->body($data['body']);
            return;
        }

        $this->next->call();

        if (!empty($resp->allowCache) && ($resp->status() == 200)) {
            $this->app->expires('+1 hour');
            $stashItem->set(array(
                'content_type'  => $resp['Content-Type'],
                'body'          => $resp->body(),
                'last_modified' => time()
            ), $resp->cacheExpiration);
        }
    }
}
