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
        array_map(function($x) { var_dump($x); }, func_get_args());
        die;
    }
}
