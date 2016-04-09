<?php

namespace Staticus\Action\Voice;

use AudioManager\Adapter\Google;
use AudioManager\Adapter\Ivona;
use App\Exceptions\ErrorException;

class VoiceAdapterFactory
{
    const VOICE_PROVIDER_GOOGLE = 'google';
    const VOICE_PROVIDER_IVONA = 'ivona';

    /**
     * @return \AudioManager\Adapter\AdapterInterface
     * @throws RuntimeException
     */
    public function __invoke()
    {
        $adapterName = strtolower(env('VOICE_DEFAULT_PROVIDER', self::VOICE_PROVIDER_GOOGLE));
        switch ($adapterName) {
            case self::VOICE_PROVIDER_GOOGLE:
                $options = new Google\Options();
                $options->setLanguage('en');
                $options->setEncoding('UTF-8');
                $adapter = new Google($options);
                break;
            case self::VOICE_PROVIDER_IVONA:
                $secretKey = env('VOICE_IVONA_SECRET_KEY');
                $accessKey = env('VOICE_IVONA_ACCESS_KEY');
                $authenticate = new Ivona\Authenticate($secretKey, $accessKey);
                $options = new Ivona\Options($authenticate);
                $adapter = new Ivona($options);
                $adapter->setOptions($options);
                break;
            default:
                throw new ErrorException('Not implemented functionality for voice provider: ' . $adapterName);
        }

        return $adapter;
    }
}