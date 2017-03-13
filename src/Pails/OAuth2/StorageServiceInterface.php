<?php
/**
 * StorageInterface.php
 */
namespace Pails\OAuth2;

use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

/**
 * Interface StorageServiceInterface
 *
 * @package Pails\OAuth2
 */
interface StorageServiceInterface extends
    AccessTokenRepositoryInterface,
    AuthCodeRepositoryInterface,
    ClientRepositoryInterface,
    RefreshTokenRepositoryInterface,
    ScopeRepositoryInterface,
    UserRepositoryInterface
{
}
