<?php

declare(strict_types=1);

namespace Joomla\Registry;

final class Registry
{
    /** @var array<string,mixed> */
    private array $data;

    /** @param array<string,mixed> $data */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return $this->data;
    }
}
