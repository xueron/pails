<?php
/**
 * ScopeRepository.php
 *
 */
namespace Pails\OAuth2\Repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

/**
 * Class ScopeRepository
 * @package Pails\OAuth2\Repositories
 */
class ScopeRepository extends BaseRepository implements ScopeRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function getScopeEntityByIdentifier($identifier)
    {
        return $this->storageService->getScopeEntityByIdentifier($identifier);
    }

    /**
     * @inheritdoc
     */
    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $clientEntity,
        $userIdentifier = null
    )
    {
        return $this->storageService->finalizeScopes($scopes, $grantType, $clientEntity, $userIdentifier);
    }
}
