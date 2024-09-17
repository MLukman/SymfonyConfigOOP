<?php

namespace MLukman\SymfonyConfigOOP\Attribute;

use Attribute;
use Override;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ObjectArrayConfig extends ObjectConfig
{

    #[Override]
    public function __construct(
            public string $prototypeClass,
            ?string $info = null,
            mixed $defaultValue = null,
            public bool $isRequired = false,
            string|array|null $example = null,
            array $extras = [])
    {
        parent::__construct($info, $defaultValue, $isRequired, $example, $extras);
    }

    #[\Override]
    static protected function getAppender(NodeDefinition $node)
    {
        return $node->arrayPrototype();
    }
}
