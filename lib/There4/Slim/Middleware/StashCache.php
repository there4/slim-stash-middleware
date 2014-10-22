<?php
namespace There4\Slim\Middleware;

class StashCache extends \Slim\Middleware
{
    public function __construct($stash)
    {
        $this->stash = $stash;
    }

    public function call()
    {
        $req   = $this->app->request();
        $resp  = $this->app->response();
        $stash = $this->stash;

        // Only cache GET requests
        if (!$this->app->request->isGet()) {
            $this->next->call();
            return;
        }

        // TODO: Allow this signature generator to be a callback
        $key = $req->getResourceUri();

        // Get via the key if it's not a miss send it to the client
        $stashItem = $stash->getItem('routes' . $key);
        $data = $stashItem->get(\Stash\Item::SP_PRECOMPUTE, 300);
        if (!$stashItem->isMiss()) {
            $this->app->lastModified($data['last_modified']);
            $resp['Content-Type'] = $data['content_type'];
            $resp->body($data['body']);
            return;
        }

        // Run the next middleware layer
        $this->next->call();

        // If we allow cache and the endpoint ran correctly, cache the result
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

/* End of file StashCache.php */
