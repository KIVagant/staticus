<?php
namespace Staticus\Resources\Middlewares;

use Staticus\Config\Config;
use Staticus\Middlewares\MiddlewareAbstract;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Staticus\Diactoros\Exceptions\WrongRequestException;
use Staticus\Resources\ResourceDOInterface;
use Zend\Diactoros\Response\EmptyResponse;

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
        try {
            $this->fillResource();
        } catch (WrongRequestException $e) {

            /** @see \Zend\Diactoros\Response::$phrases */
            return new EmptyResponse(400);
        }

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
        $alt = static::getParamFromRequest('alt', $this->request);
        $alt = $this->cleanup($alt);
        $var = static::getParamFromRequest('var', $this->request);
        $var = $this->cleanup($var);
        $v = static::getParamFromRequest('v', $this->request);
        $author = static::getParamFromRequest('author', $this->request);
        $author = $this->cleanup($author);

        $cacheDir = $this->config->get('data_dir');
        /**
         * You shouldn't check 'recreate' and 'destroy' params here.
         * @see \Staticus\Action\StaticMiddlewareAbstract::postAction
         * @see \Staticus\Action\StaticMiddlewareAbstract::deleteAction
         */
        $this->resourceDO
            ->reset()
            ->setBaseDirectory($cacheDir)
            ->setName($name)
            ->setNameAlternative($alt)
            ->setVariant($var)
            ->setVersion($v)
            ->setAuthor($author);

        if (!$this->resourceDO->getType()) {
            $type = $this->request->getAttribute('type');
            $type = $this->cleanup($type);
            if (empty($type) || !preg_match('/\w+/u', $name)) {
                throw new WrongRequestException('Unknown resource type for ' . $name);
            }
            $this->resourceDO->setType($type);
        }
    }

    protected function cleanup($name)
    {
        $name = preg_replace('/\s+/u', ' ', trim(mb_strtolower(rawurldecode((string)$name), 'UTF-8')));

        return $name;
    }

    /**
     * @param $name
     * @param ServerRequestInterface $request
     * @return string
     * @todo move this method somethere
     */
    public static function getParamFromRequest($name, ServerRequestInterface $request)
    {
        $paramsGET = $request->getQueryParams();
        $paramsPOST = (array)$request->getParsedBody();

        $str = isset($paramsPOST[$name])
            ? $paramsPOST[$name]
            : (
                isset($paramsGET[$name])
                ? $paramsGET[$name]
                : ''
            );

        return $str;
    }
}
