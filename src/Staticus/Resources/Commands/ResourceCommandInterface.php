<?php
namespace Staticus\Resources\Commands;

use Staticus\Resources\Exceptions\CommandErrorException;
use Staticus\Resources\ResourceDOInterface;

interface ResourceCommandInterface
{
    const FAIL = 0;
    const SUCCESS = 1;
    const NOT_EXISTS = 2;
    const ALREADY_EXISTS = 3;
    /**
     * @return int|ResourceDOInterface
     * @throws CommandErrorException
     */
    public function run();

}