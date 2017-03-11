<?php
/**
 * AccessTokenRepository.php
 *
 */
namespace Pails\OAuth2\Repositories;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

/**
 * Class AccessTokenRepository
 * @package Pails\OAuth2\Repositories
 */
class AccessTokenRepository extends BaseRepository implements AccessTokenRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        return $this->storageService->getNewToken($clientEntity, $scopes, $userIdentifier);
    }

    /**
     * @inheritdoc
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        return $this->storageService->persistNewAccessToken($accessTokenEntity);
    }

    /**
     * @inheritdoc
     */
    public function revokeAccessToken($tokenId)
    {
        $this->storageService->revokeAccessToken($tokenId);
    }

    /**
     * @inheritdoc
     */
    public function isAccessTokenRevoked($tokenId)
    {
        $this->storageService->isAccessTokenRevoked($tokenId);
    }
}
