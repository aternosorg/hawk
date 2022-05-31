<?php

require_once __DIR__ . "/../vendor/autoload.php";

use Aternos\Hawk\Chunk;
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
        \targument 1: path of world folder " . PHP_EOL . "
        \targument 2: block coordinates(x,y,z) " . PHP_EOL;
        return;

    case 3:
        if (!file_exists($argv[1])) {
            echo "$argv[1] does not exist. " . PHP_EOL;
            return;
        }
        break;

    default:
        echo "too many params given. choose: " . PHP_EOL . "
        \targument 1: path of word folder " . PHP_EOL . "
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

$fileName = Region::getRegionFileNameFromBlock($blockPos);
$blockPath = $inputPath . "/region/" . $fileName;
$entitiesPath = $inputPath . "/entities/" . $fileName;

$blockFiles = (file_exists($blockPath)) ? [new File($blockPath)] : [];
$entitiesFiles = (file_exists($entitiesPath)) ? [new File($entitiesPath)] : [];

$hawk = new Hawk($blockFiles, $entitiesFiles);
$entities = $hawk->getAllEntitiesFromChunk($blockPos);
foreach ($entities as $entity){
    echo $entity . " " . PHP_EOL;
}
echo count($entities) . " entities in chunk " . Chunk::getChunkCoordinatesFromBlock($blockPos) . " " . PHP_EOL;

// Close files

foreach ($blockFiles as $file){
    $file->close();
}
foreach ($entitiesFiles as $file){
    $file->close();
}
