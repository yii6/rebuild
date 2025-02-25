<?php

declare(strict_types=1);

namespace Rebuild\Contract;

interface ConfigInterface
{
    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $key identifier of the entry to look for
     * @param mixed $default default value of the entry when does not found
     * @return mixed entry
     */
    public function get(string $key, $default = null);

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $keys identifier of the entry to look for
     * @return bool
     */
    public function has(string $keys);

    /**
     * Set a value to the container by its identifier.
     *
     * @param string $key identifier of the entry to set
     * @param mixed $value the value that save to container
     */
    public function set(string $key, $value);
}
