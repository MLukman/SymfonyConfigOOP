<?php

namespace MLukman\SymfonyConfigOOP\Attribute;

use Attribute;
use Override;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ObjectConfig extends BaseConfig
{

    static public function createTreeBuilder(string $name, string $rootClass): TreeBuilder
    {
        $treeBuilder = new TreeBuilder($name);
        $rootNode = $treeBuilder->getRootNode();
        self::populate($rootNode, $rootClass);
        return $treeBuilder;
    }

    static public function populate(NodeDefinition $rootNode, string $rootClass)
    {
        $appender = static::getAppender($rootNode);
        $reflection = new ReflectionClass($rootClass);
        foreach ($reflection->getProperties() as $property) {
            /* @var $property ReflectionProperty */
            foreach ($property->getAttributes() as $attribute) {
                /* @var $attribute ReflectionAttribute */
                if (is_subclass_of($attribute->getName(), BaseConfig::class)) {
                    /* @var $childAttribute BaseConfig */
                    $childAttribute = $attribute->newInstance();
                    $childNodeDefinition = call_user_func(
                            [$attribute->getName(), 'createNodeDefinition'],
                            $property->getName(),
                            $childAttribute->prototypeClass ?? $property->getType()->getName());
                    $childAttribute->apply($childNodeDefinition, $property);
                    $appender->append($childNodeDefinition);
                }
            }
        }
    }

    static protected function getAppender(NodeDefinition $node)
    {
        return $node;
    }

    #[Override]
    static protected function createNodeDefinition(string $name, string $rootClass): NodeDefinition
    {
        return static::createTreeBuilder($name, $rootClass)->getRootNode();
    }
}
