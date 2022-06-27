<?php

namespace Roboroads\LighthouseSettings\Directives;

use Exception;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\App;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\ArgResolver;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Roboroads\LighthouseSettings\Exceptions\NotInstanceOfSettingsException;
use Spatie\LaravelSettings\Settings;

class SettingsDirective extends BaseDirective implements FieldResolver
{
    public static function definition(): string
    {
        return /** @lang GraphQL */ <<<'GRAPHQL'
"""
Make the fields of this type auto-fill based on the settings class.
"""
directive @settings(
    """
    The settings class, if different from the type name
    """
    class: String
) on FIELD_DEFINITION
GRAPHQL;
    }
    
    public function resolveField(FieldValue $fieldValue): FieldValue
    {
        $fieldValue->setResolver(function ($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Settings {
            // Get name of the settings class
            if ($this->directiveHasArgument('class')) {
                $settingsClass = $this->directiveArgValue('class');
            } else {
                $settingsClass = ASTHelper::modelName($this->definitionNode);
            }
            
            // Find out if we can instanciate the settings class
            try {
                $settingsInstance = App::make($settingsClass);
            } catch (Exception $ex) {
                $originalException = $ex;
                try {
                    $settingsInstance = App::make((config('lighthouse-settings.settings-namespace') ?? '\\App\\Settings') . '\\' . $settingsClass);
                } catch (Exception $ex) {
                    throw $originalException;
                }
            }
            
            // Instance should be subclass of Settings
            if(!is_subclass_of($settingsInstance, Settings::class)) {
                throw new NotInstanceOfSettingsException($settingsInstance::class);
            }
            /* @var Settings $settingsInstance */
            
            // If @setting is used for a mutation, update the settings
            if($resolveInfo->operation->operation === "mutation") {
                $settingsInstance->fill($resolveInfo->argumentSet->toArray());
                $settingsInstance->save();
            }
            
            return $settingsInstance;
        });
        
        return $fieldValue;
    }
}
