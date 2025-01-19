<?php

namespace MLukman\SymfonyConfigOOP\Tests\App\Config;

use MLukman\SymfonyConfigOOP\Attribute\ConfigKey;
use MLukman\SymfonyConfigOOP\Attribute\ConfigPath;
use MLukman\SymfonyConfigOOP\Attribute\OptionConfig;

class ChildConfig
{
    #[OptionConfig]
    public SimpleEnum $enum;

    #[OptionConfig]
    public BackedEnum $backedenum;

    #[ConfigKey]
    public string $name;

    #[ConfigPath(separator: ':')]
    public string $path;
}
