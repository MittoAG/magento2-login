<?php

namespace Mitto\Login\Controller\Otp;

use Exception;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Mitto\Login\Api\CustomerPhoneRepositoryInterface;
use Mitto\Login\Api\OtpRepositoryInterface;

/**
 * Class Submit
 * @package Mitto\Login\Controller\Otp
 */
class Submit extends Action
{
    /**
     * @var OtpRepositoryInterface
     */
    private $otpRepository;
    /**
     * @var CustomerPhoneRepositoryInterface
     */
    private $customerPhoneRepository;
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * Submit constructor.
     * @param Context $context
     * @param CustomerPhoneRepositoryInterface $customerPhoneRepository
     * @param OtpRepositoryInterface $otpRepository
     * @param Session $customerSession
     */
    public function __construct(
        Context $context,
        CustomerPhoneRepositoryInterface $customerPhoneRepository,
        OtpRepositoryInterface $otpRepository,
        Session $customerSession
    ) {
        parent::__construct($context);
        $this->otpRepository = $otpRepository;
        $this->customerPhoneRepository = $customerPhoneRepository;
        $this->customerSession = $customerSession;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $phone = $this->getRequest()->getParam('phone');
            $verificationToken = $this->getRequest()->getParam('verification_token');
            $otp = $this->otpRepository->getByPhone($phone);
            $status = $otp->checkVerificationToken($verificationToken);
            if ($status) {
                $otp->invalidateVerificationToken();
                $customerPhone = $this->customerPhoneRepository->getByPhone($phone);
                $customerId = $customerPhone->getCustomerId();
                $this->customerSession->loginById($customerId);
                return $this->_redirect('customer/account');
            }
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }
        return $this->_redirect('customer/account/login');
    }
}
