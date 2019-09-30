<?php

namespace Mitto\Login\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Mitto\Login\Api\Data\CustomerPhoneInterface;

/**
 * @method CustomerPhoneInterface setCustomerId(int $id)
 * @method CustomerPhoneInterface setPhone(string $phone)
 * @method CustomerPhoneInterface setIsVerified(bool $isVerified)
 */
class CustomerPhone extends AbstractModel implements CustomerPhoneInterface, IdentityInterface
{
    const CACHE_TAG = 'mitto_login_customer_phone';

    protected function _construct()
    {
        $this->_init(ResourceModel\CustomerPhone::class);
    }

    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
