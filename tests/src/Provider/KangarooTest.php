<?php

namespace KangarooRewards\OAuth2\Client\Test\Provider;

use KangarooRewards\OAuth2\Client\Provider\Kangaroo;
use Mockery as m;

class KangarooTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Kangaroo
     */
    protected $provider;

    protected function setUp()
    {
        $this->provider = new Kangaroo([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'businessId' => 'mock_store_name',
            'redirectUri' => 'none',
        ]);
    }

    public function tearDown()
    {
        m::close();
        parent::tearDown();
    }

    public function testAuthorizationUrl()
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

    public function testGetBaseAccessTokenUrl()
    {
        $url = $this->provider->getBaseAccessTokenUrl([]);
        $uri = parse_url($url);

        $this->assertEquals('/oauth/token', $uri['path']);
    }

    public function testGetAccessToken()
    {
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getHeader')
            ->times(1)
            ->andReturn('application/json');

        $response->shouldReceive('getBody')
            ->times(1)
            ->andReturn('{"access_token":"mock_access_token","token_type":"bearer","expires_in":3600}');

        $response->shouldReceive('getStatusCode')->andReturn(200);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertLessThanOrEqual(time() + 3600, $token->getExpires());
        $this->assertGreaterThanOrEqual(time(), $token->getExpires());
    }

    /**
     * @expectedException League\OAuth2\Client\Grant\Exception\InvalidGrantException
     */
    public function testTryingToRefreshAnAccessTokenWillThrow()
    {
        $this->provider->getAccessToken('foo', ['refresh_token' => 'foo_token']);
    }

    public function testScopes()
    {
        $this->assertEquals(['manage-all'], $this->provider->getDefaultScopes());
    }

    public function testResourceOwner()
    {
        $email = 'ttuser125@kangarewards.com';
        $userId = rand(1000, 9999);

        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getHeader')
            ->times(1)
            ->andReturn('application/json');

        $response->shouldReceive('getBody')
            ->times(1)
            ->andReturn('{"access_token":"mock_access_token","token_type":"bearer","expires_in":3600}');

        $response->shouldReceive('getStatusCode')->andReturn(200);

        $userResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $userResponse->shouldReceive('getBody')->andReturn('{"data":{"id":' . $userId . ',"profile":{"user_id":223,"username":"ttuser125","email":"ttuser125@kangarewards.com","name":"Ali El Zein"},"business":{"id":95,"name":"Muscle Depot"}}}');
        $userResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $userResponse->shouldReceive('getStatusCode')->andReturn(200);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(2)
            ->andReturn($response, $userResponse);

        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $resourceOwner = $this->provider->getResourceOwner($token);

        $this->assertInstanceOf('KangarooRewards\OAuth2\Client\Provider\KangarooResourceOwner', $resourceOwner);
        $this->assertEquals($userId, $resourceOwner->getId());
        $this->assertEquals($email, $resourceOwner->getEmail());
    }
}
