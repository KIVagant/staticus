<?php
namespace App\Resources;

use App\Action\ActionAbstract;
use App\Diactoros\FileContentResponse\FileContentResponse;
use App\Resources\Exceptions\SaveFileErrorException;
use App\Resources\Exceptions\WrongResponseException;
use Zend\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SaveFileMiddleware extends ActionAbstract
{
    protected static $mimeType = 'application/octet-stream';

    /**
     * @var FileContentResponse
     */
    protected $response; // another type for nice IDE autocomplete in child classes

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable|null $next
     * @return EmptyResponse
     * @throws \Exception
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    )
    {
        parent::__invoke($request, $response, $next);
        if (!$response instanceof FileContentResponse) {
            return $next($request, $response);
        }
        $filePath = $response->getPath();
        if (empty($filePath)) {
            throw new WrongResponseException('Empty file path. File can\'t be saved.');
        }
        $this->setHeaders();
        $resource = $response->getResource();
        if (is_resource($resource)) {
            $this->writeFile($filePath, $resource);
        } else {
            $body = $response->getBody();
            $contents = $body->getContents();
            $this->writeFile($filePath, $contents);
        }

        return new EmptyResponse($response->getStatusCode(), $response->getHeaders());
    }

    protected function writeFile($filePath, $content)
    {
        if (!file_put_contents($filePath, $content)) {
            throw new SaveFileErrorException('File cannot be written to the path ' . $filePath);
        }
    }

    protected function setHeaders()
    {
        $fileHeaders = [
            'Content-Type' => static::$mimeType,
        ];
        $headers = $this->response->getHeaders();
        $headers = array_merge($headers, $fileHeaders);
        $this->response->setHeaders($headers);
    }
}
