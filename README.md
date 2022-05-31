# Hawk

### About

Hawk is a PHP library to get and/or replace blocks and get and/or delete entities in Minecraft region files.
This allows the user to replace blocks or delete entities that will crash the server when loaded.

Currently, only the Minecraft Anvil world format (Minecraft Java Edition Version 1.16+) is supported.

### Installation

```bash
composer require aternos/hawk
```

## Usage

### Class Hawk

#### Setups:

Setup for blocks in any supported version:

```php
// New block coordinates
$blockPos = new McCoordinates3D(1, 2, 3);

// Path to your region file and calculating the filename from the coordinates
$inputPath = "/your/world/region/directory";
$blockFiles[] = new File($inputPath . "/" . Region::getRegionFileNameFromBlock($blockPos);

// Instantiating Hawk only with blockFiles
$hawk = new Hawk(blockFiles: $blockFiles);
```

Setup for entities prior 1.17:

```php
// New entity coordinates
$entityPos = new McCoordinatesFloat(1.2, 2.3, 3.4);

// Path to your region file and calculating the filename from the coordinates
$inputPath = "/your/world/region/directory";
$entitiesFiles[] = new File($inputPath . "/" . Region::getRegionFileNameFromBlock(McCoordinatesFloat::get3DCoordinates($entityPos));

// Instantiating Hawk only with blockFiles because entities used to be in the same file
$hawk = new Hawk(blockFiles: $entitiesFiles);
```

Setup for entities starting from 1.17:

```php
// Path to your entities directory and calculating the filename from the coordinates
$inputPath = "/your/world/entities/directory";
$entitiesFiles[] = new File($inputPath . "/" . Region::getRegionFileNameFromBlock(McCoordinatesFloat::get3DCoordinates($entityPos));

$hawk = new Hawk(entitiesFiles: $entitiesFiles);
```

#### How to read a block:

```php
$block = $hawk->getBlock($blockPos);
```

#### How to replace a block at x = 1, y = 2, z = 3 with wool(default is minecraft:stone):

```php
$hawk->replaceBlock($blockPos, "minecraft:wool");
$hawk->save();
```

#### Get all entities in a specific chunk:

```php
$entities = $hawk->getAllEntitiesFromChunk(McCoordinatesFloat::get3DCoordinates($entityPos));
```

#### How to get all entities next to float coordinates (there could be more than just one):

```php
$entities = $hawk->getEntities($entityName,$entityPos);
```

#### How to delete an entity:

```php
$entities = $hawk->getEntities($entityName,$entityPos);
$hawk->deleteEntity($entities[0]);
$hawk->save();
```

For more information see these examples: [getBlock.php](examples/getBlock.php), [replaceBlock.php](examples/replaceBlock.php), [getEntity.php](examples/getEntity.php), [getAllEntitiesInChunk.php](examples/getAllEntitiesInChunk.php), [deleteEntity.php](examples/deleteEntity.php).

#### Methods

| Name                                                                                                         | Return type                    | Description                                                                                                 |
|--------------------------------------------------------------------------------------------------------------|--------------------------------|-------------------------------------------------------------------------------------------------------------|
| loadBlockRegions([File](src/File.php)[] $files)                                                              | void                           | Load extra "block"("world/region") regions from $files into Hawk                                            |
| loadEntitiesRegions([File](src/File.php)[] $files)                                                           | void                           | Load extra "entities"("world/entities") regions from $files into Hawk                                       |
| getBlockRegionFromBlock([McCoordinates3D](src/McCoordinates3D.php) $coordinates)                             | [Region](src/BlockRegion.php)  | Get block region from block at $coordinates                                                                 |
| getEntitiesRegionFromBlock([McCoordinates3D](src/McCoordinates3D.php) $coordinates)                          | [Region](src/BlockRegion.php)  | Get entities region from block at $coordinates (see McCoordinatesFloat::get3DCoordinates for entity coords) |
| getBlock([McCoordinates3D](src/McCoordinates3D.php) $coordinates)                                            | [DataBlock](src/DataBlock.php) | Get block at $coordinates                                                                                   |
| replaceBlock([McCoordinates3D](src/McCoordinates3D.php) $coordinates, string $blockName = "minecraft:stone") | void                           | Replace block at $coordinates with block $blockName                                                         |
| getEntities(string $name, [McCoordinatesFloat](src/McCoordinatesFloat.php) $coordinates)                     | [Entity](src/Entity.php)[]     | Gets one or multiple entities at $coordinates                                                               |
| getAllEntitiesFromChunk([McCoordinates3D](src/McCoordinates3D.php) $blockCoordinates)                        | [Entity](src/Entity.php)[]     | Gets all entities in chunk based on $coordinates                                                            |
| deleteEntity([Entity](src/Entity.php) $entity)                                                               | void                           | Deletes an entity object                                                                                    |
| save()                                                                                                       | void                           | Save changes to file                                                                                        |

### Class Region

A region object represents a Minecraft region file. 
The main tasks of a region object is to read/decompress and write/compress chunks from/to its region file.
Additionally, it provides static functions to calculate region coordinates and its file name.

#### Methods

| Name                                                                                                     | Return type                                | Description                                   |
|----------------------------------------------------------------------------------------------------------|--------------------------------------------|-----------------------------------------------|
| static getRegionFileNameFromBlock([McCoordinates3D](src/McCoordinates3D.php) $coordinates)               | string                                     | Get region file name out of block coordinates |
| static getRegionCoordinatesFromFile([AbstractFile](src/AbstractFile.php) $file)                          | [McCoordinates2D](src/McCoordinates2D.php) | Get region coordinates from file name         | 
| static getRegionCoordinatesFromBlockCoordinates([McCoordinates3D](src/McCoordinates3D.php) $coordinates) | [McCoordinates2D](src/McCoordinates2D.php) | Get region coordinates from block coordinates |
| static getRegionCoordinatesFromChunkCoordinates([McCoordinates2D](src/McCoordinates2D.php) $coordinates) | [McCoordinates2D](src/McCoordinates2D.php) | Get region coordinates from chunk coordinates |

### Class Chunk

A chunk object represents a Minecraft chunk in Mojangs [chunk format](https://minecraft.fandom.com/wiki/Chunk_format).
The main task of a chunk object is to replace the sections tag of the NBT structure, compress the new chunk data and provide it to its region.
Additionally, it provides a static function to calculate chunk coordinates.

#### Methods

| Name                                                                                | Return type                                | Description                                  |
|-------------------------------------------------------------------------------------|--------------------------------------------|----------------------------------------------|
| static getChunkCoordinates([McCoordinates3D](src/McCoordinates3D.php) $coordinates) | [McCoordinates2D](src/McCoordinates2D.php) | Get chunk coordinates from block coordinates |

### Class Section

A section object represents a single section tag.

#### Methods

| Name                                                                                   | Return type                                | Description                                     |
|----------------------------------------------------------------------------------------|--------------------------------------------|-------------------------------------------------|
| static getSectionCoordinates([McCoordinates3D](src/McCoordinates3D.php) $coordinates)  | [McCoordinates3D](src/McCoordinates3D.php) | Get section coordinates from block coordinates  |
| static getBlockCoordinates([McCoordinates3D](src/McCoordinates3D.php) $coordinates)    | [McCoordinates3D](src/McCoordinates3D.php) | Get block coordinates relative to its section   |
