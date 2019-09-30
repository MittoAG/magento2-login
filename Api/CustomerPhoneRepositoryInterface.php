<?php

namespace Mitto\Login\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Mitto\Login\Api\Data\CustomerPhoneInterface;

/**
 * Interface CustomerPhoneRepositoryInterface
 * @package Mitto\Login\Api
 */
interface CustomerPhoneRepositoryInterface
{
    /**
     * @param CustomerPhoneInterface $customerPhone
     * @return mixed
     */
    public function save(CustomerPhoneInterface $customerPhone);

    /**
     * @param $id
     * @return mixed
     */
    public function getById($id);

    /**
     * @param $phone
     * @return bool
     */
    public function isPhoneUsed($phone);

    /**
     * @param $phone
     * @return CustomerPhoneInterface
     * @throws NoSuchEntityException
     */
    public function getByPhone($phone);

    /**
     * @param SearchCriteriaInterface $criteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $criteria);

    /**
     * @param CustomerPhoneInterface $customerPhone
     * @return mixed
     */
    public function delete(CustomerPhoneInterface $customerPhone);

    /**
     * @param $id
     * @return mixed
     */
    public function deleteById($id);

    /**
     * @return CustomerPhoneInterface
     */
    public function create();
}
