<?php

namespace MLukman\SymfonyConfigOOP\Tests\App;

use Override;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

abstract class TestCaseBase extends TestCase
{
    protected static Processor $processor;

    #[Override]
    public static function setUpBeforeClass(): void
    {
        self::$processor = new Processor();
    }

    protected static function loadConfigFile(string $path): array
    {
        return [Yaml::parse(\file_get_contents($path))];
    }
}
