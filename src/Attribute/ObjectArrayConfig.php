<?php

namespace MLukman\SymfonyConfigOOP\Attribute;

use Attribute;
use MLukman\SymfonyConfigOOP\ConfigDenormalizer;
use Override;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ObjectArrayConfig extends ObjectConfig
{
    #[Override]
    public function __construct(
        public string $prototypeClass, public int $dimension = 1,
        ?string $info = null, mixed $defaultValue = null,
        public bool $isRequired = false, string|array|null $example = null,
        array $extras = []
    ) {
        parent::__construct($info, $defaultValue, $isRequired, $example, $extras);
    }

    #[Override]
    protected function childAppender(NodeDefinition $rootNode): NodeDefinition
    {
        $p = $rootNode->arrayPrototype();
        for ($i = 1; $i < $this->dimension; $i++) {
            $p = $p->arrayPrototype();
        }
        return $p;
    }

    #[Override]
    public function denormalize(
        ConfigDenormalizer $denormalizer, mixed $data, string $ptype,
        ?string $format, array $context
    ): mixed {
        // this recursive function parses multi-dimensional array
        $pattr = $this;
        $parseTree = function ($tree, $dim, $context) use (
            &$parseTree, $pattr, $format, $denormalizer
        ) {
            $dim--;
            $treeOut = [];
            foreach ($tree as $pk => $pv) {
                $ncontext = ['path' => array_merge($context['path'], [$pk])] + $context;
                if ($dim == 0) {
                    $treeOut[$pk] = $denormalizer->denormalize($pv, $pattr->prototypeClass, $format, $ncontext);
                } else {
                    $treeOut[$pk] = $parseTree($pv, $dim, $ncontext);
                }
            }
            return $treeOut;
        };
        return $parseTree($data, $pattr->dimension, $context);
    }
}
