<?php

namespace Mitto\Login\Controller\Otp;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Mitto\Core\Model\Renderer;
use Mitto\Core\Model\Sender;
use Mitto\Login\Api\CustomerPhoneRepositoryInterface;
use Mitto\Login\Api\OtpRepositoryInterface;

/**
 * Class Check
 * @package Mitto\Login\Controller\Otp
 */
class Check extends Action
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
     * @var Sender
     */
    private $sender;
    /**
     * @var Renderer
     */
    private $renderer;

    /**
     * Check constructor.
     * @param Context $context
     * @param CustomerPhoneRepositoryInterface $customerPhoneRepository
     * @param OtpRepositoryInterface $otpRepository
     * @param Sender $sender
     * @param Renderer $renderer
     */
    public function __construct(
        Context $context,
        CustomerPhoneRepositoryInterface $customerPhoneRepository,
        OtpRepositoryInterface $otpRepository,
        Sender $sender,
        Renderer $renderer
    ) {
        parent::__construct($context);
        $this->otpRepository = $otpRepository;
        $this->customerPhoneRepository = $customerPhoneRepository;
        $this->sender = $sender;
        $this->renderer = $renderer;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        try {
            $phone = $this->getRequest()->getParam('phone');
            $code = $this->getRequest()->getParam('code');
            $otp = $this->otpRepository->getByPhone($phone);
            $status = $otp->checkCode($code);
            if (!$status) {
                throw new LocalizedException(__('Code is invalid or has expired, please click on the resend button to obtain new one.'));
            }
            $result->setData([
                'status'             => $status,
                'verification_token' => $otp->getVerificationToken(),
            ]);
        } catch (Exception $e) {
            $result->setStatusHeader(500)->setData(['error' => $e->getMessage()]);
        }
        return $result;
    }
}
