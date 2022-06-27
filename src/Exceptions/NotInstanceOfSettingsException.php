<?php

namespace Roboroads\LighthouseSettings\Exceptions;

use Exception;
use Spatie\LaravelSettings\Settings;

class NotInstanceOfSettingsException extends Exception
{
    public function __construct(string $triedClass = "")
    {
        parent::__construct("Class $triedClass is not of type ".Settings::class);
    }
}
