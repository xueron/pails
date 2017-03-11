<?php
/**
 * RefreshTokenRepository.php
 *
 */
namespace Pails\OAuth2\Repositories;

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

/**
 * Class RefreshTokenRepository
 * @package Pails\OAuth2\Repositories
 */
class RefreshTokenRepository extends BaseRepository implements RefreshTokenRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function getNewRefreshToken()
    {
        return $this->storageService->getNewRefreshToken();
    }

    /**
     * @inheritdoc
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        return $this->storageService->persistNewRefreshToken($refreshTokenEntity);
    }

    /**
     * @inheritdoc
     */
    public function revokeRefreshToken($tokenId)
    {
        $this->storageService->revokeRefreshToken($tokenId);
    }

    /**
     * @inheritdoc
     */
    public function isRefreshTokenRevoked($tokenId)
    {
        $this->storageService->isRefreshTokenRevoked($tokenId);
    }
}
