<?php

namespace Mitto\Login\Api\Data;

/**
 * @method getGeneratedAt()
 * @method getCode()
 */
interface OtpInterface
{
    public function generate();

    /**
     * @param $code
     * @return mixed
     */
    public function checkCode($code);

    /**
     * @param $verificationToken
     * @return mixed
     */
    public function checkVerificationToken($verificationToken);

    public function getVerificationToken();

    public function invalidateVerificationToken();
}
