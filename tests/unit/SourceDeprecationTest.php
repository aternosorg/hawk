<?php

namespace Aternos\Hawk\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Compiles every source file in a separate PHP process with all errors enabled,
 * so compile-time deprecations are found even in classes the other tests never load.
 */
class SourceDeprecationTest extends TestCase
{
    public static function provideSourceFiles(): array
    {
        $sourceDirectory = dirname(__DIR__, 2) . "/src";
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceDirectory, RecursiveDirectoryIterator::SKIP_DOTS));

        $sourceFiles = [];
        foreach ($files as $file) {
            if ($file->getExtension() === "php") {
                $relativePath = substr($file->getPathname(), strlen($sourceDirectory) + 1);
                $sourceFiles[$relativePath] = [$file->getPathname()];
            }
        }
        ksort($sourceFiles);
        return $sourceFiles;
    }

    #[DataProvider('provideSourceFiles')]
    public function testSourceFileCompilesWithoutDeprecations(string $path): void
    {
        $command = [PHP_BINARY, "-d", "error_reporting=-1", "-d", "display_errors=stderr", "-d", "log_errors=0", "-l", $path];
        $process = proc_open($command, [1 => ["pipe", "w"], 2 => ["pipe", "w"]], $pipes);
        $this->assertIsResource($process, "Could not start PHP to lint " . $path);

        stream_get_contents($pipes[1]);
        $errorOutput = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        $this->assertSame(0, $exitCode, "PHP could not compile " . $path . ":\n" . $errorOutput);
        $this->assertSame("", trim($errorOutput), "PHP reported problems while compiling " . $path . ":\n" . $errorOutput);
    }
}
