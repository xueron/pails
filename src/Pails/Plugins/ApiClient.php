<?php
/**
 * ApiClient.php
 */
namespace Pails\Plugins;

use GuzzleHttp\Promise;
use League\OAuth2\Client\Token\AccessToken;
use Pails\Injectable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * Class ApiClient
 *
 * @package Pails\Plugins
 *
 * @method ResponseInterface get(string|UriInterface $uri, array $options = [])
 * @method ResponseInterface head(string|UriInterface $uri, array $options = [])
 * @method ResponseInterface put(string|UriInterface $uri, array $options = [])
 * @method ResponseInterface post(string|UriInterface $uri, array $options = [])
 * @method ResponseInterface patch(string|UriInterface $uri, array $options = [])
 * @method ResponseInterface delete(string|UriInterface $uri, array $options = [])
 * @method Promise\PromiseInterface getAsync(string|UriInterface $uri, array $options = [])
 * @method Promise\PromiseInterface headAsync(string|UriInterface $uri, array $options = [])
 * @method Promise\PromiseInterface putAsync(string|UriInterface $uri, array $options = [])
 * @method Promise\PromiseInterface postAsync(string|UriInterface $uri, array $options = [])
 * @method Promise\PromiseInterface patchAsync(string|UriInterface $uri, array $options = [])
 * @method Promise\PromiseInterface deleteAsync(string|UriInterface $uri, array $options = [])
 */
class ApiClient extends Injectable
{
    const SESSION_ACCESS_TOKEN_KEY = 'OAUTH_ACCESS_TOKEN';

    public function __call($method, $args)
    {
        if (count($args) < 1) {
            throw new \InvalidArgumentException('Magic request methods require a URI and optional options array');
        }

        $uri = $args[0];
        $opts = isset($args[1]) ? $args[1] : [];

        $res = substr($method, -5) === 'Async'
            ? $this->requestAsync(substr($method, 0, -5), $uri, $opts)
            : $this->request($method, $uri, $opts);

        return \GuzzleHttp\json_decode((string)$res->getBody());
    }

    /**
     * @param $method
     * @param $url
     * @param $options
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function request($method, $url, $options)
    {
        $request = $this->authClient->getAuthenticatedRequest($method, $url, $this->getAccessToken(), $options);
        return $this->httpClient->send($request);
    }

    /**
     * @param $method
     * @param $url
     * @param $options
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function requestAsync($method, $url, $options)
    {
        $request = $this->authClient->getAuthenticatedRequest($method, $url, $this->getAccessToken(), $options);
        return $this->httpClient->sendAsync($request);
    }

    /**
     * Get AccessToken from AS or session
     *
     * @return \League\OAuth2\Client\Token\AccessToken
     */
    protected function getAccessToken()
    {
        /** @var AccessToken $accessToken */
        if ($accessToken = $this->cache->get(static::SESSION_ACCESS_TOKEN_KEY)) {
            if (!$accessToken->hasExpired()) {
                return $accessToken;
            }
        }
        $accessToken = $this->authClient->getAccessToken('client_credentials');
        $ttl = $accessToken->getExpires() - time();
        $this->cache->save(static::SESSION_ACCESS_TOKEN_KEY, $accessToken, $ttl);
        return $accessToken;
    }
}
