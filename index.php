<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\PathMap;

$pathMap = new PathMap(\file_get_contents(dirname(__FILE__) . '/maps/map3.txt'));

$pathMap->follow();

echo $pathMap->getLetters() . PHP_EOL . $pathMap->getPath() . PHP_EOL;
