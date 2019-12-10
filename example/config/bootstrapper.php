<?php
require_once(GLOBAL_DIR.'/vendor/autoload.php');
// Support for .env files
if (file_exists(GLOBAL_DIR.'/.env')) {
    $envFile = fopen(GLOBAL_DIR.'/.env', 'r');
    $envArray = [];
    while (($line = fgets($envFile)) !== false) {
        $line = str_replace("\r\n",'',$line);
        $line = str_replace("\n",'',$line);
        $lineArray = explode('=', $line);
        $envArray[$lineArray[0]] = $lineArray[1];
    }
    fclose($envFile);
}

function ENV($key) {
    global $envArray;
    if (isset($envArray[$key])) {
        return $envArray[$key];
    }
    return false;
}
//composer dump-autoload -o
$Core = new Amper\Core();
$Core->run();

?>