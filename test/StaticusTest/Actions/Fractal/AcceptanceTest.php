<?php

namespace StaticusTest\Actions\Fractal;

use App\Actions\Image\ActionDelete;
use App\Actions\Image\ActionGet;
use App\Actions\Image\ActionPost;
use FractalManager\Adapter\MandlebrotAdapter;
use FractalManager\Manager as FractalManager;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use SearchManager\Adapter\GoogleAdapter;
use SearchManager\Image\GoogleCustomSearchImage;
use SearchManager\Image\SearchImageProviderProxy;
use SearchManager\Manager;
use Staticus\Config\Config;
use Staticus\Diactoros\Response\FileContentResponse;
use Staticus\Diactoros\Response\FileUploadedResponse;
use Staticus\Resources\Image\CropImageDO;
use Staticus\Resources\Jpg\CropMiddleware;
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
    /*
     * WARNING! This test will modify files on disk!
     * All tests must be run one-by-one! Do not change their position in this file!
     */
    const DEFAULT_RESOURCE_UUID = 'a20439c55d292a4a765b7f4a417a8061';
    /**
     * @todo add test for \Jpg\PrepareResourceMiddleware
     */
    const DEFAULT_RESOURCE_NAME = 'Something! Really strange, and random: - here. We\'re happy 43_times';
    const DEFAULT_RESOURCE_ENCODED = 'Something! Really strange, and random: - here. We\u0027re happy 43_times';
    const ROUTE_PREFIX = '/';
    const SIZE_X = 100;
    const SIZE_Y = 100;
    const FILE_PATH_V0 = 'jpg/def/def/0/a20/0/' . self::DEFAULT_RESOURCE_UUID . '.jpg';
    const FILE_PATH_V1 = 'jpg/def/def/1/a20/0/' . self::DEFAULT_RESOURCE_UUID . '.jpg';
    const FILE_PATH_V2 = 'jpg/def/def/2/a20/0/' . self::DEFAULT_RESOURCE_UUID . '.jpg';
    const FILE_PATH_V0_SIZE = 'jpg/def/def/0/a20/' . self::SIZE_X . 'x'. self::SIZE_Y .'/' . self::DEFAULT_RESOURCE_UUID . '.jpg';

    /**
     * @var Filesystem
     */
    protected $filesystem;

    protected function setUp()
    {
        parent::setUp();
        $this->resetFileSystem();
        $this->dataDir = env('DATA_DIR', '/tmp/');
    }


    // Cleanup and first test
    public function testDestroyAction()
    {
        $image = $this->prepareResource();
        $this->makeDeleteRequest($image, ['destroy' => '1']);
        $this->assertFalse($this->filesystem->has($this->dataDir . static::FILE_PATH_V0));
    }

    public function testGetNotFound()
    {
        $this->assertFalse($this->filesystem->has($this->dataDir . static::FILE_PATH_V0));
        $image = $this->prepareResource();
        $response = $this->makeGetRequest($image);
        /** @var EmptyResponse $response */
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testPostCreate()
    {
        $this->assertFalse($this->filesystem->has($this->dataDir . static::FILE_PATH_V0));
        $image = $this->prepareResource();
        $responsePost = $this->makePostRequest($image);
        $this->assertTrue($responsePost instanceof Response);
        $this->assertTrue($responsePost instanceof FileContentResponse);
        /** @var EmptyResponse $responsePost */
        $this->assertEquals(201, $responsePost->getStatusCode());
        $responseSave = $this->subtestSaveResourceMiddleware($responsePost, $image, $this->dataDir . static::FILE_PATH_V0);
        $this->subtestResourceResponseMiddleware($responseSave, $image, 201);
    }

    protected function subtestSaveResourceMiddleware($responsePost, ResourceDO $image, $filePath)
    {
        $this->assertFalse($this->filesystem->has($filePath));
        $config = [
            'staticus' => [
                'magic_defaults' => [
                    'allow' => true,
                    'variant' => true,
                    'version' => true,
                    'size' => true,
                ]
            ]
        ];
        $config = new Config($config);
        $action = new SaveResourceMiddleware($image, $this->resetFileSystem(), $config);
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
        $this->assertTrue($this->filesystem->has($filePath), $image->getFilePath());

        return $responseSave;
    }

    protected function subtestResourceResponseMiddleware($responseSave, ResourceDO $image, $statusCode)
    {
        $action           = new ResourceResponseMiddleware($image);
        $responseResource = null;
        $resourceRoute    = $this->getResourceRoute($image);
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

        $crop    = $image->getCrop();
        $cropStr = $crop
            ? '"crop":' . json_encode($crop->toArray()) . ','
            : '';

        $model = '{"resource":{' . $cropStr . '"height":' . (int) $image->getHeight()
            . ',"name":"' . self::DEFAULT_RESOURCE_ENCODED . '",'
            . '"nameAlternative":"","namespace":"","new":true,'
            . '"recreate":false,"type":"jpg","uuid":"' . self::DEFAULT_RESOURCE_UUID . '",'
            . '"variant":"def","version":0,"width":' . (int) $image->getWidth()
            . ',"dimension":"0"'
            . '},'
            . '"uri":"' . self::DEFAULT_RESOURCE_ENCODED . '.jpg"}';
        $this->assertEquals($model, $responseResource->getBody()->getContents());
    }

    public function testGetFound()
    {
        $this->assertTrue($this->filesystem->has($this->dataDir . static::FILE_PATH_V0));
        $image = $this->prepareResource();
        $response = $this->makeGetRequest($image);
        /** @var EmptyResponse $response */
        $this->assertEquals(200, $response->getStatusCode());
        $model = [realpath($this->dataDir . static::FILE_PATH_V0)];
        $this->assertEquals($model, $response->getHeader('X-Accel-Redirect'));
    }
    public function testPostNotModified()
    {
        $this->assertTrue($this->filesystem->has($this->dataDir . static::FILE_PATH_V0));
        $image = $this->prepareResource();
        $responsePost = $this->makePostRequest($image);
        $this->assertTrue($responsePost instanceof Response);
        $this->assertTrue($responsePost instanceof EmptyResponse);
        /** @var EmptyResponse $responsePost */
        $this->assertEquals(304, $responsePost->getStatusCode());
    }
    public function testPostRecreate()
    {
        $this->assertTrue($this->filesystem->has($this->dataDir . static::FILE_PATH_V0));
        $image = $this->prepareResource();
        $responsePost = $this->makePostRequest($image, ['recreate' => '1']);
        $this->assertTrue($responsePost instanceof Response);
        $this->assertTrue($responsePost instanceof FileContentResponse);
        /** @var EmptyResponse $responsePost */
        $this->assertEquals(201, $responsePost->getStatusCode());
        $this->subtestSaveResourceMiddleware($responsePost, $image, $this->dataDir . static::FILE_PATH_V1);
    }

    public function testPostRecreateWithCrop()
    {
        if (!class_exists(\Imagick::class)) {
            $this->markTestSkipped('Imagick is not installed');
        }
        $imagePath = $this->dataDir . static::FILE_PATH_V0_SIZE;
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
        $this->assertTrue($this->filesystem->has($this->dataDir . static::FILE_PATH_V0));
        $image = $this->prepareResource();
        $this->appendCropToDO($image);
        $this->appendSize($image);

        $responsePost = $this->makeCropRequest($image, ['recreate' => '1']);
        $this->assertTrue($responsePost instanceof Response);
        /** @var EmptyResponse $responsePost */
        $this->assertEquals(200, $responsePost->getStatusCode());
        $this->assertTrue($this->filesystem->has($imagePath), $image->getFilePath());
        list($width, $height) = getimagesize($imagePath);
        $this->assertEquals($width, $image->getCrop()->getWidth());
        $this->assertEquals($height, $image->getCrop()->getHeight());

        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    public function testPostUpload()
    {
        $uploadedFiles = [new UploadedFile($this->dataDir . static::FILE_PATH_V0, 123, UPLOAD_ERR_OK)];
        $this->assertTrue($this->filesystem->has($this->dataDir . static::FILE_PATH_V0));
        $this->assertFalse($this->filesystem->has($this->dataDir . static::FILE_PATH_V2));
        $image = $this->prepareResource();
        $responsePost = $this->makePostRequest($image, ['recreate' => '1'], $uploadedFiles);
        $this->assertTrue($responsePost instanceof Response);
        $this->assertTrue($responsePost instanceof FileUploadedResponse);
        /** @var EmptyResponse $responsePost */
        $this->assertEquals(201, $responsePost->getStatusCode());
        $this->subtestSaveResourceMiddleware($responsePost, $image, $this->dataDir . static::FILE_PATH_V2);
    }

    public function testDeleteAction()
    {
        $image = $this->prepareResource();

        // First, delete the special version 2
        $image->setVersion(2);
        $this->makeDeleteRequest($image);
        $this->assertTrue($this->filesystem->has($this->dataDir . static::FILE_PATH_V0));
        $this->assertTrue($this->filesystem->has($this->dataDir . static::FILE_PATH_V1));
        $this->assertFalse($this->filesystem->has($this->dataDir . static::FILE_PATH_V2));

        // Second, delete the basic version (backup version will created)
        $image->setVersion(0);
        $this->makeDeleteRequest($image);
        $this->assertFalse($this->filesystem->has($this->dataDir . static::FILE_PATH_V0));
        $this->assertTrue($this->filesystem->has($this->dataDir . static::FILE_PATH_V1));
        $this->assertTrue($this->filesystem->has($this->dataDir . static::FILE_PATH_V2));

        // And now destroy all created files (test cleanup)
        $this->makeDeleteRequest($image, ['destroy' => '1']);
        $this->assertFalse($this->filesystem->has($this->dataDir . static::FILE_PATH_V0));
        $this->assertFalse($this->filesystem->has($this->dataDir . static::FILE_PATH_V1));
        $this->assertFalse($this->filesystem->has($this->dataDir . static::FILE_PATH_V2));
    }

    protected function prepareResource()
    {
        $image = new ResourceDO();
        $image->setName(self::DEFAULT_RESOURCE_NAME);
        $image->setNameAlternative('');
        $image->setVariant();
        $image->setVersion();
        $image->setRecreate();
        $image->setBaseDirectory($this->dataDir);
        $image->setAuthor('');
        $image->setWidth();
        $image->setHeight();

        return $image;
    }

    protected function appendCropToDO(ResourceDO $image)
    {
        $crop = new CropImageDO();
        $crop->setX(10);
        $crop->setY(10);
        $crop->setWidth(50);
        $crop->setHeight(50);
        $image->setCrop($crop);

        return $image;
    }

    protected function appendSize(ResourceDO $image)
    {
        $image->setWidth(self::SIZE_X);
        $image->setHeight(self::SIZE_Y);

        return $image;
    }

    protected function invokeAction(ServerRequest $request, MiddlewareInterface $action, ResourceDO $image, Response $response = null)
    {
        $resultResponse = null;
        if (!$response) {
            $response = new Response();
        }
        $action(
            $request,
            $response,
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
        $action = new ActionGet($image, $this->resetFileSystem());
        $response = $this->invokeAction($request, $action, $image);
        $this->assertTrue($response instanceof Response);
        $this->assertTrue($response instanceof EmptyResponse);

        return $response;
    }
    protected function makePostRequest(ResourceDO $image, $parsedBody = [], $uploadedFiles = [])
    {
        $resourceRoute = $this->getResourceRoute($image);
        $request = new ServerRequest([$resourceRoute], $uploadedFiles, null, null, 'php://input', [], [], [], $parsedBody);
        $fractalManager = $this->fractalManagerFactory();
        $searchManager = $this->searchManagerFactory();
        $action = new ActionPost($image, $this->resetFileSystem(), $fractalManager, $searchManager);
        $response = $this->invokeAction($request, $action, $image);

        return $response;
    }

    protected function makeCropRequest(ResourceDO $image, $parsedBody = [], $uploadedFiles = [])
    {
        $resourceRoute = $this->getResourceRoute($image);
        $request = new ServerRequest([$resourceRoute], $uploadedFiles, null, null, 'php://input', [], [], [], $parsedBody);
        $action = new CropMiddleware($image, $this->resetFileSystem());

        $response = new EmptyResponse(200, [
            'Content-Type' => 'image/jpg',
        ]);

        $response = $this->invokeAction($request, $action, $image, $response);

        return $response;
    }

    protected function makeDeleteRequest(ResourceDO $image, $queryParams = [])
    {
        $resourceRoute = $this->getResourceRoute($image);
        $request = new ServerRequest([$resourceRoute], [], null, null, 'php://input', [], [], $queryParams);
        $action = new ActionDelete($image, $this->resetFileSystem());
        $response = $this->invokeAction($request, $action, $image);
        $this->assertTrue($response instanceof Response);
        $this->assertTrue($response instanceof EmptyResponse);
        /** @var EmptyResponse $response */
        $this->assertEquals(204, $response->getStatusCode());
    }

    /**
     * @return Manager
     */
    protected function searchManagerFactory()
    {
        $config = [
            'api' => [
                'google' => [
                    'key' => env('GOOGLE_SEARCH_API_KEY'),
                    'cx' => env('GOOGLE_SEARCH_API_CX'),
                ]
            ]
        ];
        $config = new Config($config);
        $searchAdapter = new GoogleCustomSearchImage($config);
        $searchProvider = new SearchImageProviderProxy($searchAdapter);
        $searchProvider = new GoogleAdapter($searchProvider);
        $searchManager = new Manager($searchProvider);

        return $searchManager;
    }

    /**
     * @return FractalManager
     */
    protected function fractalManagerFactory()
    {
        $fractalAdapter = new MandlebrotAdapter();
        $fractalManager = new FractalManager($fractalAdapter);

        return $fractalManager;
    }

    /**
     * @return Filesystem
     */
    protected function resetFileSystem()
    {
        $adapter = new Local('/'); // can't be replaced to env('DATA_DIR') until all file operations will be refactored
        $this->filesystem = new Filesystem($adapter);

        return $this->filesystem;
    }
}
