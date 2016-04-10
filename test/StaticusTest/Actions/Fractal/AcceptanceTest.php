<?php

namespace StaticusTest\Actions\Fractal;

use App\Actions\Image\ActionDelete;
use App\Actions\Image\ActionGet;
use App\Actions\Image\ActionPost;
use FractalManager\Adapter\Mandlebrot;
use FractalManager\Manager;
use Staticus\Diactoros\FileContentResponse\FileContentResponse;
use Staticus\Diactoros\FileContentResponse\FileUploadedResponse;
use Staticus\Resources\Jpg\ResourceDO;
use Staticus\Resources\Jpg\ResourceResponseMiddleware;
use Staticus\Resources\Jpg\SaveResourceMiddleware;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\UploadedFile;
use Zend\Stratigility\MiddlewareInterface;

class AcceptanceTest extends \PHPUnit_Framework_TestCase
{
    // TODO: test for variants
    /*
     * WARNING! This test will modify files on disk!
     * All tests must be run one-by-one! Do not change their position in this file!
     */
    const DEFAULT_RESOURCE_NAME = 'somethingreallystrangeandrandomhere43jejlhkla';
    const ROUTE_PREFIX = '/';
    const FILE_PATH_V0 = 'jpg/def/0/0/70c6bb24a7468ef0bdd98f0a773626a1.jpg';
    const FILE_PATH_V1 = 'jpg/def/1/0/70c6bb24a7468ef0bdd98f0a773626a1.jpg';
    const FILE_PATH_V2 = 'jpg/def/2/0/70c6bb24a7468ef0bdd98f0a773626a1.jpg';
    // Cleanup and first test
    public function testDestroyAction()
    {
        $image = $this->prepareResource();
        $this->makeDeleteRequest($image, ['destroy' => '1']);
        $this->assertFileNotExists(env('DATA_DIR') . static::FILE_PATH_V0);
    }

