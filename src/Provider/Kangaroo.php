<?php

namespace KangarooRewards\OAuth2\Client\Provider;

use KangarooRewards\OAuth2\Client\Grant\CustomGrantFactory;
use KangarooRewards\OAuth2\Client\Provider\KangarooResourceOwner;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Kangaroo extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * @var string
     */
    protected $urlAuthorize = 'https://api.kangaroorewards.com/oauth/authorize';

    /**
     * @var string
     */
    protected $urlAccessToken = 'https://api.kangaroorewards.com/oauth/token';

    /**
     * @var string
     */
    protected $urlResourceOwnerDetails = 'https://api.kangaroorewards.com/me';

    /**
     * Default scopes
     *
     * @var array
     */
    public $defaultScopes = ['admin'];

    /**
     * @param array $options
     * @param array $collaborators
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($options = [], array $collaborators = [])
    {
        $collaborators['grantFactory'] = new CustomGrantFactory;
        parent::__construct($options, $collaborators);
    }

    public function getBaseAuthorizationUrl()
    {
        return $this->urlAuthorize;
    }

    /**
     * @param array $params
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->urlAccessToken;
    }

    /**
     * @param AccessToken $token
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->urlResourceOwnerDetails;
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
            'urlResourceOwnerDetails',
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
            if (isset($data['error']['description'])) {
                $message = $data['error']['description'];
            } elseif (isset($data['error']['message'])) {
                $message = $data['error']['message'];
            } elseif (isset($data['message'])) {
                $message = $data['message'];
            } else {
                $message = $response->getReasonPhrase();
            }

            throw new IdentityProviderException(
                $message,
                $response->getStatusCode(),
                $response
            );
        }
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
