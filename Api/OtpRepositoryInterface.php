<?php

namespace Mitto\Login\Api;

use Mitto\Login\Api\Data\OtpInterface;

/**
 * Interface OtpRepositoryInterface
 * @package Mitto\Login\Api
 */
interface OtpRepositoryInterface
{
    /**
     * @param OtpInterface $address
     * @return mixed
     */
    public function save(OtpInterface $address);

    /**
     * @param string $phone
     * @return OtpInterface
     */
    public function getByPhone($phone);

    /**
     * @param string $phone
     * @return OtpInterface
     */
    public function getByPhoneOrCreate($phone);
}
