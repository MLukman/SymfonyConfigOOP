<?php

namespace MLukman\SymfonyConfigOOP\Attribute;

use Attribute;
use Override;
use ReflectionProperty;
use Symfony\Component\Config\Definition\Builder\EnumNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

#[Attribute(Attribute::TARGET_PROPERTY)]
class OptionConfig extends BaseConfig
{

    #[\Override]
    public function __construct(public array $options, ?string $info = null, mixed $defaultValue = null, bool $isRequired = false, mixed $example = null, array $extras = [])
    {
        parent::__construct($info, $defaultValue, $isRequired, $example, $extras);
    }

    #[Override]
    static protected function createNodeDefinition(string $name, string $rootClass): NodeDefinition
    {
        return new EnumNodeDefinition($name);
    }

    #[\Override]
    protected function apply(NodeDefinition $node, ReflectionProperty $property): NodeDefinition
    {
        $node->values($this->options);
        return parent::apply($node, $property);
    }
}
