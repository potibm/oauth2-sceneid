<?php

declare(strict_types=1);

namespace potibm\SceneIdOauth2\Test;

use GuzzleHttp\ClientInterface;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Tool\QueryBuilderTrait;
use PHPUnit\Framework\TestCase;
use potibm\SceneIdOauth2\SceneIdProvider;
use potibm\SceneIdOauth2\SceneIdUser;
use Psr\Http\Message\ResponseInterface;

class SceneIdProviderTest extends TestCase
{
    use QueryBuilderTrait;

    protected SceneIdProvider $provider;

    public function setUp(): void
    {
        $this->provider = new SceneIdProvider([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'https://example.com/callback-url',
        ]);
    }

    public function testAuthorizationUrl(): void
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('approval_prompt', $query);
        $this->assertNotNull($this->provider->getState());
    }

    /*
    public function testScopes(): void
    {
        $scopeSeparator = ',';
        $options = ['scope' => [uniqid(), uniqid()]];
        $query = ['scope' => implode($scopeSeparator, $options['scope'])];
        $url = $this->provider->getAuthorizationUrl($options);
        $encodedScope = $this->buildQueryString($query);

        $this->assertStringContainsString($encodedScope, $url);
    }*/

    public function testGetAuthorizationUrl(): void
    {
        $url = $this->provider->getAuthorizationUrl();
        $this->assertStringStartsWith('https://id.scene.org/oauth/authorize/?', $url);
    }

    public function testGetBaseAccessTokenUrl(): void
    {
        $url = $this->provider->getBaseAccessTokenUrl([]);
        $this->assertEquals('https://id.scene.org/oauth/token/', $url);
    }

    public function testGetAccessToken(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')
            ->willReturn('{"access_token":"mock_access_token", "scope":"repo,gist", "token_type":"bearer"}');
        $response->method('getBody')
            ->willReturn('{"access_token":"mock_access_token", "scope":"repo,gist", "token_type":"bearer"}');
        $response->method('getHeader')
            ->willReturn(['content-type' => 'json']);
        $response->method('getStatusCode')
            ->willReturn(200);

        $client = $this->createMock(ClientInterface::class);
        $client->method('send')->willReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertNull($token->getExpires());
        $this->assertNull($token->getRefreshToken());
        $this->assertNull($token->getResourceOwnerId());
    }

    public function testUserData(): void
    {
        $userId = rand(1000, 9999);
        $email = uniqid();
        $nickname = uniqid();
        $firstname = uniqid();
        $lastname = uniqid();

        $postResponse = $this->createMock(ResponseInterface::class);
        $postResponse->method('getBody')
            ->willReturn(http_build_query([
                'access_token' => 'mock_access_token',
                'expires' => 3600,
                'refresh_token' => 'mock_refresh_token',
            ]));
        $postResponse->method('getHeader')
            ->willReturn(['content-type' => 'application/x-www-form-urlencoded']);
        $postResponse->method('getStatusCode')
            ->willReturn(200);

        $userResponse = $this->createMock(ResponseInterface::class);
        $userResponse->method('getBody')
            ->willReturn(json_encode(['success' => true, 'user' => [
                "id" => $userId,
                "first_name" => $firstname,
                "last_name" => $lastname,
                "display_name" => $nickname,
                "email" => $email
            ]]));
        $userResponse->method('getHeader')
            ->willReturn(['content-type' => 'json']);
        $userResponse->method('getStatusCode')
            ->willReturn(200);

        $client = $this->createMock(ClientInterface::class);
        $client->method('send')
            ->willReturn($postResponse, $userResponse);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $user = $this->provider->getResourceOwner($token);

        $this->assertEquals($userId, $user->getId());
        $this->assertEquals($userId, $user->toArray()['id']);
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($email, $user->toArray()['email']);
        $this->assertEquals($firstname, $user->getFirstname());
        $this->assertEquals($firstname, $user->toArray()['firstname']);
        $this->assertEquals($lastname, $user->getLastName());
        $this->assertEquals($lastname, $user->toArray()['lastname']);
        $this->assertEquals($nickname, $user->getDisplayname());
        $this->assertEquals($nickname, $user->toArray()['displayname']);
    }

    public function testWithInvalidTokenResponse(): void
    {
        $this->expectException(IdentityProviderException::class);

        $postResponse = $this->createMock(ResponseInterface::class);
        $postResponse->method('getBody')
            ->willReturn(http_build_query([
                'error' => 'some error'
            ]));
        $postResponse->method('getHeader')
            ->willReturn(['content-type' => 'application/x-www-form-urlencoded']);
        $postResponse->method('getStatusCode')
            ->willReturn(200);


        $client = $this->createMock(ClientInterface::class);
        $client->method('send')
            ->willReturn($postResponse);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    public function testWithInvalidUserResponse(): void
    {
        $this->expectException(IdentityProviderException::class);

        $postResponse = $this->createMock(ResponseInterface::class);
        $postResponse->method('getBody')
            ->willReturn(http_build_query([
                'access_token' => 'mock_access_token',
                'expires' => 3600,
                'refresh_token' => 'mock_refresh_token',
            ]));
        $postResponse->method('getHeader')
            ->willReturn(['content-type' => 'application/x-www-form-urlencoded']);
        $postResponse->method('getStatusCode')
            ->willReturn(200);

        $userResponse = $this->createMock(ResponseInterface::class);
        $userResponse->method('getBody')
            ->willReturn(json_encode(['success' => false]));
        $userResponse->method('getHeader')
            ->willReturn(['content-type' => 'json']);
        $userResponse->method('getStatusCode')
            ->willReturn(200);

        $client = $this->createMock(ClientInterface::class);
        $client->method('send')
            ->willReturn($postResponse, $userResponse);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $user = $this->provider->getResourceOwner($token);
    }
}
