<?php

namespace Mason\FlexQueue\Contracts;

class HellowWorld extends BaseJob
{
    protected function beforeHandle(): void
    {
        echo "[HellowWorld] beforeHandle\n";
    }

    public function handle(): void
    {
        echo "Hello, World!\n";
    }

    protected function afterHandle(): void
    {
        echo "[HellowWorld] afterHandle\n";
    }
}
