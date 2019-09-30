<?php

namespace Mitto\Login\Model;

use Exception;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mitto\Login\Api\CustomerPhoneRepositoryInterface;
use Mitto\Login\Api\Data\CustomerPhoneInterface;
use Mitto\Login\Model\ResourceModel\CustomerPhone as ObjectResourceModel;
use Mitto\Login\Model\ResourceModel\CustomerPhone\CollectionFactory;

/**
 * Class CustomerPhoneRepository
 * @package Mitto\Login\Model
 */
class CustomerPhoneRepository implements CustomerPhoneRepositoryInterface
{
    protected $objectFactory;
    protected $objectResourceModel;
    protected $collectionFactory;
    protected $searchResultsFactory;

    /**
     * CustomerPhoneRepository constructor.
     * @param \Mitto\Login\Model\CustomerPhoneFactory $objectFactory
     * @param ObjectResourceModel $objectResourceModel
     * @param CollectionFactory $collectionFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        CustomerPhoneFactory $objectFactory,
        ObjectResourceModel $objectResourceModel,
        CollectionFactory $collectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory
    )
    {
        $this->objectFactory = $objectFactory;
        $this->objectResourceModel = $objectResourceModel;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @param CustomerPhoneInterface $object
     * @return CustomerPhoneInterface
     * @throws CouldNotSaveException
     */
    public function save(CustomerPhoneInterface $object)
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
     * @return CustomerPhone
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

    public function isPhoneUsed($phone)
    {
        try {
            $this->getByPhone($phone);
            return true;
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }


    /**
     * @param $phone
     * @return CustomerPhoneInterface|CustomerPhone
     * @throws NoSuchEntityException
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
     * @param CustomerPhoneInterface $object
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(CustomerPhoneInterface $object)
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
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }

    /**
     * @param SearchCriteriaInterface $criteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $collection = $this->collectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $fields[] = $filter->getField();
                $conditions[] = [$condition => $filter->getValue()];
            }
            if ($fields) {
                $collection->addFieldToFilter($fields, $conditions);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $objects = [];
        foreach ($collection as $objectModel) {
            $objects[] = $objectModel;
        }
        $searchResults->setItems($objects);
        return $searchResults;
    }

    /**
     * @return CustomerPhoneInterface|CustomerPhone
     */
    public function create()
    {
        return $this->objectFactory->create();
    }
}
