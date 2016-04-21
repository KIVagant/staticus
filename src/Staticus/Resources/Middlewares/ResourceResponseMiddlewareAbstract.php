<?php
namespace Staticus\Resources\Middlewares;

use Staticus\Diactoros\FileContentResponse\FileContentResponse;
use Staticus\Diactoros\FileContentResponse\FileUploadedResponse;
use Staticus\Diactoros\FileContentResponse\ResourceDoResponse;
use Staticus\Middlewares\MiddlewareAbstract;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Staticus\Resources\ResourceDOInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;

abstract class ResourceResponseMiddlewareAbstract extends MiddlewareAbstract
{
    /**
     * @var ResourceDOInterface
     */
    protected $resourceDO;
    
    public function __construct(ResourceDOInterface $resourceDO)
    {
        $this->resourceDO = $resourceDO;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    )
    {
        parent::__invoke($request, $response, $next);
        if ($this->isSupportedResponse($response)) {
            $data = [
                'resource' => $this->resourceDO->toArray(),
                'uri' => $this->getUri($this->resourceDO),
            ];
            if (!empty($data)) {
                $headers = $response->getHeaders();
                $headers['Content-Type'] = 'application/json';
                $response = $this->getResponseObject($data, $response->getStatusCode(), $headers);
            }
        }

        return $next($request, $response);
    }

    /**
     * @return ResponseInterface
     */
    protected function getResponseObject($data, $status, array $headers)
    {
        $return = new JsonResponse($data, $status, $headers);

        return $return;
    }

    protected function getUri(ResourceDOInterface $resourceDO)
    {
        $uri = $resourceDO->getName() . '.' . $resourceDO->getType();
        $query = [];
        if (ResourceDOInterface::DEFAULT_VARIANT !== $resourceDO->getVariant()) {
            $query['var'] = $resourceDO->getVariant();
        }
        if (ResourceDOInterface::DEFAULT_VERSION !== $resourceDO->getVersion()) {
            $query['v'] = $resourceDO->getVersion();
        }
        $query = http_build_query($query);
        if ($query) {
            $uri .= '?' . $query;
        }

        return $uri;
    }

    /**
     * @param ResponseInterface $response
     * @return bool
     */
    protected function isSupportedResponse(ResponseInterface $response)
    {
        return $response instanceof EmptyResponse
        || $response instanceof FileContentResponse
        || $response instanceof FileUploadedResponse
        || $response instanceof ResourceDoResponse;
    }
}
