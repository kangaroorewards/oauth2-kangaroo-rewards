<?php

/**
 * @license http://opensource.org/licenses/MIT MIT
 * @link http://thephpleague.com/oauth2-client/ Documentation
 * @link https://packagist.org/packages/league/oauth2-client Packagist
 * @link https://github.com/thephpleague/oauth2-client GitHub
 */

namespace KangarooRewards\OAuth2\Client\Grant;

use League\OAuth2\Client\Grant\AbstractGrant;

/**
 * Represents a Google grant.
 *
 * @link http://tools.ietf.org/html/rfc6749#section-1.3.3 Resource Owner Password Credentials (RFC 6749, §1.3.3)
 */
class Google extends AbstractGrant
{
    /**
     * @inheritdoc
     */
    protected function getName()
    {
        return 'google';
    }

    /**
     * @inheritdoc
     */
    protected function getRequiredRequestParameters()
    {
        return [
            'username',
            'google_token',
        ];
    }
}
