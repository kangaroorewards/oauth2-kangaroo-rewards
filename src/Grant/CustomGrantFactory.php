<?php
/**
 * This file is part of the league/oauth2-client library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Alex Bilbie <hello@alexbilbie.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @link http://thephpleague.com/oauth2-client/ Documentation
 * @link https://packagist.org/packages/league/oauth2-client Packagist
 * @link https://github.com/thephpleague/oauth2-client GitHub
 */

namespace KangarooRewards\OAuth2\Client\Grant;

use League\OAuth2\Client\Grant\Exception\InvalidGrantException;
use League\OAuth2\Client\Grant\GrantFactory;

/**
 * Represents a factory used when retrieving an authorization grant type.
 */
class CustomGrantFactory extends GrantFactory
{
    /**
     * Registers a default grant singleton by name.
     *
     * @param  string $name
     * @return self
     */
    protected function registerDefaultGrant($name)
    {
        // PascalCase the grant. E.g: 'authorization_code' becomes 'AuthorizationCode'
        $class = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $name)));

        if ($name == 'facebook') {
            $class = 'KangarooRewards\\OAuth2\\Client\\Grant\\' . $class;
        } else {
            $class = 'League\\OAuth2\\Client\\Grant\\' . $class;
        }

        $this->checkGrant($class);

        return $this->setGrant($name, new $class);
    }
}
