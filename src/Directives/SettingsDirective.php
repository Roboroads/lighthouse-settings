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
use Roboroads\LighthouseSettings\Helpers\SettingsInstanceHelper;
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
            $settingsInstance = (new SettingsInstanceHelper($this->definitionNode, $this->directiveArgValue('class')))->getSettingsInstance();
            
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
