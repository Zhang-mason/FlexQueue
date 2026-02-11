<?php

namespace Mason\FlexQueue\Contracts;

abstract class BaseJob implements JobInterface
{
    private $queue = 'default';
    public function __construct()
    {
    }
    public function getQueue(): string
    {
        return $this->queue;
    }
}
