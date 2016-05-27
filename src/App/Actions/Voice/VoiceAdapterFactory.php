<?php

namespace App\Actions\Voice;

use AudioManager\Adapter\Google;
use AudioManager\Adapter\Ivona;
use Staticus\Config\ConfigInterface;
use Staticus\Config\Config;
use Staticus\Exceptions\ErrorException;

class VoiceAdapterFactory
{
    const VOICE_PROVIDER_GOOGLE = 'google';
    const VOICE_PROVIDER_IVONA = 'ivona';
    /**
     * @var ConfigInterface|Config
     */
    protected $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @return \AudioManager\Adapter\AdapterInterface
     * @throws RuntimeException
     */
    public function __invoke()
    {
        $adapterName = strtolower($this->config->get('voice.provider', self::VOICE_PROVIDER_GOOGLE));
        switch ($adapterName) {
            case self::VOICE_PROVIDER_GOOGLE:
                $adapter = new Google();
                $adapter->getOptions()->setLanguage('en');
                $adapter->getOptions()->setEncoding('UTF-8');
                break;
            case self::VOICE_PROVIDER_IVONA:
                $secretKey = $this->config->get('voice.' . $adapterName . '.secret_key', '');
                $accessKey = $this->config->get('voice.' . $adapterName . '.access_key', '');
                $adapter = new Ivona();
                $adapter->getOptions()->setSecretKey($secretKey);
                $adapter->getOptions()->setAccessKey($accessKey);
                break;
            default:
                throw new ErrorException('Not implemented functionality for voice provider: ' . $adapterName, __LINE__);
        }

        return $adapter;
    }
}