<?php

namespace Mitto\Login\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Mitto\Login\Api\Data\OtpInterface;
use Mitto\Login\Helper\Config;
use Mitto\Login\Model\ResourceModel\Otp\Collection;

/**
 * @method ResourceModel\Otp getResource()
 * @method Collection getCollection()
 * @method getGeneratedAt()
 * @method getCode()
 */
class Otp extends AbstractModel implements
    OtpInterface,
    IdentityInterface
{
    const CACHE_TAG = 'mitto_login_otp';
    const VERIFICATION_TOKEN = 'verification_token';
    protected $_cacheTag = 'mitto_login_otp';
    protected $_eventPrefix = 'mitto_login_otp';
    /**
     * @var Config
     */
    private $config;
    /**
     * @var DateTime
     */
    private $date;
    /**
     * @var Random
     */
    private $mathRandom;

    /**
     * Otp constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Config $config
     * @param Random $mathRandom
     * @param DateTime $date
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Config $config,
        Random $mathRandom,
        DateTime $date,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->config = $config;
        $this->date = $date;
        $this->mathRandom = $mathRandom;
    }

    protected function _construct()
    {
        $this->_init(ResourceModel\Otp::class);
    }

    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return bool
     */
    public function checkGenerationTimestamp()
    {
        return $this->date->gmtTimestamp() - strtotime($this->getGeneratedAt()) < 60;
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    public function generate()
    {
        if ($this->checkGenerationTimestamp()) {
            throw new LocalizedException(__('Previous code did not expire, please wait'));
        }
        $code = $this->_generateOTPCode();
        $this->setData('code', $code);
        $this->setData('generated_at', $this->date->gmtTimestamp());
        $this->setData(self::VERIFICATION_TOKEN, $this->_generateVerificationToken());
        $this->save();
        $this->load($this->getId());
        return $code;
    }

    /**
     * @param $code
     * @return bool|mixed
     * @throws LocalizedException
     */
    public function checkCode($code)
    {
        if ($this->checkGenerationTimestamp() && $this->getCode() === $code) {
            return true;
        }
        $this->generate();
        return false;
    }

    /**
     * @param $verificationToken
     * @return bool|mixed
     * @throws LocalizedException
     */
    public function checkVerificationToken($verificationToken)
    {
        return $this->getVerificationToken() === $verificationToken;
    }

    /**
     * @return mixed
     * @throws LocalizedException
     */
    public function getVerificationToken()
    {
        if (!$this->hasData(self::VERIFICATION_TOKEN)) {
            $this->setData(self::VERIFICATION_TOKEN, $this->_generateVerificationToken());
            $this->save();
        }
        return $this->getData(self::VERIFICATION_TOKEN);
    }

    public function invalidateVerificationToken()
    {
        $this->setData(self::VERIFICATION_TOKEN, null)->save();
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    protected function _generateVerificationToken()
    {
        return $this->mathRandom->getUniqueHash();
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    protected function _generateOTPCode()
    {
        $length = $this->config->getCodeLength();
        return $this->mathRandom->getRandomNumber(
            str_repeat('9', $length - 1),
            str_repeat('9', $length)
        );
    }
}
