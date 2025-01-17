<?php

namespace MLukman\SymfonyConfigOOP;

use MLukman\SymfonyConfigOOP\Attribute\BaseConfig;
use MLukman\SymfonyConfigOOP\Attribute\ObjectArrayConfig;
use MLukman\SymfonyConfigOOP\Attribute\ObjectConfig;
use MLukman\SymfonyConfigOOP\Attribute\OptionConfig;
use Override;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionEnum;
use ReflectionProperty;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use function enum_exists;

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
                        $pattr = $attribute->newInstance();
                        // this recursive function parses multi-dimensional array
                        $parseTree = function ($tree, $dim) use (&$parseTree, $pattr, $format, $context) {
                            $dim--;
                            $treeOut = [];
                            foreach ($tree as $pk => $pv) {
                                if ($dim == 0) {
                                    $treeOut[$pk] = $this->denormalize($pv, $pattr->prototypeClass, $format, $context);
                                    // special logic to inject the key into property _id if it exists
                                    if (property_exists($treeOut[$pk], '_id')) {
                                        (new \ReflectionClass($treeOut[$pk]))->getProperty('_id')->setValue($treeOut[$pk], $pk);
                                    }
                                } else {
                                    $treeOut[$pk] = $parseTree($pv, $dim);
                                }
                            }
                            return $treeOut;
                        };
                        $parray = $parseTree($data[$pname], $pattr->dimension);
                        $property->setValue($out, $parray);
                        break;

                    case OptionConfig::class:
                        if (enum_exists($ptype)) {
                            $refl = new ReflectionEnum($ptype);
                            if ($refl->isBacked()) {
                                $property->setValue($out, $ptype::from($data[$pname]));
                            } else {
                                $property->setValue($out, $refl->getCase($data[$pname])->getValue());
                            }
                        }
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
    public function supportsDenormalization(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = []
    ): bool {
        if (is_array($data)) {
            return true;
        }
        return false;
    }
}
