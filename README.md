# [Lighthouse](https://github.com/nuwave/lighthouse) plugin for [spatie/laravel-settings](https://github.com/Spatie/laravel-settings)

A nice way to make Lighthouse receive and update typed settings from the laravel-settings plugin.

## Install

```bash
composer require roboroads/lighthouse-laravel-settings
```

After installing you might want to re-generate the ide-helper files:

```bash
php artisan lighthouse:ide-helper
```

## Usage

First, create your settings as normal. Let's say we have the following settings file

```php
class GeneralSettings extends Settings
{
    public string $name;
    public string $url;
    public string $description;

    public static function group(): string
    {
        return 'general';
    }
}
```

### Querying settings

Use @settings to tell Lighthouse to get settings using the same name as the type:

```graphql
type GeneralSettings {
    name: String!
    url: String!
    description: String!
}

type Query {
    generalSettings: GeneralSettings! @settings
}
```

> Note: if your type does not match the pattern `App\Settings\TypeName` (in this case `App\Settings\GeneralSettings`) you can use `@settings(class: "\\Path\\To\\YourSettings")`.


### Mutating settings

Use @settings to tell Lighthouse to update settings using the same name as the type:

```graphql
type GeneralSettings {
    name: String!
    url: String!
    description: String!
}

type Mutation {
    updateGeneralSettings(
        name: String!
        url: String!
        description: String!
    ): GeneralSettings! @settings
}
```

> Note: Except for @can, you can use @rules, @trim, etc. like you can when updating models.

### Using @canSettings instead of @can

Since Lighthouse really wants to connect @can to an actual Model, you have to use @canSettings to use the ability to authorize the user for settings.

```graphql
type Mutation {
    updateGeneralSettings(
        name: String!
        url: String!
        description: String!
    ): GeneralSettings! @settings @canSettings(ability: "editSettings")
}
```

```php
//Defining a gate
Gate::define('editSettings', function (User $user, string $settingsClass) {
    return $user->isAdmin();
});
```

## Optional configuration

If you want to tweak this plugin you can publish `config/laravel-settings.php` with:
```bash
php artisan vendor:publish --provider="Roboroads\LighthouseSettings\ServiceProvider"
```

### settings-namespace

Change the default namespace this package looks in when searching for settings. 
