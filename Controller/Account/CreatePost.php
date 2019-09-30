<?php

namespace Mitto\Login\Controller\Account;

use Exception;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Helper\Address;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Customer\Model\Registration;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlFactory;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mitto\Login\Api\CustomerPhoneRepositoryInterface;
use Mitto\Login\Api\OtpRepositoryInterface;
use Mitto\Login\Helper\Config;

/**
 * Class CreatePost
 */
class CreatePost extends \Magento\Customer\Controller\Account\CreatePost
{
    /**
     * @var Validator
     */
    private $formKeyValidator;
    /**
     * @var AccountRedirect
     */
    private $accountRedirect;
    /**
     * @var Config
     */
    private $configHelper;
    /**
     * @var OtpRepositoryInterface
     */
    private $otpRepository;
    /**
     * @var CustomerPhoneRepositoryInterface
     */
    private $customerPhoneRepository;

    /**
     * CreatePost constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param AccountManagementInterface $accountManagement
     * @param Address $addressHelper
     * @param UrlFactory $urlFactory
     * @param FormFactory $formFactory
     * @param SubscriberFactory $subscriberFactory
     * @param RegionInterfaceFactory $regionDataFactory
     * @param AddressInterfaceFactory $addressDataFactory
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param CustomerUrl $customerUrl
     * @param Registration $registration
     * @param Escaper $escaper
     * @param CustomerExtractor $customerExtractor
     * @param DataObjectHelper $dataObjectHelper
     * @param AccountRedirect $accountRedirect
     * @param Config $configHelper
     * @param OtpRepositoryInterface $otpRepository
     * @param CustomerPhoneRepositoryInterface $customerPhoneRepository
     * @param Validator|null $formKeyValidator
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        AccountManagementInterface $accountManagement,
        Address $addressHelper,
        UrlFactory $urlFactory,
        FormFactory $formFactory,
        SubscriberFactory $subscriberFactory,
        RegionInterfaceFactory $regionDataFactory,
        AddressInterfaceFactory $addressDataFactory,
        CustomerInterfaceFactory $customerDataFactory,
        CustomerUrl $customerUrl,
        Registration $registration,
        Escaper $escaper,
        CustomerExtractor $customerExtractor,
        DataObjectHelper $dataObjectHelper,
        AccountRedirect $accountRedirect,
        Config $configHelper,
        OtpRepositoryInterface $otpRepository,
        CustomerPhoneRepositoryInterface $customerPhoneRepository,
        Validator $formKeyValidator = null
    ) {
        parent::__construct($context, $customerSession, $scopeConfig, $storeManager, $accountManagement, $addressHelper, $urlFactory, $formFactory, $subscriberFactory, $regionDataFactory, $addressDataFactory, $customerDataFactory, $customerUrl, $registration, $escaper, $customerExtractor, $dataObjectHelper, $accountRedirect, $formKeyValidator);
        $this->formKeyValidator = $formKeyValidator ?: ObjectManager::getInstance()->get(Validator::class);;
        $this->accountRedirect = $accountRedirect;
        $this->configHelper = $configHelper;
        $this->otpRepository = $otpRepository;
        $this->customerPhoneRepository = $customerPhoneRepository;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Forward|Redirect|void
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->session->isLoggedIn() || !$this->registration->isAllowed()) {
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        if (!$this->getRequest()->isPost()
            || !$this->formKeyValidator->validate($this->getRequest())
        ) {
            $url = $this->urlModel->getUrl('*/*/create', ['_secure' => true]);
            return $this->resultRedirectFactory->create()
                                               ->setUrl($this->_redirect->error($url));
        }

        try {
            $phone = $this->getRequest()->getParam('phone');

            $verificationToken = $this->getRequest()->getParam('verification_token');
            $otp = $this->otpRepository->getByPhone($phone);
            $status = $otp->checkVerificationToken($verificationToken);
            if (!$status) {
                throw new LocalizedException(__('Phone number is not confirmed'));
            }

            $this->session->regenerateId();
            $address = $this->extractAddress();
            $addresses = $address === null ? [] : [$address];

            $customer = $this->customerExtractor->extract('customer_account_create', $this->_request);
            $customer->setAddresses($addresses);

            if (!$customer->getEmail()) {
                $customer->setEmail($phone . $this->configHelper->getEmailSuffix());
            }
            $redirectUrl = $this->session->getBeforeAuthUrl();

            $customer = $this->accountManagement
                ->createAccount($customer, null, $redirectUrl);

            $this->_eventManager->dispatch(
                'customer_register_success',
                ['account_controller' => $this, 'customer' => $customer]
            );

            $customerPhone = $this->customerPhoneRepository->create();
            $customerPhone->setCustomerId($customer->getId())->setPhone($phone)->setIsVerified(true);
            $this->customerPhoneRepository->save($customerPhone);

            $this->session->setCustomerDataAsLoggedIn($customer);
            $this->messageManager->addSuccess($this->getSuccessMessage());
            $resultRedirect = $this->accountRedirect->getRedirect();

            return $resultRedirect;
        } catch (InputException $e) {
            $this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
            foreach ($e->getErrors() as $error) {
                $this->messageManager->addError($this->escaper->escapeHtml($error->getMessage()));
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
        } catch (Exception $e) {
            $this->messageManager->addException($e, __('We can\'t save the customer.'));
        }

        $this->session->setCustomerFormData($this->getRequest()->getPostValue());
        $defaultUrl = $this->urlModel->getUrl('*/*/create', ['_secure' => true]);
        return $resultRedirect->setUrl($this->_redirect->error($defaultUrl));
    }
}
