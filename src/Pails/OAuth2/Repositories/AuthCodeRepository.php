<?php
/**
 * AuthCodeRepository.php
 *
 */
namespace Pails\OAuth2\Repositories;

use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

/**
 * Class AuthCodeRepository
 * @package Pails\OAuth2\Repositories
 */
class AuthCodeRepository extends BaseRepository implements AuthCodeRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function getNewAuthCode()
    {
        return $this->storageService->getNewAuthCode();
    }

    /**
     * @inheritdoc
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        return $this->storageService->persistNewAuthCode($authCodeEntity);
    }

    /**
     * @inheritdoc
     */
    public function revokeAuthCode($codeId)
    {
        $this->storageService->revokeAuthCode($codeId);
    }

    /**
     * @inheritdoc
     */
    public function isAuthCodeRevoked($codeId)
    {
        $this->storageService->isAuthCodeRevoked($codeId);
    }
}
