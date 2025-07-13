<?php

namespace MLukman\SymfonyConfigOOP\Attribute;

use Attribute;
use Override;
use ReflectionProperty;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ArrayConfig extends BaseConfig
{
    #[Override]
    public function __construct(
        protected string $type = "scalar", ?string $info = null,
        mixed $defaultValue = null, bool $isRequired = false,
        string|array|null $example = null, array $extras = []
    ) {
        parent::__construct($info, $defaultValue, $isRequired, $example, $extras);
    }

    #[Override]
    protected function createNode(string $name, string $rootClass): NodeDefinition
    {
        return new ArrayNodeDefinition($name);
    }

    #[Override]
    protected function apply(NodeDefinition $node, ReflectionProperty $property): NodeDefinition
    {
        /* @var $node ArrayNodeDefinition */
        switch ($this->type) {
            case "integer": case "int":
                $node->integerPrototype();
                break;
            case "float":
                $node->floatPrototype();
                break;
            case "boolean": case "bool":
                $node->booleanPrototype();
                break;
            default:
                $node->scalarPrototype();
        }
        if ($this->isRequired) {
            $node->requiresAtLeastOneElement();
        }
        return parent::apply($node, $property);
    }
}
