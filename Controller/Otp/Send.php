<?php

namespace Mitto\Login\Controller\Otp;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Mitto\Core\Model\Renderer;
use Mitto\Core\Model\Sender;
use Mitto\Login\Api\CustomerPhoneRepositoryInterface;
use Mitto\Login\Api\OtpRepositoryInterface;

/**
 * Class Send
 * @package Mitto\Login\Controller\Otp
 */
class Send extends Action
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
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Send constructor.
     * @param Context $context
     * @param CustomerPhoneRepositoryInterface $customerPhoneRepository
     * @param OtpRepositoryInterface $otpRepository
     * @param Sender $sender
     * @param Renderer $renderer
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        CustomerPhoneRepositoryInterface $customerPhoneRepository,
        OtpRepositoryInterface $otpRepository,
        Sender $sender,
        Renderer $renderer,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->otpRepository = $otpRepository;
        $this->customerPhoneRepository = $customerPhoneRepository;
        $this->sender = $sender;
        $this->renderer = $renderer;
        $this->scopeConfig = $scopeConfig;
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
            if(!$this->customerPhoneRepository->isPhoneUsed($phone)){
                throw new LocalizedException(__('There is no account associated with this phone number'));
            }
            $otp = $this->otpRepository->getByPhoneOrCreate($phone);
            $code = $otp->generate();
            $response = $this->sender->send(
                $phone,
                $this->renderer->render(
                    $this->scopeConfig->getValue(
                        'mitto_login/templates/customer_login_code',
                        ScopeInterface::SCOPE_STORE
                    ),
                    [
                        'code' => $code,
                    ]
                )
            );
            $result->setData([
                'success'   => $response['responseCode'] == 0,
                'generated' => $otp->getGeneratedAt(),
            ]);
        } catch (Exception $e) {
            $result->setStatusHeader(500)->setData(['error' => $e->getMessage()]);
        }
        return $result;
    }
}
