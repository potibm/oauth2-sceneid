<?php

declare(strict_types=1);

namespace potibm\SceneIdOauth2;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class SceneIdProvider extends AbstractProvider
{
    public const ENDPOINT_TOKEN = 'https://id.scene.org/oauth/token/';

    public const ENDPOINT_AUTH = 'https://id.scene.org/oauth/authorize/';

    public const ENDPOINT_RESOURCE = 'https://id.scene.org/api/3.0';

    public function getBaseAuthorizationUrl(): string
    {
        return self::ENDPOINT_AUTH;
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return self::ENDPOINT_TOKEN;
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        $fields = [
            'id',
            'first_name',
            'last_name',
            'display_name',
            'email',
        ];

        return self::ENDPOINT_RESOURCE . '/me?fields=' . implode(',', $fields)
            . '&access_token=' . $token;
    }

    protected function getScopeSeparator(): string
    {
        return ' ';
    }

    protected function getDefaultScopes(): array
    {
        return ['basic', 'user:email'];
    }

    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if (! empty($data['error'])) {
            throw new IdentityProviderException($data['error'], 500, $data);
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token): ResourceOwnerInterface
    {
        if (
            !key_exists('success', $response) ||
            $response['success'] !== true ||
            ! key_exists('user', $response) ||
            ! is_array($response['user'])
        ) {
            throw new IdentityProviderException('Failure in response', 500, $response);
        }
        return new SceneIdUser($response['user']);
    }
}
