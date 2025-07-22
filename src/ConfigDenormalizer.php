<?php

namespace MLukman\SymfonyConfigOOP;

use MLukman\SymfonyConfigOOP\Attribute\BaseConfig;
use MLukman\SymfonyConfigOOP\Attribute\ConfigAttribute;
use Override;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\PropertyAccess\PropertyAccess;
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
    public function supportsDenormalization(
        mixed $data, string $type, ?string $format = null, array $context = []
    ): bool {
        return is_array($data) && !empty($context['path'] ?? null);
    }

    #[Override]
    public function denormalize(
        mixed $data, string $type, ?string $format = null, array $context = []
    ): mixed {
        $refl = new ReflectionClass($type);
        $out = $refl->newInstance();
        $context['parents'][] = $out;
        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($refl->getProperties() as $property) {
            /** @var ReflectionProperty $property */
            $pname = $property->getName();
            $ptype = $property->getType()->__toString();
            $pdata = $data[$pname] ?? null;
            $ncontext = ['path' => array_merge($context['path'], [$pname])] + $context;
            foreach ($property->getAttributes() as $attribute) {
                $pattr = $attribute->newInstance();
                if (!($pattr instanceof ConfigAttribute) ||
                    !$pattr->canDenormalize($pdata, $context) ||
                    ($pattr instanceof BaseConfig && !isset($data[$pname]))) {
                    continue;
                }
                $pvalue = $pattr->denormalize($this, $pdata, $ptype, $format, $ncontext);
                if (is_null($pvalue) && !$property->getType()->allowsNull()) {
                    continue;
                }
                if ($accessor->isWritable($out, $pname)) {
                    $accessor->setValue($out, $pname, $pvalue);
                } else {
                    $property->setValue($out, $pvalue);
                }
            }
        }
        return $out;
    }
}
