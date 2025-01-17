<?php

namespace MLukman\SymfonyConfigOOP\Attribute;

use Override;
use ReflectionProperty;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

abstract class NumericalConfig extends BaseConfig
{
    #[Override]
    public function __construct(?string $info = null, mixed $defaultValue = null, bool $isRequired = false, public ?int $min = null, public ?int $max = null, string|array|null $example = null, array $extras = [])
    {
        parent::__construct($info, $defaultValue, $isRequired, $example, $extras);
    }

    #[Override]
    protected function apply(NodeDefinition $node, ReflectionProperty $property): NodeDefinition
    {
        if (!is_null($this->min)) {
            $node->min($this->min);
        }
        if (!is_null($this->max)) {
            $node->max($this->max);
        }
        return parent::apply($node, $property);
    }
}
