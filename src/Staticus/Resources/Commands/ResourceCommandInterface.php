<?php
namespace Staticus\Resources\Commands;

use Staticus\Resources\Exceptions\CommandErrorException;
use Staticus\Resources\ResourceDOInterface;

interface ResourceCommandInterface
{
    /**
     * @return int|ResourceDOInterface
     * @throws CommandErrorException
     */
    public function __invoke();

}