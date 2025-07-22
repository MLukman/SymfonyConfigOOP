<?php

namespace MLukman\SymfonyConfigOOP\Attribute;

use Attribute;
use MLukman\SymfonyConfigOOP\ConfigDenormalizer;
use Override;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * Attributed property will be populated with the configuration key.
 * Examples: property1, 0
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class ConfigKey implements ConfigAttribute
{
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
        $last = array_pop($context['path']);
        if ($ptype == 'string') {
            return end($context['path']);
        } else {
            throw new InvalidConfigurationException(printf('Configuration "%s.%s" must be a string', implode('.', $context['path']), $last));
        }
    }
}
