<?php
/**
 * UserRepository.php
 *
 */
namespace Pails\OAuth2\Repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

/**
 * Class UserRepository
 * @package Pails\OAuth2\Repositories
 */
class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    )
    {
        return $this->storageService->getUserEntityByUserCredentials($username, $password, $grantType, $clientEntity);
    }
}
