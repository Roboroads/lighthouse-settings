<?php

namespace Roboroads\LighthouseSettings\Directives;

use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Auth\CanDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Roboroads\LighthouseSettings\Helpers\SettingsInstanceHelper;

class CanSettingsDirective extends CanDirective
{
    public static function definition(): string
    {
        return /** @lang GraphQL */ <<<'GRAPHQL'
"""
Alternative for the @can directive so it can be used with settings
"""
directive @canSettings(
    """
    The ability to check permissions for.
    """
    ability: String!
    
    """
    The settings class, if different from the type name
    """
    class: String
    
    """
    Pass along the client given input data as arguments to `Gate::check`.
    """
    injectArgs: Boolean = false
    
    """
    Statically defined arguments that are passed to `Gate::check`.
    
    You may pass arbitrary GraphQL literals,
    e.g.: [1, 2, 3] or { foo: "bar" }
    """
    args: CanArgs
) on FIELD_DEFINITION
GRAPHQL;
    }
    
    public function handleField(FieldValue $fieldValue, Closure $next): FieldValue
    {
        $previousResolver = $fieldValue->getResolver();
        $ability = $this->directiveArgValue('ability');
    
        $fieldValue->setResolver(function ($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) use ($ability, $previousResolver) {
            $gate = $this->gate->forUser($context->user());
            $checkArguments = $this->buildCheckArguments($args);
    
            $settingsClass = (new SettingsInstanceHelper($this->definitionNode, $this->directiveArgValue('class')))->getSettingsClass();
            $this->authorize($gate, $ability, $settingsClass, $checkArguments);
        
            return $previousResolver($root, $args, $context, $resolveInfo);
        });
    
        return $next($fieldValue);
    }
}