    public function testGetNotFound()
    {
        $this->assertFileNotExists(env('DATA_DIR') . static::FILE_PATH_V0);
        $image = $this->prepareResource();
        $response = $this->makeGetRequest($image);
        /** @var EmptyResponse $response */
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testPostCreate()
    {
        $this->assertFileNotExists(env('DATA_DIR') . static::FILE_PATH_V0);
        $image = $this->prepareResource();
        $responsePost = $this->makePostRequest($image);
        $this->assertTrue($responsePost instanceof Response);
        $this->assertTrue($responsePost instanceof FileContentResponse);
        /** @var EmptyResponse $responsePost */
        $this->assertEquals(201, $responsePost->getStatusCode());
        $responseSave = $this->subtestSaveResourceMiddleware($responsePost, $image, env('DATA_DIR') . static::FILE_PATH_V0);
        $responseResource = $this->subtestResourceResponseMiddleware($responseSave, $image, 201);
    }

    protected function subtestSaveResourceMiddleware($responsePost, ResourceDO $image, $filePath)
    {
        $this->assertFileNotExists($filePath);
        $action = new SaveResourceMiddleware($image);
        $responseSave = null;
        $resourceRoute = $this->getResourceRoute($image);
        $action(new ServerRequest([$resourceRoute]), $responsePost, function (
            ServerRequest $request,
            Response $response,
            callable $next = null
        ) use (&$responseSave) {
            $responseSave = $response;
        });
        $this->assertTrue($responseSave instanceof Response);
        $this->assertTrue($responseSave instanceof EmptyResponse);
        $this->assertFileExists($filePath);

        return $responseSave;
    }

    protected function subtestResourceResponseMiddleware($responseSave, ResourceDO $image, $statusCode)
    {
        $action = new ResourceResponseMiddleware($image);
        $responseResource = null;
        $resourceRoute = $this->getResourceRoute($image);
        $action(new ServerRequest([$resourceRoute]), $responseSave, function (
            ServerRequest $request,
            Response $response,
            callable $next = null
        ) use (&$responseResource) {
            $responseResource = $response;
        });
        $this->assertTrue($responseResource instanceof Response);
        $this->assertTrue($responseResource instanceof JsonResponse);
        /** @var JsonResponse $responseResource */
        $this->assertEquals($statusCode, $responseResource->getStatusCode());
        $model = '{"resource":{"height":0,"name":"somethingreallystrangeandrandomhere43jejlhkla",'
            . '"nameAlternative":"","recreate":false,"type":"jpg","uuid":"70c6bb24a7468ef0bdd98f0a773626a1",'
            . '"variant":"def","version":0,"width":0},'
            . '"uri":"somethingreallystrangeandrandomhere43jejlhkla.jpg"}';
        $this->assertEquals($model, $responseResource->getBody()->getContents());

        return $responseResource;
    }

    public function testGetFound()
    {
        $this->assertFileExists(env('DATA_DIR') . static::FILE_PATH_V0);
        $image = $this->prepareResource();
        $response = $this->makeGetRequest($image);
        /** @var EmptyResponse $response */
        $this->assertEquals(200, $response->getStatusCode());
        $model = [realpath(env('DATA_DIR') . static::FILE_PATH_V0)];
        $this->assertEquals($model, $response->getHeader('X-Accel-Redirect'));
    }
    public function testPostNotModified()
    {
        $this->assertFileExists(env('DATA_DIR') . static::FILE_PATH_V0);
        $image = $this->prepareResource();
        $responsePost = $this->makePostRequest($image);
        $this->assertTrue($responsePost instanceof Response);
        $this->assertTrue($responsePost instanceof EmptyResponse);
        /** @var EmptyResponse $responsePost */
        $this->assertEquals(304, $responsePost->getStatusCode());
    }
    public function testPostRecreate()
    {
        $this->assertFileExists(env('DATA_DIR') . static::FILE_PATH_V0);
        $image = $this->prepareResource();
        $responsePost = $this->makePostRequest($image, ['recreate' => '1']);
        $this->assertTrue($responsePost instanceof Response);
        $this->assertTrue($responsePost instanceof FileContentResponse);
        /** @var EmptyResponse $responsePost */
        $this->assertEquals(201, $responsePost->getStatusCode());
        $this->subtestSaveResourceMiddleware($responsePost, $image, env('DATA_DIR') . static::FILE_PATH_V1);
    }
    public function testPostUpload()
    {
        $uploadedFiles = [new UploadedFile(env('DATA_DIR') . static::FILE_PATH_V0, 123, UPLOAD_ERR_OK)];
        $this->assertFileExists(env('DATA_DIR') . static::FILE_PATH_V0);
        $this->assertFileNotExists(env('DATA_DIR') . static::FILE_PATH_V2);
        $image = $this->prepareResource();
        $responsePost = $this->makePostRequest($image, ['recreate' => '1'], $uploadedFiles);
        $this->assertTrue($responsePost instanceof Response);
        $this->assertTrue($responsePost instanceof FileUploadedResponse);
        /** @var EmptyResponse $responsePost */
        $this->assertEquals(201, $responsePost->getStatusCode());
        $this->subtestSaveResourceMiddleware($responsePost, $image, env('DATA_DIR') . static::FILE_PATH_V2);
    }
    public function testDeleteAction()
    {
        $image = $this->prepareResource();

        // First, delete the special version 2
        $image->setVersion(2);
        $this->makeDeleteRequest($image);
        $this->assertFileExists(env('DATA_DIR') . static::FILE_PATH_V0);
        $this->assertFileExists(env('DATA_DIR') . static::FILE_PATH_V1);
        $this->assertFileNotExists(env('DATA_DIR') . static::FILE_PATH_V2);

        // Second, delete the basic version (backup version will created)
        $image->setVersion(0);
        $this->makeDeleteRequest($image);
        $this->assertFileNotExists(env('DATA_DIR') . static::FILE_PATH_V0);
        $this->assertFileExists(env('DATA_DIR') . static::FILE_PATH_V1);
        $this->assertFileExists(env('DATA_DIR') . static::FILE_PATH_V2);

        // And now destroy all created files (test cleanup)
        $this->makeDeleteRequest($image, ['destroy' => '1']);
        $this->assertFileNotExists(env('DATA_DIR') . static::FILE_PATH_V0);
        $this->assertFileNotExists(env('DATA_DIR') . static::FILE_PATH_V1);
        $this->assertFileNotExists(env('DATA_DIR') . static::FILE_PATH_V2);
    }

    protected function prepareResource()
    {
        $image = new ResourceDO();
        $image->setName(self::DEFAULT_RESOURCE_NAME);
        $image->setNameAlternative('');
        $image->setVariant();
        $image->setVersion();
        $image->setRecreate();
        $image->setBaseDirectory(env('DATA_DIR'));
        $image->setAuthor('');
        $image->setWidth();
        $image->setHeight();

        return $image;
    }

    protected function invokeAction(ServerRequest $request, MiddlewareInterface $action, ResourceDO $image)
    {
        $resultResponse = null;
        $action(
            $request,
            new Response(),
            function (ServerRequest $request,
                Response $response,
                callable $next = null) use (&$resultResponse) {
                $resultResponse = $response;
            }
        );

        return $resultResponse;
    }

    protected function getResourceRoute($image)
    {
        return self::ROUTE_PREFIX . $image->getName() . '.' . $image->getType();
    }

    protected function makeGetRequest($image)
    {
        $resourceRoute = $this->getResourceRoute($image);
        $request = new ServerRequest([$resourceRoute]);

        $action = new ActionGet($image);
        $response = $this->invokeAction($request, $action, $image);
        $this->assertTrue($response instanceof Response);
        $this->assertTrue($response instanceof EmptyResponse);

        return $response;
    }
    protected function makePostRequest(ResourceDO $image, $parsedBody = [], $uploadedFiles = [])
    {
        $resourceRoute = $this->getResourceRoute($image);
        $request = new ServerRequest([$resourceRoute], $uploadedFiles, null, null, 'php://input', [], [], [], $parsedBody);
        $adapter = new Mandlebrot();
        $manager = new Manager($adapter);
        $action = new ActionPost($image, $manager);
        $response = $this->invokeAction($request, $action, $image);

        return $response;
    }

    protected function makeDeleteRequest(ResourceDO $image, $queryParams = [])
    {
        $resourceRoute = $this->getResourceRoute($image);
        $request = new ServerRequest([$resourceRoute], [], null, null, 'php://input', [], [], $queryParams);
        $action = new ActionDelete($image);
        $response = $this->invokeAction($request, $action, $image);
        $this->assertTrue($response instanceof Response);
        $this->assertTrue($response instanceof EmptyResponse);
        /** @var EmptyResponse $response */
        $this->assertEquals(204, $response->getStatusCode());
    }
}
