<?php

namespace MLukman\SymfonyConfigOOP;

use MLukman\SymfonyConfigOOP\Attribute\ObjectArrayConfig;
use MLukman\SymfonyConfigOOP\Attribute\ObjectConfig;
use Override;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Yaml\Yaml;

class ConfigUtil
{
    public static Serializer $serializer;

    public static function parseConfigurationFiles(
        string $filePathsPattern, string $name, string $rootClass,
        int $dimension = 0
    ): object|array {
        $configs = [];
        foreach (\glob($filePathsPattern) as $file) {
            if (\is_file($file)) {
                $configs += Yaml::parse(\file_get_contents($file));
            }
        }
        return static::parseConfigurationValues($configs, $name, $rootClass, $dimension);
    }

    public static function parseConfigurationValues(
        array $configurationValues, string $name, string $rootClass,
        int $dimension = 0
    ): object|array {
        $configurations = self::createConfiguration($name, $rootClass, $dimension)->getConfigTreeBuilder()->buildTree()->finalize($configurationValues);
        return $dimension >= 1 ?
            self::deserializeArray($configurations, $rootClass, $dimension, [$name]) :
            self::deserializeObject($configurations, $rootClass, [$name]);
    }

    /**
     * Create ConfigurationInterface that is needed by
     * \Symfony\Component\Config\Definition\Processor::processConfiguration() method
     *
     * @param string $name
     * @param string $rootClass
     * @param int $dimension
     * @return ConfigurationInterface
     */
    public static function createConfiguration(
        string $name, string $rootClass, int $dimension = 0
    ): ConfigurationInterface {
        return new class($name, $rootClass, $dimension) implements ConfigurationInterface {
            public function __construct(
                private string $name, private string $rootClass,
                private int $dimension
            ) {
                if (!\class_exists($this->rootClass)) {
                    throw new InvalidConfigurationException("ConfigUtil::createConfiguration() failed because {$this->rootClass} class does not exist");
                }
            }

            #[Override]
            public function getConfigTreeBuilder(): TreeBuilder
            {
                $rootConfig = $this->dimension <= 0 ? new ObjectConfig() : new ObjectArrayConfig($this->rootClass, dimension: $this->dimension);
                return $rootConfig->createTreeBuilder($this->name, $this->rootClass);
            }
        };
    }

    /**
     * Populate \Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator that is passed to
     * \Symfony\Component\HttpKernel\Bundle\AbstractBundle::configure() method
     *
     * @param DefinitionConfigurator $definition
     * @param string $rootClass
     * @param int $dimension
     * @return void
     */
    public static function populateDefinitionConfigurator(
        DefinitionConfigurator $definition, string $rootClass,
        int $dimension = 0
    ): void {
        $rootConfig = $dimension <= 0 ? new ObjectConfig() : new ObjectArrayConfig($rootClass, dimension: $dimension);
        $rootConfig->populate($definition->rootNode(), $rootClass);
    }

    /**
     *
     * @param array $configurations
     * @param string $rootClass
     * @return mixedProcess the input $configurations array and output as an object of the class
     * defined by $rootClass
     */
    public static function deserializeObject(
        array $configurations, string $rootClass, array $path = []
    ): object {
        if (!isset(self::$serializer)) {
            self::$serializer = new Serializer(
                [new ConfigDenormalizer()],
                ['json' => new JsonEncoder()]
            );
        }
        return self::$serializer->deserialize(\json_encode($configurations), $rootClass, 'json', ['path' => $path]);
    }

    /**
     * Process the input $configurations array and output as an array of items of the class
     * defined by $arrayItemClass while preserving the original array keys
     */
    public static function deserializeArray(
        array $configurations, string $arrayItemClass, int $dimension = 1,
        array $path = []
    ): array {
        $deserialized = [];
        if ($dimension > 1) {
            foreach ($configurations as $key => $configuration) {
                $deserialized[$key] = self::deserializeArray($configuration, $arrayItemClass, $dimension - 1, array_merge($path, [$key]));
            }
        } else {
            foreach ($configurations as $key => $configuration) {
                $deserialized[$key] = self::deserializeObject($configuration, $arrayItemClass, array_merge($path, [$key]));
            }
        }
        return $deserialized;
    }
}
