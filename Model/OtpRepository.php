<?php

namespace Mitto\Login\Model;

use Exception;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mitto\Login\Api\Data\OtpInterface;
use Mitto\Login\Api\OtpRepositoryInterface;
use Mitto\Login\Model\ResourceModel\Otp\CollectionFactory;

/**
 * Class OtpRepository
 * @package Mitto\Login\Model
 */
class OtpRepository implements OtpRepositoryInterface
{
    /**
     * @var OtpFactory
     */
    private $objectFactory;
    /**
     * @var ResourceModel\Otp
     */
    private $objectResourceModel;
    /**
     * @var ResourceModel\Otp\CollectionFactory
     */
    private $collectionFactory;

    /**
     * OtpRepository constructor.
     * @param OtpFactory $objectFactory
     * @param ResourceModel\Otp $objectResourceModel
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        OtpFactory $objectFactory,
        \Mitto\Login\Model\ResourceModel\Otp $objectResourceModel,
        CollectionFactory $collectionFactory
    ) {
        $this->objectFactory = $objectFactory;
        $this->objectResourceModel = $objectResourceModel;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param OtpInterface $object
     * @return OtpInterface
     * @throws CouldNotSaveException
     */
    public function save(OtpInterface $object)
    {
        try {
            $this->objectResourceModel->save($object);
        } catch (Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }
        return $object;
    }

    /**
     * @param $id
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }

    /**
     * @param OtpInterface $object
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(OtpInterface $object)
    {
        try {
            $this->objectResourceModel->delete($object);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @param $id
     * @return Otp
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $object = $this->objectFactory->create();
        $this->objectResourceModel->load($object, $id);
        if (!$object->getId()) {
            throw new NoSuchEntityException(__('Object with id "%1" does not exist.', $id));
        }
        return $object;
    }

    /**
     * @param string $phone
     * @return OtpInterface
     */
    public function getByPhone($phone)
    {
        $object = $this->objectFactory->create();
        $this->objectResourceModel->load($object, $phone, 'phone');
        if (!$object->getId()) {
            throw new NoSuchEntityException(__('Object with phone "%1" does not exist.', $phone));
        }
        return $object;
    }

    /**
     * @param $phone
     * @return OtpInterface|Otp
     */
    public function getByPhoneOrCreate($phone)
    {
        try {
            return $this->getByPhone($phone);
        } catch (NoSuchEntityException $e) {
            return $this->objectFactory->create(['data' => ['phone' => $phone]]);
        }
    }
}
