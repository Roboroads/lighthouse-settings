<?php

namespace Roboroads\LighthouseSettings\Helpers;

use GraphQL\Language\AST\Node;
use Illuminate\Support\Facades\App;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use Roboroads\LighthouseSettings\Exceptions\ClassNotFoundException;
use Roboroads\LighthouseSettings\Exceptions\NotInstanceOfSettingsException;
use Spatie\LaravelSettings\Settings;

class SettingsInstanceHelper
{
    protected Node $definitionNode;
    protected ?string $manualClass;
    
    public function __construct(Node $definitionNode, ?string $manualClass)
    {
        $this->definitionNode = $definitionNode;
        $this->manualClass = $manualClass;
    }
    
    
    public function getSettingsInstance(): Settings
    {
        return App::make($this->getSettingsClass());
    }
    
    public function getSettingsClass(): string
    {
        // Get name of the settings class
        if ($this->manualClass) {
            $settingsClass = $this->manualClass;
        } else {
            $settingsClass = ASTHelper::modelName($this->definitionNode);
        }
        
        if ($this->testSettingsClass($settingsClass)) {
            return $settingsClass;
        }
        
        $guesssedSettingsClass = (config('lighthouse-settings.settings-namespace') ?? '\\App\\Settings').'\\'.$settingsClass;
        if ($this->testSettingsClass($guesssedSettingsClass)) {
            return $guesssedSettingsClass;
        }
        
        throw new ClassNotFoundException($settingsClass);
    }
    
    public function testSettingsClass(string $settingsClass): bool
    {
        if (class_exists($settingsClass)) {
            if (!is_subclass_of($settingsClass, Settings::class)) {
                throw new NotInstanceOfSettingsException($settingsClass);
            }
            
            return true;
        }
        
        return false;
    }
}
