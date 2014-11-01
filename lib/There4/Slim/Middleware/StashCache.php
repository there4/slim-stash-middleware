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

        // Allow a callback, call_user_func allows an array to be passed
        if (isset($resp->signature)) {
            $signature = is_callable($resp->signature)
                ? call_user_func($resp->signature)
                : $resp->signature;
        } else {
            $signature = $req->getResourceUri();
        }
        // Get via the signature if it's not a miss send it to the client
        // and return to halt the response chain
        $stashItem = $stash->getItem('routes' . $signature);
        $data = $stashItem->get(\Stash\Item::SP_PRECOMPUTE, 300);
        if (!$stashItem->isMiss()) {
            $this->app->lastModified($data['last_modified']);
            $resp['Content-Type'] = $data['content_type'];
            $resp->body($data['body']);
            return;
        }
        // Else we continue on with the middleware change and run the next
        // middleware layer
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
