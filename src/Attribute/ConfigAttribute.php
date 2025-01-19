<?php

namespace MLukman\SymfonyConfigOOP\Attribute;

use MLukman\SymfonyConfigOOP\ConfigDenormalizer;

interface ConfigAttribute
{
    public function canDenormalize(mixed $data, array $context): bool;
    public function denormalize(ConfigDenormalizer $denormalizer, mixed $data, string $ptype, ?string $format, array $context): mixed;
}
