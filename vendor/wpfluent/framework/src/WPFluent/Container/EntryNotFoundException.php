<?php

namespace TaskLedger\Framework\Container;

use Exception;
use TaskLedger\Framework\Container\Contracts\Psr\NotFoundExceptionInterface;

class EntryNotFoundException extends Exception implements NotFoundExceptionInterface
{
    //
}
