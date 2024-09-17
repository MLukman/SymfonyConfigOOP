<?php

namespace MLukman\SymfonyConfigOOP;

use MLukman\SymfonyConfigOOP\Attribute\BaseConfig;
use MLukman\SymfonyConfigOOP\Attribute\ObjectArrayConfig;
use MLukman\SymfonyConfigOOP\Attribute\ObjectConfig;
use Override;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ConfigDenormalizer implements DenormalizerInterface
{

    #[Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        if (!is_array($data)) {
            return null;
        }
        $refl = new ReflectionClass($type);
        $out = $refl->newInstance();

        foreach ($refl->getProperties() as $property) {
            /* @var $property ReflectionProperty */
            $pname = $property->getName();
            $ptype = $property->getType()->getName();
            if (!isset($data[$pname])) {
                continue;
            }
            foreach ($property->getAttributes() as $attribute) {
                if (!is_subclass_of($attribute->getName(), BaseConfig::class)) {
                    continue;
                }
                /* @var $attribute ReflectionAttribute */
                switch ($attribute->getName()) {
                    case ObjectConfig::class:
                        $property->setValue($out, $this->denormalize($data[$pname], $ptype, $format, $context));
                        break;

                    case ObjectArrayConfig::class:
                        $parray = [];
                        $pattr = $attribute->newInstance();
                        foreach ($data[$pname] as $pk => $pv) {
                            $parray[$pk] = $this->denormalize($pv, $pattr->prototypeClass, $format, $context);
                            // special logic to inject the key into property _id if it exists
                            if (property_exists($parray[$pk], '_id')) {
                                (new \ReflectionClass($parray[$pk]))->getProperty('_id')->setValue($parray[$pk], $pk);
                            }
                        }
                        $property->setValue($out, $parray);
                        break;

                    default:
                        $property->setValue($out, $data[$pname]);
                }
            }
        }
        return $out;
    }

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
        if (is_array($data)) {
            return true;
        }
        return false;
    }
}