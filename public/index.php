<?php
ini_set('display_errors', E_ALL);
define('REQUEST_MICROTIME', microtime(true));
chdir(dirname(__DIR__));
if (file_exists('vendor/autoload.php')) {
    $loader = require 'vendor/autoload.php';
}
$dotenv = new Dotenv\Dotenv('./');
$dotenv->load();

// -------
use AudioManager\Adapter\Google;
use AudioManager\Adapter\Ivona;
use AudioManager\Manager;

define('DATA_DIR', 'data/');
define('VOICE_DIR', DATA_DIR . 'voice/');
define('VOICE_PROVIDER_GOOGLE', 'google');
define('VOICE_PROVIDER_GOOGLE_DIR', VOICE_DIR . 'v1/');
define('VOICE_PROVIDER_IVONA', 'ivona');
define('VOICE_PROVIDER_IVONA_DIR', VOICE_DIR . 'v2/');
define('VOICE_FILE_EXTENSION', 'mp3');

function file_force_download($file)
{
    $mime = mime_content_type($file);
    set_header('X-Accel-Redirect: /' . $file);
    set_header('Content-Type: ' . $mime);
//    set_header('New-File-Path: ' . $file);
//    set_header('Content-Disposition: attachment; filename=' . basename($file));
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

$adapterName = strtolower(env('VOICE_DEFAULT_PROVIDER', VOICE_PROVIDER_GOOGLE));
switch ($adapterName) {
    case VOICE_PROVIDER_GOOGLE:
        $options = new Google\Options();
        $options->setLanguage('en');
        $options->setEncoding('UTF-8');
        $adapter = new Google($options);
        $voiceFilePath = VOICE_PROVIDER_GOOGLE_DIR;
        break;
    case VOICE_PROVIDER_IVONA:
        $secretKey = env('VOICE_IVONA_SECRET_KEY');
        $accessKey = env('VOICE_IVONA_ACCESS_KEY');
        $authenticate = new Ivona\Authenticate($secretKey, $accessKey);
        $options = new Ivona\Options($authenticate);
        $adapter = new Ivona($options);
        $adapter->setOptions($options);
        $voiceFilePath = VOICE_PROVIDER_GOOGLE_DIR;
        break;
    default:
        throw new RuntimeException('Not implemented functionality for voice provider: ' . $adapterName);
}
function get_text_from_uri()
{
    $text = urldecode($_SERVER["REQUEST_URI"]);
    if (preg_match('/.' . VOICE_FILE_EXTENSION . '$/u', $text)) {
        $text = str_replace('../', '', $text);
        $text = str_replace('/', '', $text);
        $text = preg_replace('/.' . VOICE_FILE_EXTENSION . '$/u', '', $text);
    } else {
        $text = '';
    }
    if (!$text) {
        header("HTTP/1.0 404 Not Found");
        throw new RuntimeException('Wrong audio request');
    }

    return $text;
}
$text = get_text_from_uri();
$textHash = md5($text);
$voiceFilePath .= $textHash . '.' . VOICE_FILE_EXTENSION;
if (file_exists($voiceFilePath)) {
    file_force_download($voiceFilePath);
} else {
    $manager = new Manager($adapter);
    $content = $manager->read($text);
    $headers = $manager->getHeaders();
    if (!isset($headers['http_code']) || $headers['http_code'] != 200) {
        throw new RuntimeException('Wrong http code from voice provider ' . $adapterName . ': ' . $headers['http_code']);
    }
    file_put_contents($voiceFilePath, $content);
    chmod($voiceFilePath, '0766');
    file_force_download($voiceFilePath);
}
