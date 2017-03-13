<?php
/**
 * ApplicationInterface.php
 */
namespace Pails;

/**
 * Interface ApplicationInterface
 *
 * @package Pails
 */
interface ApplicationInterface
{
    /**
     * @return mixed
     */
    public function init();

    /**
     * @return mixed
     */
    public function boot();

    /**
     * @return mixed
     */
    public function handle();
}
