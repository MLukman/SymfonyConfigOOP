<?php

namespace MLukman\SymfonyConfigOOP\Attribute;

use Attribute;
use MLukman\SymfonyConfigOOP\ConfigDenormalizer;
use Override;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ObjectConfig extends BaseConfig
{
    public function createTreeBuilder(string $name, string $rootClass): TreeBuilder
    {
        $treeBuilder = new TreeBuilder($name);
        $this->populate($treeBuilder->getRootNode(), $rootClass);
        return $treeBuilder;
    }

    public function populate(NodeDefinition $rootNode, string $rootClass)
    {
        $appender = $this->childAppender($rootNode);
        $reflection = new ReflectionClass($rootClass);
        foreach ($reflection->getProperties() as $property) {
            /* @var $property ReflectionProperty */
            foreach ($property->getAttributes() as $attribute) {
                /* @var $attribute ReflectionAttribute */
                if (is_subclass_of($attribute->getName(), BaseConfig::class)) {
                    /* @var $childAttribute BaseConfig */
                    $childAttribute = $attribute->newInstance();
                    $childNode = $childAttribute->createNode(
                        $property->getName(),
                        $childAttribute->prototypeClass ?? $property->getType()->getName()
                    );
                    $childAttribute->apply($childNode, $property);
                    $appender->append($childNode);
                }
            }
        }
    }

    protected function childAppender(NodeDefinition $rootNode): NodeDefinition
    {
        return $rootNode;
    }

    #[Override]
    protected function createNode(string $name, string $rootClass): NodeDefinition
    {
        return $this->createTreeBuilder($name, $rootClass)->getRootNode();
    }

    #[Override]
    public function canDenormalize(mixed $data, array $context): bool
    {
        return is_array($data);
    }

    #[Override]
    public function denormalize(ConfigDenormalizer $denormalizer, mixed $data, string $ptype, ?string $format, array $context): mixed
    {
        if (!is_array($data)) {
            return null;
        }
        return $denormalizer->denormalize($data, $ptype, $format, $context);
    }
}
