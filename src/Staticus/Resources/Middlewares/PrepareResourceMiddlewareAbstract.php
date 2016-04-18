<?php
namespace Staticus\Resources\Middlewares;

use Staticus\Config\Config;
use Staticus\Middlewares\MiddlewareAbstract;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Staticus\Exceptions\WrongRequestException;
use Staticus\Resources\ResourceDOInterface;

abstract class PrepareResourceMiddlewareAbstract extends MiddlewareAbstract
{
    protected $resourceDO;
    /**
     * @var Config
     */
    protected $config;

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
        $name = static::getParamFromRequest('name', $this->request);
        $name = $this->cleanup($name);
        $name = $this->defaultValidator('name', $name, false
            , ResourceDOInterface::NAME_REG_SYMBOLS, $this->config->get('staticus.clean_resource_name'));
        $alt = static::getParamFromRequest('alt', $this->request);
        $alt = $this->cleanup($alt);
        $var = static::getParamFromRequest('var', $this->request);
        $var = $this->cleanup($var);
        $var = $this->defaultValidator('var', $var, true);
        $v = (int)static::getParamFromRequest('v', $this->request);
        $author = static::getParamFromRequest('author', $this->request);
        $author = $this->cleanup($author);

        $dataDir = $this->config->get('staticus.data_dir');
        /**
         * You shouldn't check 'recreate' and 'destroy' params here.
         * @see \Staticus\Action\StaticMiddlewareAbstract::postAction
         * @see \Staticus\Action\StaticMiddlewareAbstract::deleteAction
         */
        $this->resourceDO
            ->reset()
            ->setBaseDirectory($dataDir)
            ->setName($name)
            ->setNameAlternative($alt)
            ->setVariant($var)
            ->setVersion($v)
            ->setAuthor($author);
        if (!$this->resourceDO->getType()) {
            $type = static::getParamFromRequest('type', $this->request);
            $type = $this->cleanup($type);
            $type = $this->defaultValidator('type', $type);
            $this->resourceDO->setType($type);
        }
        $this->fillSpecificResourceSpecific();
        ddc($this->resourceDO);
    }
    abstract protected function fillSpecificResourceSpecific();

    protected function cleanup($name)
    {
        $name = preg_replace('/\s+/u', ' ', trim(mb_strtolower(rawurldecode((string)$name), 'UTF-8')));
        $name = str_replace(['\\', '/'], '', $name);

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
        $attribute = $request->getAttribute($name);
        if ($attribute) {

            return $attribute;
        }
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

    protected function defaultValidator($name, $value, $canBeEmpty = false, $allowedRegexpSymbols = '\w\d\-', $replaceDeniedSymbols = false)
    {
        if (empty($value) && !$canBeEmpty) {
            throw new WrongRequestException('Empty request param "' . $name . '"', __LINE__);
        } else if (!empty($value)) {
            if ($replaceDeniedSymbols) {
                $value = trim(preg_replace('/\s+/u', ' ', preg_replace('~[^' . $allowedRegexpSymbols . ']+~', '_', $value)));
            } else {
                if (!preg_match('/^[' . $allowedRegexpSymbols . ']+$/ui', $value)) {
                    throw new WrongRequestException('Wrong request param "' . $name . '": ' . $value, __LINE__);
                }
            }
        }

        return $value;
    }
}
