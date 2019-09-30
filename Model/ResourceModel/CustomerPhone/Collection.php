<?php

namespace Mitto\Login\Model\ResourceModel\CustomerPhone;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Mitto\Login\Model\CustomerPhone;

/**
 * Class Collection
 * @package Mitto\Login\Model\ResourceModel\CustomerPhone
 */
class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(CustomerPhone::class, \Mitto\Login\Model\ResourceModel\CustomerPhone::class);
    }
}
