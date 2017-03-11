<?php
/**
 * RefreshTokenEntity.php
 *
 */
namespace Pails\OAuth2\Entities;

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\RefreshTokenTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

class RefreshTokenEntity implements RefreshTokenEntityInterface
{
    use EntityTrait, TokenEntityTrait, RefreshTokenTrait;
}
