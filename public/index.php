<?php
function parce_file($file)
{
    return str_replace('_', '/', $file);
}
function get_path_if_exists($file)
{
    $path = '/var/www/fuse/i/img' . $file;
    if (realpath($path) && file_exists($path)) {
        return $path;
    }

    return false;
}

/**
 * @param $file
 * @param $path
 */
function file_force_download($file, $path)
{
    $mime = mime_content_type($path);
    $file = '/img' . $file;
    set_header('X-Accel-Redirect: ' . $file);
    set_header('Content-Type: ' . $mime);
    //        set_header('Content-Disposition: attachment; filename=' . basename($file));
}

function set_header($header)
{
//    e($header);
    header($header);
}
function e($text)
{
    echo '<pre>' . $text . '</pre>';
}
$file = $_SERVER["REQUEST_URI"];
if (!get_path_if_exists($file)) { // Блокируем прямое скачивание (тупое решение в лоб)
    $file = parce_file($file);
    $path = get_path_if_exists($file);
    if ($path) {
        file_force_download($file, $path);
    }
}
exit;