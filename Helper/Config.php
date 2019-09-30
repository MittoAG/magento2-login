<?php

namespace Mitto\Login\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 * @package Mitto\Login\Helper
 */
class Config extends AbstractHelper
{
    /**
     * @param string $field
     * @return mixed
     */
    protected function getGeneralConfig($field)
    {
        return $this->scopeConfig->getValue(
            'mitto_login/general/' . $field,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)$this->getGeneralConfig('enabled');
    }

    /**
     * @return string
     */
    public function getEmailSuffix()
    {
        return $this->getGeneralConfig('email_suffix');
    }

    /**
     * @return mixed
     */
    public function getCodeLength()
    {
        return $this->getGeneralConfig('code_length');
    }
}
