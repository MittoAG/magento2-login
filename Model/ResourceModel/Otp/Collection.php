<?php

namespace Mitto\Login\Model\ResourceModel\Otp;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Mitto\Login\Model\Otp;

/**
 * Class Collection
 * @package Mitto\Login\Model\ResourceModel\Otp
 */
class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init(Otp::class, \Mitto\Login\Model\ResourceModel\Otp::class);
    }
}
