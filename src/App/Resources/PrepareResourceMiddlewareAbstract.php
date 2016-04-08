<?php
namespace App\Resources;

use Common\Config\Config;
use Common\Middleware\MiddlewareAbstract;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class PrepareResourceMiddlewareAbstract extends MiddlewareAbstract
{
    private $resourceDO;
    /**
     * @var Config
     */
    private $config;

    public function __construct(ResourceDOInterface $resourceDO, Config $config)
    {
        $this->resourceDO = $resourceDO;
        $this->config = $config;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    )
    {
        parent::__invoke($request, $response, $next);
        $this->fillResource();

        return $next($request, $response);
    }

    /**
     * @throws WrongRequestException
     * @todo: Write separate cleanup rules for each parameter
     */
    protected function fillResource()
    {
        $name = $this->request->getAttribute('name');
        $name = $this->cleanup($name);
        if (empty($name) || !preg_match('/\w+/u', $name)) {
            throw new WrongRequestException('Wrong resource name ' . $name);
        }
        $alt = $this->request->getAttribute('alt');
        $alt = $this->cleanup($alt);
        $type = $this->request->getAttribute('type');
        $type = $this->cleanup($type);
        if (empty($type) || !preg_match('/\w+/u', $name)) {
            throw new WrongRequestException('Wrong resource type ' . $name);
        }
        $variant = $this->request->getAttribute('var');
        $variant = $this->cleanup($variant);
        $version = $this->request->getAttribute('v');
        $author = $this->request->getAttribute('author');
        $author = $this->cleanup($author);
        $cacheDir = $this->config->get('data_dir');
        $this->resourceDO
            ->reset()
            ->setBaseDirectory($cacheDir)
            ->setName($name)
            ->setNameAlternative($alt)
            ->setType($type)
            ->setVariant($variant)
            ->setVersion($version)
            ->setAuthor($author);
    }

    protected function cleanup($name)
    {
        $name = preg_replace('/\s+/u', ' ', trim(mb_strtolower(rawurldecode((string)$name), 'UTF-8')));

        return $name;
    }
}
