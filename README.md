# SceneId Provider for OAuth 2.0 Client
[![Latest Version](https://img.shields.io/github/release/potibm/oauth2-sceneid.svg?style=flat-square)](https://github.com/potibm/oauth2-sceneid/releases)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/potibm/oauth2-sceneid?style=flat-square)](https://packagist.org/packages/potibm/oauth2-sceneid)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Coverage Status](https://img.shields.io/codecov/c/github/potibm/oauth2-sceneid?style=flat-square)](https://app.codecov.io/gh/potibm/oauth2-sceneid)


This package provides [SceneId](https://id.scene.org) OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## Installation

To install, use composer:

```
composer require potibm/oauth2-sceneid
```

## Usage

Usage is the same as The League's OAuth client, using `potibm\SceneIdOauth2\SceneIdProvider` as the provider.

### Authorization Code Flow

```php
$provider = new potibm\SceneIdOauth2\SceneIdProvider([
    'clientId'          => '{sceneid-client-id}',
    'clientSecret'      => '{sceneid-client-secret}',
    'redirectUri'       => 'https://example.com/callback-url',
]);
```

## License

The MIT License (MIT). Please see [License File](https://github.com/thephpleague/oauth2-github/blob/master/LICENSE) for more information.
