<?php

require_once __DIR__ . "/../vendor/autoload.php";

use Aternos\Hawk\File;
use Aternos\Hawk\Hawk;
use Aternos\Hawk\McCoordinates3D;
use Aternos\Hawk\Region;

if (!isset($argc)) {
    echo "argc and argv disabled. check php.ini " . PHP_EOL;
    return;
}

$yes = ["Y", "y", false];
$no = ["N", "n"];

switch ($argc) {
    case 1:
    case 2:
        echo "too few params given. 
        \targument 1: path of input file " . PHP_EOL . "
        \targument 2: block coordinates(x,y,z) " . PHP_EOL;
        return;

    case 3:
        if (!file_exists($argv[1])) {
            echo "directory does not exist. " . PHP_EOL;
            return;
        }
        break;

    default:
        echo "too many params given. choose: " . PHP_EOL . "
        \targument 1: path of input file " . PHP_EOL . "
        \targument 2: block coordinates(x,y,z) " . PHP_EOL;
        return;
}

$inputPath = $argv[1];
$coordinates = explode(",", $argv[2]);
if(count($coordinates) !== 3){
    echo "Wrong coordinates " . PHP_EOL;
    return;
}
$blockPos = new McCoordinates3D($coordinates[0], $coordinates[1], $coordinates[2]);

// Could be more than one
$files[] = new File($inputPath . "/" . Region::getRegionFileNameFromBlock($blockPos));

$hawk = new Hawk(blockFiles: $files);
echo strval($hawk->getBlock($blockPos));

foreach ($files as $file) {
    $file->close();
}

