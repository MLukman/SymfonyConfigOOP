<?php

namespace MLukman\SymfonyConfigOOP\Attribute;

use Attribute;
use Override;
use Symfony\Component\Config\Definition\Builder\IntegerNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IntegerConfig extends NumericalConfig
{
    #[Override]
    protected function createNode(string $name, string $rootClass): NodeDefinition
    {
        return new IntegerNodeDefinition($name);
    }
}
