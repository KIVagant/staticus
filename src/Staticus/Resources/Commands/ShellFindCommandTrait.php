<?php

namespace Staticus\Resources\Commands;

use Staticus\Resources\ResourceDOInterface;

trait ShellFindCommandTrait
{
    /**
     * @param $baseDir
     * @param $uuid
     * @param $type
     * @param $variant
     * @param $version
     * @return string
     */
    protected function getShellFindCommand($baseDir, $uuid, $type, $variant = ResourceDOInterface::DEFAULT_VARIANT, $version = ResourceDOInterface::DEFAULT_VERSION)
    {
        $command = 'find ';
        if ($version !== ResourceDOInterface::DEFAULT_VERSION) {
            $command .= $baseDir . $type . DIRECTORY_SEPARATOR . $variant . DIRECTORY_SEPARATOR . $version . DIRECTORY_SEPARATOR;
        } elseif ($variant !== ResourceDOInterface::DEFAULT_VARIANT) {
            $command .= $baseDir . $type . DIRECTORY_SEPARATOR . $variant . DIRECTORY_SEPARATOR;
        } else {
            $command .= $baseDir . $type . DIRECTORY_SEPARATOR;
        }
        $command .= ' -type f -name ' . $uuid . '.' . $type;

        return $command;
    }

    /**
     * @param $baseDir
     * @param $uuid
     * @param $type
     * @param $variant
     * @return int
     */
    protected function findLastExistsVersion($baseDir, $uuid, $type, $variant)
    {
        $variantVersions = $this->findAllVersions($baseDir, $uuid, $type, $variant);
        $lastVersion = (int)current($variantVersions);

        return $lastVersion;
    }

    protected function findAllVersions($baseDir, $uuid, $type, $variant)
    {
        $command = $this->getShellFindCommand($baseDir, $uuid, $type, $variant);
        $result = shell_exec($command);
        $result = array_filter(explode(PHP_EOL, $result));
        $prefixPath = $baseDir . $type . DIRECTORY_SEPARATOR . $variant . DIRECTORY_SEPARATOR;
        $prefixPathLenght = mb_strlen($prefixPath, 'UTF-8');
        $variantVersions = [];
        // Определяем последнюю версию
        foreach ($result as $path) {
            $path = str_replace('//', '/', $path);
            // Проверяем, что из shell не прилетело чего-нибудь лишнего, не содержащего нужные нам маршруты
            if (0 === strpos($path, $prefixPath)) {
                $suffix = substr($path, $prefixPathLenght);
                $nextSeparator = strpos($suffix, DIRECTORY_SEPARATOR);
                if ($nextSeparator) {
                    $variantVersions[] = substr($suffix, 0, $nextSeparator);
                }
            }
        }
        $variantVersions = array_unique($variantVersions);
        rsort($variantVersions, SORT_NUMERIC);

        return $variantVersions;
    }
}