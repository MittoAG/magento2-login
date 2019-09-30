<?php

namespace Mitto\Login\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Otp
 * @package Mitto\Login\Model\ResourceModel
 */
class Otp extends AbstractDb
{

    protected function _construct()
    {
        $this->_init('mitto_login_otp', 'id');
    }
}
