<?php
namespace Staticus\Middlewares;


use Staticus\Exceptions\ExceptionCodes;
use Staticus\Exceptions\WrongRequestException;
use Zend\Diactoros\Response\JsonResponse;

class ErrorHandler
{
    public function __invoke(\Exception $exception)
    {
        $className = $exception->getTrace();
        if (isset($className[0]['class'])) {
            $className = $className[0]['class'];
        }
        if ($exception instanceof WrongRequestException) {

            /** @see \Zend\Diactoros\Response::$phrases */
            return $this->response(400, $exception->getMessage(), ExceptionCodes::code($className) . '.' . $exception->getCode());
        } else {

            /** @see \Zend\Diactoros\Response::$phrases */
            return $this->response(503, 'Internal error', ExceptionCodes::code($className) . '.' . $exception->getCode());
        }


    }
    protected function response($status, $message, $code)
    {
        $error = [
            'error' => [
                'message' => $message,
                'code' => $code],
        ];

        return new JsonResponse($error, $status);
    }
}