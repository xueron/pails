<?php
/**
 * ClientRepository.php
 */
namespace Pails\OAuth2\Repositories;

use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

/**
 * Class ClientRepository
 *
 * @package Pails\OAuth2\Repositories
 */
class ClientRepository extends BaseRepository implements ClientRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getClientEntity($clientIdentifier, $grantType, $clientSecret = null, $mustValidateSecret = true)
    {
        return $this->storageService->getClientEntity($clientIdentifier, $grantType, $clientSecret, $mustValidateSecret);
    }
}
