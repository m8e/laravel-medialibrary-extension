# Extra features for [spatie/laravel-medialibrary](https://github.com/spatie/laravel-medialibrary) package

[![Source Code](https://img.shields.io/badge/source-okipa/laravel--medialibrary--extension-blue.svg)](https://github.com/Okipa/laravel-medialibrary-extension)
[![Latest Version](https://img.shields.io/packagist/v/okipa/laravel-medialibrary-extension.svg?style=flat-square)](https://packagist.org/packages/okipa/laravel-medialibrary-extension)
[![Total Downloads](https://img.shields.io/packagist/dt/okipa/laravel-medialibrary-extension.svg?style=flat-square)](https://packagist.org/packages/okipa/laravel-medialibrary-extension)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)
[![StyleCI](https://styleci.io/repos/178888702/shield)](https://styleci.io/repos/178888702)
[![Build Status](https://scrutinizer-ci.com/g/Okipa/laravel-medialibrary-extension/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Okipa/laravel-medialibrary-extension/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/Okipa/laravel-medialibrary-extension/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Okipa/laravel-medialibrary-extension/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Okipa/laravel-medialibrary-extension/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Okipa/laravel-medialibrary-extension/?branch=master)

This package provide extra features for the [spatie/laravel-medialibrary](https://github.com/spatie/laravel-medialibrary) package.  
Find the base package documentation here : https://docs.spatie.be/laravel-medialibrary/v7.  

## Installation

You can install the package via composer :

```bash
composer require okipa/laravel-medialibrary-extension:^7.0.0
```

The extension package will automatically install the [spatie/laravel-medialibrary](https://github.com/spatie/laravel-medialibrary) if it has not already been installed on your project.  
This extension package will follow the `spatie` base package versions.

## Extra features

### Mime types constraint
Addition of the `acceptsMimeTypes(array $mimeTypes): MediaCollection` which can be used with a media collection.  
Once declared, the mime types constraints will be used to trigger the `FileUnacceptableForCollection` exception if not respected, and also used to generate validation constraints and legends (see bellow).
```php
public function registerMediaCollections()
{
    $this->addMediaCollection('images')->acceptsFile(function (File $file) {
        return $file->size <= 30000;
    })->acceptsMimeTypes(['image/jpeg']);
}
```

### Collection validation string
Addition of the `validationConstraints(string $collectionName): string` method, which can be used with a model using the `HasMediaTrait`.  
```php
// in your user storing form request for example
public function rules()
{
    return [
        'avatar' => (new User)->validationConstraints('avatar'),
        // other validation rules
    ];
}
```
Rendering example : `dimensions:min_width=60,min_height=20|mimetypes:image/jpeg,image/png`.

### Collection legend
Addition of the `constraintsLegend(string $collectionName): string` method, which can be used with a model using the `HasMediaTrait`.
```html
// in your HTML form
<label for="avatar">Choose a profile picture :</label>
<input type=" id="avatar" name="avatar" value="{{ $avatarFileName }}">
<small>{{ (new User)->constraintsLegend('avatar') }}</small>
```
Rendering example : `Min. width : 150 px / Min. height : 70 px. Accepted MIME Type(s) : image/jpeg, image/png.`

### Other public methods
The following methods can also be used separately on a model using the `HasMediaTrait` :
- `collectionDimensionsLegend($collectionName): string`
- `collectionMimeTypesLegend($collectionName): string`
- `collectionMaxSizes(string $collectionName = 'default'): array`

## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Arthur LORENT](https://github.com/okipa)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.