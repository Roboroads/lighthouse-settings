<?php

namespace Roboroads\LighthouseSettings\Exceptions;

use Exception;
use Spatie\LaravelSettings\Settings;

class ClassNotFoundException extends Exception
{
    public function __construct(string $triedClass = "")
    {
        parent::__construct("Could not find settingsclass ".$triedClass);
    }
}
