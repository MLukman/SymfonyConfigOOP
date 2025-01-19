<?php

namespace MLukman\SymfonyConfigOOP\Attribute;

use Attribute;
use Exception;
use MLukman\SymfonyConfigOOP\ConfigDenormalizer;
use Override;
use ReflectionEnum;
use ReflectionEnumBackedCase;
use ReflectionProperty;
use Symfony\Component\Config\Definition\Builder\EnumNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use function enum_exists;

#[Attribute(Attribute::TARGET_PROPERTY)]
class OptionConfig extends BaseConfig
{
    #[Override]
    public function __construct(public array $options = [], ?string $info = null, mixed $defaultValue = null, bool $isRequired = false, mixed $example = null, array $extras = [])
    {
        parent::__construct($info, $defaultValue, $isRequired, $example, $extras);
    }

    #[Override]
    protected function createNode(string $name, string $rootClass): NodeDefinition
    {
        return new EnumNodeDefinition($name);
    }

    #[Override]
    protected function apply(NodeDefinition $node, ReflectionProperty $property): NodeDefinition
    {
        if (empty($this->options)) {
            if (enum_exists($ptype = $property->getType()->getName())) {
                $refl = new ReflectionEnum($ptype);
                if ($refl->isBacked()) {
                    $this->options = array_map(fn(ReflectionEnumBackedCase $case) => $case->getBackingValue(), $refl->getCases());
                } else {
                    $this->options = array_keys($refl->getConstants());
                }
            } else {
                throw new Exception("The attribute OptionConfig for property {$property->getName()} of class {$property->getDeclaringClass()->name} requires list of options since this property is not an Enum");
            }
        }
        $node->values($this->options);
        return parent::apply($node, $property);
    }

    #[Override]
    public function denormalize(ConfigDenormalizer $denormalizer, mixed $data, string $ptype, ?string $format, array $context): mixed
    {
        if (enum_exists($ptype)) {
            $refl = new ReflectionEnum($ptype);
            if ($refl->isBacked()) {
                return $ptype::from($data);
            } else {
                return $refl->getCase($data)->getValue();
            }
        } else {
            return $data;
        }
    }
}
