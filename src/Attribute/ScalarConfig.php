<?php

namespace MLukman\SymfonyConfigOOP\Attribute;

use Attribute;
use Override;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ScalarConfig extends BaseConfig
{

    #[Override]
    static protected function createNodeDefinition(string $name, string $rootClass): NodeDefinition
    {
        return new ScalarNodeDefinition($name);
    }
}
