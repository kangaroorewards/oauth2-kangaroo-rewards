<?php

namespace KangarooRewards\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class KangarooResourceOwner implements ResourceOwnerInterface
{
    /**
     * Raw response
     *
     * @var array
     */
    protected $response;

    /**
     * Creates new resource owner.
     *
     * @param array  $response
     */
    public function __construct(array $response = array())
    {
        $this->response = $response;
    }

    /**
     * Get Account ID
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->response['data']['id'] ?: null;
    }

    /**
     * Get Account Name
     *
     * @return string|null
     */
    public function getName()
    {
        $profile = $this->response['data']['profile'] ?: null;
        return $profile ? $profile['first_name'] . ' ' . $profile['last_name'] : null;
    }

    /**
     * Get Account Email Address
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->response['data']['profile']['email'] ?: null;
    }

    /**
     * Get Business ID - for business owner only
     *
     * @return string|null
     */
    public function getBusinessId()
    {
        return $this->response['data']['business']['id'] ?: null;
    }

    /**
     * Get Business Name - for business owner only
     *
     * @return string|null
     */
    public function getBusinessName()
    {
        return $this->response['data']['business']['name'] ?: null;
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }
}
