<?php

if (!function_exists('get_variables')) {
    /**
     * Get variables array from params if all keys exists and not empty.
     *
     * @param array $params
     * @param array $keys
     *
     * @return array
     */
    function get_variables(array $params, array $keys): array
    {
        foreach ($keys as $key) {
            if ($value = $params[$key] ?? 0) $variables[lcfirst($key)] = $value;
            else return [];
        }
        return $variables ?? [];
    }
}

if (!function_exists('set_data')) {
    /**
     * Set an items on data array from params by relations using dat notation.
     *
     * @param array $data
     * @param array $params
     * @param array $relations
     * @param bool $overwrite
     */
    function set_data(array &$data, array $params, array $relations, bool $overwrite = true): void
    {
        foreach ($relations as $data_key => $param_key) {
            if ($value = get_with_dot($params, $param_key))
                set_with_dot($data, is_string($data_key) ? $data_key : $param_key, $value, $overwrite);
        }
    }
}

if (!function_exists('get_with_dot')) {
    /**
     * Get an item on an array using dot notation.
     *
     * @param array $data
     * @param string $key_string
     *
     * @return mixed|null
     */
    function get_with_dot(array $data, string $key_string)
    {
        foreach (explode('.', $key_string) as $key) {
            if (is_array($data)
                && array_key_exists($key, $data)) $data =& $data[$key];
            else return null;
        }
        return $data;
    }
}

if (!function_exists('set_with_dot')) {
    /**
     * Set an item on an array using dot notation.
     *
     * @param array $target
     * @param string $key_string
     * @param mixed $value
     * @param bool $overwrite
     */
    function set_with_dot(array &$target, string $key_string, $value, bool $overwrite = true): void
    {
        if (empty($key_string)) return;

        $inner = [];
        foreach (explode('.', $key_string) as $index => $key) {
            $var = $index === 0 ? 'target' : 'inner';
            if (!is_array($$var))
                $$var = $overwrite ? [] : ['original' => $$var];
            if (!array_key_exists($key, $$var))
                ${$var}[$key] = [];
            $inner =& ${$var}[$key];
        }
        if ($overwrite || empty($inner))
            $inner = $value;
        elseif (is_array($inner))
            $inner[$key_string] = $value;
    }
}

if (!function_exists('is_multi')) {
    /**
     * Check if array is multidimensional.
     * Except keys for checking.
     *
     * @param array $target
     * @param array $except
     * @return bool
     */
    function is_multi(array $target, array $except = []): bool
    {
        foreach ($target as $key => $element) {
            if (is_array($element)
                && !in_array($key, $except, true)) return true;
        }

        return false;
    }
}

if (!function_exists('array_wrap')) {
    /**
     * Wrap value to array.
     *
     * @param mixed $value
     * @return array
     */
    function array_wrap($value): array
    {
        return is_array($value) ? $value : [$value];
    }
}
