<?php

namespace StaticusTest\Actions\Fractal;

use App\Actions\Fractal\ActionGet;
use Staticus\Resources\Jpg\ResourceDO;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\ServerRequest;

class ActionGetTest extends \PHPUnit_Framework_TestCase
{
    public function testResponse()
    {
        $image = new ResourceDO();
        $action = new ActionGet($image);
        $response = $action(new ServerRequest(['/fractal/welcome.jpg']), new Response(), function () {
        });
        $this->assertTrue($response instanceof Response);
        $this->assertTrue($response instanceof EmptyResponse);
        /** @var EmptyResponse $response */
        $this->assertEquals(404, $response->getStatusCode());
    }
}
