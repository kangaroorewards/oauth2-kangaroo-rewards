<?php

namespace KangarooRewards\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use KangarooRewards\OAuth2\Client\Provider\KangarooResourceOwner;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Kangaroo extends AbstractProvider
{
    use BearerAuthorizationTrait;

    const AUTHORIZATION_URI = 'http://api.kangaroorewards.local/oauth/authorize';
    const API_URI = 'http://api.kangaroorewards.local';
    const ACCESS_TOKEN_URI = 'http://api.kangaroorewards.local/oauth/token';

    /**
     * @var mixed
     */
    protected $businessId;

    /**
     * Default scopes
     *
     * @var array
     */
    public $defaultScopes = ['manage-all'];

    /**
     * @param array $options
     * @param array $collaborators
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($options = [], array $collaborators = [])
    {
        parent::__construct($options, $collaborators);

        if (empty($options['businessId'])) {
            $message = 'The "businessId" option not set. Please set Business ID.';
            throw new \InvalidArgumentException($message);
        }

        $this->businessId = (int) $options['businessId'];
    }

    public function getBaseAuthorizationUrl()
    {
        return static::AUTHORIZATION_URI;
    }

    /**
     * @param array $params
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return self::ACCESS_TOKEN_URI;
    }

    /**
     * @param AccessToken $token
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->getBaseApiUrl() . '/me';
    }

    /**
     * Get the default scopes used by this provider.
     *
     * This should not be a complete list of all scopes, but the minimum
     * required for the provider user interface!
     *
     * @return array
     */
    public function getDefaultScopes()
    {
        return $this->defaultScopes;
    }
    
    /**
     * Generate a Resource Owner object from a successful Resource Owner details request.
     *
     * @param array $response
     * @param AccessToken $token
     * @return League\OAuth2\Client\Provider\ResourceOwnerInterface
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new KangarooResourceOwner($response);
    }

    /**
     * Returns all options that are required.
     *
     * @return array
     */
    protected function getRequiredOptions()
    {
        return [
            'urlAuthorize',
            'urlAccessToken',
            'businessId',
        ];
    }

    /**
     * Check a provider response for errors.
     *
     * @throws IdentityProviderException
     * @param  ResponseInterface $response
     * @param  string $data Parsed response data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        $acceptableStatuses = [200, 201];
        if (!in_array($response->getStatusCode(), $acceptableStatuses)) {
            throw new IdentityProviderException(
                $data['message'] ?: $response->getReasonPhrase(),
                $response->getStatusCode(),
                $response
            );
        }
        // if (!empty($data['error'])) {
        //     $message = $data['error'];
        //     $message = isset($data['message']) ? $data['message'] : '';
        //     throw new \Exception($message);
        // }
    }

    /**
     * Get the Kangaroo api URL.
     *
     * @return string
     */
    protected function getBaseApiUrl()
    {
        return self::API_URI;
    }

    /**
     * Set the business Id
     *
     * @param int $businessId
     */
    protected function setBusinessId($businessId)
    {
        $this->businessId = $businessId;
    }

    /**
     * Get the business ID
     *
     * @return int
     */
    protected function getBusinessId()
    {
        return (int) $this->businessId;
    }

    /**
     * Verifies that all required options have been passed.
     *
     * @param  array $options
     * @return void
     * @throws InvalidArgumentException
     */
    private function assertRequiredOptions(array $options)
    {
        $missing = array_diff_key(array_flip($this->getRequiredOptions()), $options);
        if (!empty($missing)) {
            throw new InvalidArgumentException(
                'Required options not defined: ' . implode(', ', array_keys($missing))
            );
        }
    }
}
