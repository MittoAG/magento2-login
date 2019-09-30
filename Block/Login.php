<?php


namespace Mitto\Login\Block;

use Magento\Framework\View\Element\Template;
use Mitto\Login\Helper\Config;

/**
 * Class Login
 * @package Mitto\Login\Block
 */
class Login extends Template
{
    /**
     * @var Config
     */
    private $configHelper;

    /**
     * Login constructor.
     * @param Template\Context $context
     * @param Config $configHelper
     * @param array $data
     */
    public function __construct(Template\Context $context, Config $configHelper, array $data = [])
    {
        parent::__construct($context, $data);
        $this->configHelper = $configHelper;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->configHelper->isEnabled();
    }
}
