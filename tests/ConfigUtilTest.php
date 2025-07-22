<?php

namespace MLukman\SymfonyConfigOOP\Tests;

use MLukman\SymfonyConfigOOP\ConfigUtil;
use MLukman\SymfonyConfigOOP\Tests\App\Config\BackedEnum;
use MLukman\SymfonyConfigOOP\Tests\App\Config\ChildConfig;
use MLukman\SymfonyConfigOOP\Tests\App\Config\RootConfig;
use MLukman\SymfonyConfigOOP\Tests\App\Config\SimpleEnum;
use MLukman\SymfonyConfigOOP\Tests\App\TestCaseBase;
use ReflectionProperty;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class ConfigUtilTest extends TestCaseBase
{
    public function testProcess(): void
    {
        $configs = self::loadConfigFile(__DIR__ . '/config/single.yml');
        $combinedConfigs = self::$processor->processConfiguration(ConfigUtil::createConfiguration('single', RootConfig::class), $configs);
        $actual = ConfigUtil::deserializeObject($combinedConfigs, RootConfig::class, ['single']);
        //print_r($actual);
        $this->assertInstanceOf(RootConfig::class, $actual);
        $this->assertEquals($combinedConfigs['string'], $actual->string);
        $this->assertEquals($combinedConfigs['int'], $actual->int);
        $this->assertEquals($combinedConfigs['float'], $actual->float);
        $this->assertEquals($combinedConfigs['bool'], $actual->bool);
        $this->assertEquals($combinedConfigs['array'], $actual->array);
        $this->assertInstanceOf(ChildConfig::class, $actual->child);
        $this->assertCount(3, $actual->children);
        $this->assertEquals(SimpleEnum::TWO, $actual->children['case2']->enum);
        $this->assertEquals(BackedEnum::THREE, $actual->children['case3']->backedenum);
        $this->assertEquals(BackedEnum::TWO, $actual->grandChildren['20']['21']->backedenum);
        $this->assertEquals('single:grandChildren:10:11', $actual->grandChildren['10']['11']->path);
        $this->assertSame($actual, $actual->child->parent->child->parent);
        $this->assertSame($actual, $actual->children['case2']->parent);
        $this->assertSame($actual, $actual->grandChildren['20']['21']->parent);
        $this->assertNull($actual->child->invalidParent);
        $this->assertFalse((new ReflectionProperty($actual->child, 'invalidParentNotNull'))->isInitialized($actual->child));
    }

    public function testProcessArray(): void
    {
        $configs = self::loadConfigFile(__DIR__ . '/config/array.yml');
        $configMeta = self::$processor->processConfiguration(ConfigUtil::createConfiguration('array', RootConfig::class, 2), $configs);
        $actual = ConfigUtil::deserializeArray($configMeta, RootConfig::class, 2, ['array']);
        //print_r($actual);
        $this->assertIsArray($actual);
        $this->assertArrayHasKey('one', $actual);
        $this->assertInstanceOf(RootConfig::class, $actual['one']['one_three']);
        $this->assertEquals($configMeta['one']['one_three']['string'], $actual['one']['one_three']->string);
        $this->assertEquals(BackedEnum::THREE, $actual['one']['one_three']->child->backedenum);
        $this->assertEquals('array:one:one_three:child', $actual['one']['one_three']->child->path);
    }

    public function testChildNoParent(): void
    {
        $configs = self::loadConfigFile(__DIR__ . '/config/child_no_parent.yml');
        $combinedConfigs = self::$processor->processConfiguration(ConfigUtil::createConfiguration('child_no_parent', ChildConfig::class), $configs);
        $actual = ConfigUtil::deserializeObject($combinedConfigs, ChildConfig::class, ['child']);
        $this->assertInstanceOf(ChildConfig::class, $actual);
        $this->assertFalse((new ReflectionProperty($actual, 'parent'))->isInitialized($actual));
    }

    public function testExceptionInvalidEnum(): void
    {
        $configs = self::loadConfigFile(__DIR__ . '/config/invalid_enum.yml');
        $this->expectException(InvalidConfigurationException::class);
        self::$processor->processConfiguration(ConfigUtil::createConfiguration('exception', RootConfig::class, 0), $configs);
    }

    public function testExceptionInvalidRequired(): void
    {
        $configs = self::loadConfigFile(__DIR__ . '/config/invalid_required.yml');
        $this->expectException(InvalidConfigurationException::class);
        self::$processor->processConfiguration(ConfigUtil::createConfiguration('exception', RootConfig::class, 0), $configs);
    }

    public function testExceptionInvalidMin(): void
    {
        $configs = self::loadConfigFile(__DIR__ . '/config/invalid_min.yml');
        $this->expectException(InvalidConfigurationException::class);
        self::$processor->processConfiguration(ConfigUtil::createConfiguration('exception', RootConfig::class, 0), $configs);
    }

    public function testExceptionInvalidMax(): void
    {
        $configs = self::loadConfigFile(__DIR__ . '/config/invalid_max.yml');
        $this->expectException(InvalidConfigurationException::class);
        self::$processor->processConfiguration(ConfigUtil::createConfiguration('exception', RootConfig::class, 0), $configs);
    }
}
