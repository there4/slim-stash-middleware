<?php
namespace There4Test\Slim\Middleware;

use There4\Slim\Middleware\StashCache;

class StashCacheTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnsUnchangedSuccessResponse()
    {
        \Slim\Environment::mock(array(
            'SCRIPT_NAME' => '/index.php',
            'PATH_INFO' => '/simpletest'
        ));
        $app = new \Slim\Slim();
        $app->get('/simpletest', function () {
            echo 'Success';
        });

        $item = $this->getMockBuilder('\Stash\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $item->expects($this->any())
             ->method('isMiss')
             ->will($this->returnValue(true));

        $pool = $this->getMockBuilder('\Stash\Pool')
            ->disableOriginalConstructor()
            ->getMock();
        $pool->expects($this->any())
             ->method('getItem')
             ->will($this->returnValue($item));

        $mw = new StashCache($pool);
        $mw->setApplication($app);
        $mw->setNextMiddleware($app);
        $mw->call();

        $this->assertEquals(200, $app->response()->status());
        $this->assertEquals('Success', $app->response()->body());
    }
}

/* End of file StashCacheTest.php */
