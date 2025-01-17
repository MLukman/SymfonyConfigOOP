<?php

namespace MLukman\SymfonyConfigOOP\Tests\App\Config;

use MLukman\SymfonyConfigOOP\Attribute\ArrayConfig;
use MLukman\SymfonyConfigOOP\Attribute\BooleanConfig;
use MLukman\SymfonyConfigOOP\Attribute\FloatConfig;
use MLukman\SymfonyConfigOOP\Attribute\IntegerConfig;
use MLukman\SymfonyConfigOOP\Attribute\ObjectArrayConfig;
use MLukman\SymfonyConfigOOP\Attribute\ObjectConfig;
use MLukman\SymfonyConfigOOP\Attribute\ScalarConfig;

class RootConfig
{
    #[ScalarConfig(isRequired: true)]
    public string $string;

    #[IntegerConfig(min: 0)]
    public int $int;

    #[FloatConfig(max: 100.0)]
    public float $float;

    #[BooleanConfig]
    public bool $bool;

    #[ArrayConfig]
    public array $array;

    #[ObjectConfig]
    public ChildConfig $child;

    #[ObjectArrayConfig(ChildConfig::class)]
    public array $children;

    #[ObjectArrayConfig(ChildConfig::class, 2)]
    public array $grandChildren;
}
