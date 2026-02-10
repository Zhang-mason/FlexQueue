<?php

declare(strict_types=1);

namespace Mason\FlexQueue\Contracts;

interface JobInterface
{
    public function handle(): void;
}
