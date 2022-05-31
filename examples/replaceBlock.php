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

if($argc < 4){
    echo "too few params given. choose: " . PHP_EOL . "
        3 arguments: all blocks will be replaced by stone " . PHP_EOL . "
        \t1: path of input file " . PHP_EOL . "
        \t2: path of output file " . PHP_EOL . "
        \t3: block coordinates(x,y,z) " . PHP_EOL . "
        4 arguments: " . PHP_EOL . "
        \t1: path of input file " . PHP_EOL . "
        \t2: path of output file " . PHP_EOL . "
        \t3: array of block coordinates(x,y,z) " . PHP_EOL . "
        \t4: name of the replacement block " . PHP_EOL;
    return;
}

if (!file_exists($argv[1])) {
    echo "directory does not exist. " . PHP_EOL;
    return;
}


switch ($argc) {
    case 4:
        $blockName = "minecraft:stone";
        break;

    case 5:
        $blockName = $argv[$argc - 1];
        break;

    default:
        echo "too many params given. choose: " . PHP_EOL . "
        3 arguments: all blocks will be replaced by stone " . PHP_EOL . "
        \t1: path of input file " . PHP_EOL . "
        \t2: path of output file " . PHP_EOL . "
        \t3: block coordinates(x,y,z) " . PHP_EOL . "
        4 arguments: " . PHP_EOL . "
        \t1: path of input file " . PHP_EOL . "
        \t2: path of output file " . PHP_EOL . "
        \t3: array of block coordinates(x,y,z) " . PHP_EOL . "
        \t4: name of the replacement block " . PHP_EOL;
        return;
}

$inputPath = $argv[1];
$outputPath = $argv[2];
$coordinates = explode(",", $argv[3]);
if(count($coordinates) !== 3){
    echo "Wrong coordinates " . PHP_EOL;
    return;
}
$blockPos = new McCoordinates3D($coordinates[0], $coordinates[1], $coordinates[2]);

if(!file_exists($outputPath))
{
    mkdir($outputPath, recursive: true);
}
$old = $inputPath . "/" . Region::getRegionFileNameFromBlock($blockPos);
$new =  $outputPath . "/" . Region::getRegionFileNameFromBlock($blockPos);
copy($old, $new);

// Could be more than one
$files[] = new File($outputPath . "/" . Region::getRegionFileNameFromBlock($blockPos));

$hawk = new Hawk(blockFiles: $files);
$hawk->replaceBlock($blockPos, $blockName);
echo "block replaced " . PHP_EOL;
$hawk->save();
echo "saved new file " . PHP_EOL;

foreach ($files as $file) {
    $file->close();
}
