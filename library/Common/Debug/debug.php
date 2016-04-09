<?php

if ( ! function_exists('dd'))
{
    /**
     * Dump and die
     *
     * @param  mixed  $value One or many arguments
     * @return mixed
     */
    function dd($value)
    {
        if (php_sapi_name() === 'cli-server' || php_sapi_name() === "cli") {
            array_map(function($x) { var_export($x); echo PHP_EOL; }, func_get_args());
        } else {
            array_map(function($x) { var_dump($x); }, func_get_args());
        }
        die;
    }

    /**
     * Dump and die with var_export instead of var_dump
     * @param $value
     */
    function ddc($value)
    {
        array_map(function($x) { var_export($x); echo PHP_EOL; }, func_get_args());
        die;
    }
}
