<?php

namespace MLukman\SymfonyConfigOOP;

use Exception;
use MLukman\SymfonyConfigOOP\Attribute\ObjectArrayConfig;
use MLukman\SymfonyConfigOOP\Attribute\ObjectConfig;
use Override;
use ReflectionClass;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

class ConfigUtil
{
    public static Serializer $serializer;

    /**
     * Create ConfigurationInterface that is needed by
     * \Symfony\Component\Config\Definition\Processor::processConfiguration() method
     *
     * @param string $name
     * @param string $rootClass
     * @param int $dimension
     * @return ConfigurationInterface
     */
    public static function createConfiguration(string $name, string $rootClass, int $dimension = 0): ConfigurationInterface
    {
        return new class ($name, $rootClass, $dimension) implements ConfigurationInterface {
            public function __construct(private string $name, private string $rootClass, private int $dimension)
            {
                if (!class_exists($this->rootClass)) {
                    throw new Exception("{$this->rootClass} class does not exist");
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
    public static function populateDefinitionConfigurator(DefinitionConfigurator $definition, string $rootClass, int $dimension = 0): void
    {
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
    public static function process(array $configurations, string $rootClass): mixed
    {
        if (!isset(self::$serializer)) {
            self::$serializer = new Serializer(
                [new ConfigDenormalizer()],
                ['json' => new JsonEncoder()]
            );
        }
        return self::$serializer->deserialize(\json_encode($configurations), $rootClass, 'json');
    }

    /**
     * Process the input $configurations array and output as an array of items of the class
     * defined by $arrayItemClass while preserving the original array keys
     */
    public static function processArray(array $configurations, string $arrayItemClass): array
    {
        $processed = [];
        foreach ($configurations as $key => $configuration) {
            $processed[$key] = self::process($configuration, $arrayItemClass);
            if (property_exists($processed[$key], '_id')) {
                (new ReflectionClass($processed[$key]))->getProperty('_id')->setValue($processed[$key], $key);
            }
        }
        return $processed;
    }
}
