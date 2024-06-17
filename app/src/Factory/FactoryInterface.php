<?php

namespace App\Factory;

interface FactoryInterface
{
    /**
     * Makes an object in memory
     * @param array $overrides
     * @return $this
     */
    public function make(array $overrides = []): object;

    /**
     * Makes an object than save into DB
     * @param array $overrides
     * @return $this
     */
    public function create(array $overrides = []): object;
}