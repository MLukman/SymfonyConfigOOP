<?php

namespace MLukman\SymfonyConfigOOP\Tests\App\Config;

use MLukman\SymfonyConfigOOP\Attribute\OptionConfig;

class ChildConfig
{
    #[OptionConfig]
    public SimpleEnum $enum;

    #[OptionConfig]
    public BackedEnum $backedenum;
}
