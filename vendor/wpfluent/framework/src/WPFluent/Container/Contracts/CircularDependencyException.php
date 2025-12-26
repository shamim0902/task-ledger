<?php

namespace TaskLedger\Framework\Container\Contracts;

use Exception;
use TaskLedger\Framework\Container\Contracts\Psr\ContainerExceptionInterface;

class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
