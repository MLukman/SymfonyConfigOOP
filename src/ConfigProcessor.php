<?php

namespace MLukman\SymfonyConfigOOP;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

class ConfigProcessor
{

    static Serializer $serializer;

    /**
     * 
     * @param array $configurations
     * @param string $rootClass
     * @return mixedProcess the input $configurations array and output as an object of the class
     * defined by $rootClass
     */
    static public function process(array $configurations, string $rootClass): mixed
    {
        if (!isset(self::$serializer)) {
            self::$serializer = new Serializer(
                    [new ConfigDenormalizer()],
                    ['json' => new JsonEncoder()]);
        }
        return self::$serializer->deserialize(\json_encode($configurations), $rootClass, 'json');
    }

    /**
     * Process the input $configurations array and output as an array of items of the class
     * defined by $arrayItemClass while preserving the original array keys
     */
    static public function processArray(array $configurations, string $arrayItemClass): array
    {
        $processed = [];
        foreach ($configurations as $key => $configuration) {
            $processed[$key] = self::process($configuration, $arrayItemClass);
        }
        return $processed;
    }
}
