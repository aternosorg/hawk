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
if (count($coordinates) !== 3) {
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
$entities = $hawk->getEntities($entityName, $entityPos);
switch (count($entities)) {
    case 0:
        throw new Exception("How did you get here?");
    case 1:
        $hawk->deleteEntity($entities[0]);
        echo "1 entity deleted " . PHP_EOL;
        save();
        return;
    default:
        $count = count($entities);
        foreach ($entities as $index => $entity) {
            echo $index . ": " . $entity->getName() . " at " . $entity->getCoordinates() . " " . PHP_EOL;
        }
        echo $count . ": delete all " . PHP_EOL;
        $answer = "";
        while (true) {
            $answer = readline("Choose which entity will be deleted. -1 will cancel. " . PHP_EOL);
            if (!is_numeric($answer) || intval($answer) > $count) {
                echo "Wrong input try again. " . PHP_EOL;
                continue;
            }
            switch (intval($answer)) {
                case -1:
                    echo "Exiting... " . PHP_EOL;
                    closeFiles();
                    return;
                case $count:
                    foreach ($entities as $entity){
                        $hawk->deleteEntity($entity);
                    }
                    save();
                    return;
                default:
                    $hawk->deleteEntity($entities[intval($answer)]);
                    save();
                    return;
            }

        }
}

function save(): void
{
    global $hawk;
    $hawk->save();
    closeFiles();
}

function closeFiles(): void
{
    global $blockFiles;
    global $entitiesFiles;
    foreach ($blockFiles as $file) {
        $file->close();
    }
    foreach ($entitiesFiles as $file) {
        $file->close();
    }
}
