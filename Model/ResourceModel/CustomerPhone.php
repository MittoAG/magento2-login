<?php

namespace Mitto\Login\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class CustomerPhone
 * @package Mitto\Login\Model\ResourceModel
 */
class CustomerPhone extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('mitto_login_customer_phone', 'id');
    }
}
