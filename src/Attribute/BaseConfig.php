<?php

namespace MLukman\SymfonyConfigOOP\Attribute;

use MLukman\SymfonyConfigOOP\ConfigDenormalizer;
use MLukman\SymfonyConfigOOP\Enum\ConfigExtra;
use Override;
use ReflectionProperty;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

abstract class BaseConfig implements ConfigAttribute
{
    public function __construct(
        public ?string $info = null, public mixed $defaultValue = null,
        public bool $isRequired = false,
        public string|array|null $example = null, public array $extras = []
    ) {
        
    }

    protected function apply(NodeDefinition $node, ReflectionProperty $property): NodeDefinition
    {
        if (!is_null($this->info)) {
            $node->info($this->info);
        }
        if (!is_null($this->defaultValue)) {
            $node->defaultValue($this->defaultValue);
        }
        if ($this->isRequired) {
            $node->isRequired();
        }
        if (!is_null($this->example)) {
            $node->example($this->example);
        }
        foreach ($this->extras as $extra) {
            switch ($extra) {
                case ConfigExtra::DefaultTrue:
                    $node->defaultTrue();
                    break;
                case ConfigExtra::DefaultFalse:
                    $node->defaultFalse();
                    break;
                case ConfigExtra::DefaultNull:
                    $node->defaultNull();
                    break;
                case ConfigExtra::CannotBeEmpty:
                    $node->cannotBeEmpty();
                    break;
            }
        }

        return $node;
    }

    #[Override]
    public function canDenormalize(mixed $data, array $context): bool
    {
        return true;
    }

    #[Override]
    public function denormalize(
        ConfigDenormalizer $denormalizer, mixed $data, string $ptype,
        ?string $format, array $context
    ): mixed {
        return $data;
    }

    abstract protected function createNode(string $name, string $rootClass): NodeDefinition;
}
