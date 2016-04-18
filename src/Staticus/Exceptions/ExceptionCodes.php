<?php
namespace Staticus\Exceptions;

class ExceptionCodes
{
    /**
     * Class name Index in this array will be a first digit of the exception code
     * @var array
     */
    protected static
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        $codes = [
        __CLASS__,
        \App\Actions\Voice\ActionPost::class,
        \App\Actions\Voice\VoiceAdapterFactory::class,
        \Staticus\Config\Config::class,
        \Staticus\Diactoros\FileContentResponse\FileUploadedResponse::class,
        \Staticus\Middlewares\ActionGetAbstract::class,
        \Staticus\Resources\Commands\CopyResourceCommand::class,
        \Staticus\Resources\Commands\DeleteSafetyResourceCommand::class,
        \Staticus\Resources\Commands\DestroyResourceCommand::class,
        \Staticus\Resources\Gif\SaveResourceMiddleware::class,
        \Staticus\Resources\Jpg\SaveResourceMiddleware::class,
        \Staticus\Resources\Middlewares\PrepareImageMiddlewareAbstract::class,
        \Staticus\Resources\Middlewares\PrepareResourceMiddlewareAbstract::class,
        \Staticus\Resources\Middlewares\SaveResourceMiddlewareAbstract::class,
        \Staticus\Resources\Png\SaveResourceMiddleware::class,
        \Staticus\Middlewares\ActionPostAbstract::class,
        \Staticus\Diactoros\DownloadedFile::class,
        \SearchManager\Image\SearchImageProviderProxy::class,
        \Staticus\Resources\Middlewares\ImagePostProcessingMiddlewareAbstract::class
    ];

    public static function code($className)
    {
        $codePrefix = array_search($className, static::$codes);

        return (int)$codePrefix;
    }
}