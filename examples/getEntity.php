<?php

require_once __DIR__ . "/../vendor/autoload.php";

use Aternos\Hawk\File;
use Aternos\Hawk\Hawk;
use Aternos\Hawk\McCoordinatesFloat;
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
    case 3:
        echo "too few params given. 
        \targument 1: path of world folder " . PHP_EOL . "
        \targument 2: name of entity e.g. 'minecraft:armor_stand' " . PHP_EOL . "
        \targument 3: entity coordinates(x,y,z) " . PHP_EOL;
        return;

    case 4:
        if (!file_exists($argv[1])) {
            echo "directory does not exist. " . PHP_EOL;
            return;
        }
        break;

    default:
        echo "too many params given. choose: " . PHP_EOL . "
        \targument 1: path of world folder " . PHP_EOL . "
        \targument 2: name of entity e.g. 'minecraft:armor_stand' " . PHP_EOL . "
        \targument 3: entity coordinates(x,y,z) " . PHP_EOL;
        return;
}

$inputPath = $argv[1];
$entityName = $argv[2];
$coordinates = explode(",", $argv[3]);
if(count($coordinates) !== 3){
    echo "Wrong coordinates " . PHP_EOL;
    return;
}
$entityPos = new McCoordinatesFloat($coordinates[0], $coordinates[1], $coordinates[2]);

$fileName = Region::getRegionFileNameFromBlock(McCoordinatesFloat::get3DCoordinates($entityPos));
$blockPath = $inputPath . "/region/" . $fileName;
$entitiesPath = $inputPath . "/entities/" . $fileName;

$blockFiles = (file_exists($blockPath)) ? [new File($blockPath)] : [];
$entitiesFiles = (file_exists($entitiesPath)) ? [new File($entitiesPath)] : [];

$hawk = new Hawk($blockFiles, $entitiesFiles);
$entities = $hawk->getEntities($entityName,$entityPos);

foreach ($entities as $index => $entity) {
    echo $index . ": " . $entity->getName() . " at " . $entity->getCoordinates() . " " . PHP_EOL;
}

$count = count($entities);
$message = " entity found " . PHP_EOL;
if($count !== 1){
    $message = " entities found " . PHP_EOL;
}
echo $count . $message;

// Close files

foreach ($blockFiles as $file){
    $file->close();
}
foreach ($entitiesFiles as $file){
    $file->close();
}
