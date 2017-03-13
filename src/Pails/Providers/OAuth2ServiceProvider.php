<?php
namespace Pails\Providers;

use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\ImplicitGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\ResourceServer;
use Pails\OAuth2\Repositories\AccessTokenRepository;
use Pails\OAuth2\Repositories\AuthCodeRepository;
use Pails\OAuth2\Repositories\ClientRepository;
use Pails\OAuth2\Repositories\RefreshTokenRepository;
use Pails\OAuth2\Repositories\ScopeRepository;
use Pails\OAuth2\Repositories\UserRepository;

class OAuth2ServiceProvider extends AbstractServiceProvider
{
    /**
     * Register the application services.
     */
    public function register()
    {
        $this->di->setShared(
            'authServer',

            function () {
                // Init repositories
                $clientRepository = new ClientRepository();
                $scopeRepository = new ScopeRepository();
                $accessTokenRepository = new AccessTokenRepository();
                $authCodeRepository = new AuthCodeRepository();
                $userRepository = new UserRepository();
                $refreshTokenRepository = new RefreshTokenRepository();

                // Keys
                $privateKey = $this['config']->get('oauth2.private_key');
                $publicKey = $this['config']->get('oauth2.public_key');

                // Setup authorization server
                $server = new AuthorizationServer(
                    $clientRepository,
                    $accessTokenRepository,
                    $scopeRepository,
                    $privateKey,
                    $publicKey);

                if ($this['config']->get('oauth2.client_credential.enabled')) {
                    // clientGrant
                    $server->enableGrantType(
                        new ClientCredentialsGrant(),
                        seconds2interval($this['config']->get('oauth2.client_credential.token_ttl', 3600))
                    );
                }


                if ($this['config']->get('oauth2.password.enabled')) {
                    // passwordGrant
                    $passwordGrant = new PasswordGrant(
                        $userRepository,
                        $refreshTokenRepository
                    );
                    $passwordGrant->setRefreshTokenTTL(seconds2interval($this['config']->get('oauth2.password.refresh_token_ttl', 2592000)));
                    $server->enableGrantType(
                        $passwordGrant,
                        seconds2interval($this['config']->get('oauth2.password.token_ttl', 3600))
                    );
                }

                if ($this['config']->get('oauth2.auth_code.enabled')) {
                    // authCodeGrant
                    $authCodeGrant = new AuthCodeGrant(
                        $authCodeRepository,
                        $refreshTokenRepository,
                        seconds2interval($this['config']->get('oauth2.auth_code.code_ttl', 3600))
                    );
                    $authCodeGrant->setRefreshTokenTTL(seconds2interval($this['config']->get('oauth2.auth_code.refresh_token_ttl', 2592000)));
                    $server->enableGrantType(
                        $authCodeGrant,
                        seconds2interval($this['config']->get('oauth2.auth_code.token_ttl', 3600))
                    );
                }

                if ($this['config']->get('oauth2.refresh_token.enabled')) {
                    // refreshTokenGrant
                    $refreshTokenGrant = new RefreshTokenGrant(
                        $refreshTokenRepository
                    );
                    $server->enableGrantType(
                        $refreshTokenGrant,
                        seconds2interval($this['config']->get('oauth2.refresh_token.token_ttl', 3600))
                    );
                }

                if ($this['config']->get('oauth2.implicit.enabled')) {
                    // implicit grant
                    $server->enableGrantType(
                        new ImplicitGrant(seconds2interval($this['config']->get('oauth2.implicit.token_ttl', 3600))),
                        seconds2interval($this['config']->get('oauth2.implicit.token_ttl', 3600))
                    );
                }

                return $server;
            }
        );

        $this->di->setShared(
            'resourceServer',

            function () {
                $accessTokenRepository = new AccessTokenRepository();
                $publicKey = $this['config']->get('oauth2.public_key');

                $resourceServer = new ResourceServer($accessTokenRepository, $publicKey);

                return $resourceServer;
            }
        );

        $this->di->setShared(
            'authClient',

            function () {
                $provider = new GenericProvider([
                    'clientId'                => $this['config']->get('oauth2.client.client_id'),
                    'clientSecret'            => $this['config']->get('oauth2.client.client_secret'),
                    'redirectUri'             => $this['config']->get('oauth2.client.redirect_url'),
                    'urlAuthorize'            => $this['config']->get('oauth2.client.authorize_url'),
                    'urlAccessToken'          => $this['config']->get('oauth2.client.access_token_url'),
                    'urlResourceOwnerDetails' => $this['config']->get('oauth2.client.resource_owner_url'),
                ]);

                return $provider;
            }
        );
    }
}
