<?php

namespace MLukman\SymfonyConfigOOP;

use MLukman\SymfonyConfigOOP\Attribute\BaseConfig;
use MLukman\SymfonyConfigOOP\Attribute\ConfigAttribute;
use Override;
use ReflectionClass;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ConfigDenormalizer implements DenormalizerInterface
{
    #[Override]
    public function getSupportedTypes(?string $format): array
    {
        return [
            'object' => false,
        ];
    }

    #[Override]
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return is_array($data) && !empty($context['path'] ?? null);
    }

    #[Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $refl = new ReflectionClass($type);
        $out = $refl->newInstance();
        foreach ($refl->getProperties() as $property) {
            $pname = $property->getName();
            $ptype = $property->getType()->getName();
            $pdata = $data[$pname] ?? null;
            $ncontext = ['path' => array_merge($context['path'], [$pname])] + $context;
            foreach ($property->getAttributes() as $attribute) {
                $pattr = $attribute->newInstance();
                if (!($pattr instanceof ConfigAttribute) ||
                    !$pattr->canDenormalize($pdata, $context) ||
                    ($pattr instanceof BaseConfig && !isset($data[$pname]))) {
                    continue;
                }
                $property->setValue($out, $pattr->denormalize($this, $pdata, $ptype, $format, $ncontext));
            }
        }
        return $out;
    }
}
