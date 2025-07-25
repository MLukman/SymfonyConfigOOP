<?php

namespace MLukman\SymfonyConfigOOP\Attribute;

use Attribute;
use MLukman\SymfonyConfigOOP\ConfigDenormalizer;

/**
 * Attributed property will be populated with the parent configuration.
 * The type of the property must match the parent type, otherwise it will be set to null or undefined.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class ConfigParent implements ConfigAttribute
{
    public function canDenormalize(mixed $data, array $context): bool
    {
        return count($context['parents'] ?? []) >= 2;
    }

    public function denormalize(
        ConfigDenormalizer $denormalizer,
        mixed $data,
        string $ptype,
        ?string $format,
        array $context
    ): mixed {
        $parent = $context['parents'][count($context['parents']) - 2];
        if (\array_filter(explode('|', $ptype), fn($ptype) => is_a($parent, $ptype)) || $ptype == 'mixed') {
            return $parent;
        }
        return null;
    }
}
