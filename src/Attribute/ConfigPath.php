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
    public function __construct(public string $separator = '.', public int $maxTrace = 9999)
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
        $paths = array_slice($context['path'], -min(count($context['path']), $this->maxTrace));
        switch ($ptype) {
            case 'array':
                return $paths;
            case 'string':
                return implode($this->separator, $paths);
        }
    }
}
