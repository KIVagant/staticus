<?php
namespace App\Actions\Voice;

use League\Flysystem\FilesystemInterface;
use Staticus\Exceptions\ErrorException;
use Staticus\Middlewares\ActionPostAbstract;
use Staticus\Resources\Mpeg\ResourceDO;
use Staticus\Resources\ResourceDOInterface;
use AudioManager\Manager;

class ActionPost extends ActionPostAbstract
{
    const LANG_RU = 'ru-RU';
    const VOICE_RU = 'Tatyana';
    const LANG_EN = 'en-US';
    const VOICE_EN = 'Salli';

    public function __construct(ResourceDO $resourceDO, FilesystemInterface $filesystem, Manager $manager)
    {
        parent::__construct($resourceDO, $filesystem, $manager);
    }
    /**
     * @param ResourceDOInterface $resourceDO
     * @return mixed
     * @throws ErrorException
     */
    protected function generate(ResourceDOInterface $resourceDO)
    {
        /** @var Manager $generator */
        $generator = $this->generator;
        $alternative = $resourceDO->getNameAlternative();
        $voiceText = $alternative ?: $resourceDO->getName();
        $this->selectLanguage($voiceText);
        $content = $generator->read($voiceText);
        $headers = $generator->getHeaders();
        if (!array_key_exists('http_code', (array)$headers) || $headers['http_code'] != 200) {
            throw new ErrorException(
                'Wrong http response code from voice provider '
                . get_class($this->generator->getAdapter())
                . ': ' . $headers['http_code'] . '; Requested text: '
                . $resourceDO->getName());
        }

        return $content;
    }

    public function isRussian($text)
    {
        $matches = [];
        preg_match('/[а-яё]+/ui', $text, $matches);

        return !empty($matches);
    }

    /**
     * @param $voiceText
     * @todo LoD violation
     */
    protected function selectLanguage($voiceText)
    {
        $adapter = $this->generator->getAdapter();
        /** @var \AudioManager\Adapter\Options\OptionsInterface $options */
        $options = $adapter->getOptions();
        if ($this->isRussian($voiceText)) {
            $options->setLanguage(self::LANG_RU);
            $options->setVoice(self::VOICE_RU);
        } else {
            $options->setLanguage(self::LANG_EN);
            $options->setVoice(self::VOICE_EN);
        }
    }
}