<?php

namespace MLukman\SymfonyConfigOOP\Attribute;

use Attribute;
use MLukman\SymfonyConfigOOP\ConfigDenormalizer;
use Override;

/**
 * Attributed property will be populated with the configuration path.
 * Examples: property1.property11.arrayKey1.0.property3
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class ConfigPath implements ConfigAttribute
{
    public function __construct(public string $separator = '.')
    {
        
    }

    #[Override]
    public function canDenormalize(mixed $data, array $context): bool
    {
        return !empty($context['path'] ?? null);
    }

    #[Override]
    public function denormalize(
        ConfigDenormalizer $denormalizer, mixed $data, string $ptype,
        ?string $format, array $context
    ): mixed {
        array_pop($context['path']);
        switch ($ptype) {
            case 'array':
                return $context['path'];
            case 'string':
                return implode($this->separator, $context['path']);
        }
    }
}
