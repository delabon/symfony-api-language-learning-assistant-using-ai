<?php

namespace App\Factory;

interface FactoryInterface
{
    /**
     * Makes an object in memory
     * @param array<string, mixed> $overrides
     * @return object
     */
    public function make(array $overrides = []): object;

    /**
     * Makes an object than save into DB
     * @param array<string, mixed> $overrides
     * @return object
     */
    public function create(array $overrides = []): object;
}
