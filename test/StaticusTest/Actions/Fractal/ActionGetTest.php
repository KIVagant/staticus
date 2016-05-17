<?php

namespace StaticusTest\Actions\Fractal;

use App\Actions\Image\ActionGet;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Staticus\Resources\Jpg\ResourceDO;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\ServerRequest;

class ActionGetTest extends \PHPUnit_Framework_TestCase
{
    public function testResponse()
    {
        $image = new ResourceDO();
        $action = new ActionGet($image, $this->getFileSystem());
        $resultResponse = null;
        $action(new ServerRequest(['/fractal/somethingrealnotfoundasfd2hwjq2u3jejr1h2.jpg']), new Response(), function (ServerRequest $request,
            Response $response,
            callable $next = null) use (&$resultResponse) {
            $resultResponse = $response;
        });
        $this->assertTrue($resultResponse instanceof Response);
        $this->assertTrue($resultResponse instanceof EmptyResponse);
        /** @var EmptyResponse $resultResponse */
        $this->assertEquals(404, $resultResponse->getStatusCode());
    }
    /**
     * @return Filesystem
     */
    protected function getFileSystem()
    {
        $adapter = new Local('/'); // can't be replaced to env('DATA_DIR') until all file operations will be refactored
        $filesystem = new Filesystem($adapter);

        return $filesystem;
    }
}
