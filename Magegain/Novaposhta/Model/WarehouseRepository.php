<?php

/**
 * THIS IS THE REPOSITORY
 */

namespace Magegain\Novaposhta\Model;

use Magento\Framework\Api\Search\FilterGroup;
use Magegain\Novaposhta\Api\WarehouseRepositoryInterface;
use Magegain\Novaposhta\Model\ResourceModel\Warehouse as WarehouseResource;
use Magegain\Novaposhta\Model\WarehouseFactory;
use Magegain\Novaposhta\Model\ResourceModel\Warehouse\CollectionFactory;
use Magegain\Novaposhta\Model\ResourceModel\Warehouse\Collection;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magegain\Novaposhta\Api\Data\WarehouseInterfaceFactory as WarehouseDataFactory;

class WarehouseRepository implements WarehouseRepositoryInterface
{

    /**
     * @var WarehouseResource
     */
    private $warehouseResource;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CustomSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;
    private $warehouseDataFactory;
    private $warehouseFactory;

    public function __construct(
        WarehouseResource $warehouseResource,
        CollectionFactory $collectionFactory,
        WarehouseDataFactory $warehouseDataFactory,
        \Magegain\Novaposhta\Api\Data\WarehouseSearchResultsInterfaceFactory $searchResultsFactory,
        WarehouseFactory $warehouseFactory
    ) {
    
        $this->warehouseResource = $warehouseResource;
        $this->collectionFactory = $collectionFactory;
        $this->warehouseDataFactory = $warehouseDataFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->warehouseFactory = $warehouseFactory;
    }

    public function save(\Magegain\Novaposhta\Api\Data\WarehouseInterface $warehouse)
    {
        $this->warehouseResource->save($warehouse);
        return $warehouse->getId();
    }

    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var ExampleCollection $collection */
        $collection = $this->warehouseFactory->create()->getCollection();
        /** @var ExampleSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $this->applySearchCriteriaToCollection($searchCriteria, $collection);
        $warehouses = $this->convertCollectionToDataItemsArray($collection);
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($warehouses);
        return $searchResults;
    }

    private function convertCollectionToDataItemsArray(
        Collection $collection
    ) {
    
        $examples = array_map(function (Warehouse $warehouse) {
            /** @var ExampleInterface $dataObject */
            $dataObject = $this->warehouseDataFactory->create();
            $dataObject->setId($warehouse->getId());
            $dataObject->setCityId($warehouse->getCityId());
            $dataObject->setName($warehouse->getName());
            $dataObject->setNameRu($warehouse->getNameRu());
            $dataObject->setRef($warehouse->getRef());
            return $dataObject;
        }, $collection->getItems());
        return $examples;
    }

    private function applySearchCriteriaToCollection(
        SearchCriteriaInterface $searchCriteria,
        Collection $collection
    ) {
    
        $this->applySearchCriteriaFiltersToCollection(
            $searchCriteria,
            $collection
        );
        $this->applySearchCriteriaSortOrdersToCollection(
            $searchCriteria,
            $collection
        );
        $this->applySearchCriteriaPagingToCollection(
            $searchCriteria,
            $collection
        );
    }

    private function applySearchCriteriaFiltersToCollection(
        SearchCriteriaInterface $searchCriteria,
        Collection $collection
    ) {
    
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
    }

    private function addFilterGroupToCollection(
        FilterGroup $filterGroup,
        Collection $collection
    ) {
    
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?
                $filter->getConditionType() :
                'eq';
            $fields[] = $filter->getField();
            $conditions[] = [$condition => $filter->getValue()];
        }
        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
    }

    private function applySearchCriteriaSortOrdersToCollection(
        SearchCriteriaInterface $searchCriteria,
        Collection $collection
    ) {
    
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            $isAscending = $sortOrders->getDirection() == SearchCriteriaInterface::SORT_ASC;
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    $isAscending ? 'ASC' : 'DESC'
                );
            }
        }
    }

    private function applySearchCriteriaPagingToCollection(
        SearchCriteriaInterface $searchCriteria,
        Collection $collection
    ) {
    
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
    }
    public function getWarehouseByCity($postcode, $city)
    {
        return ['df','fd'];
    }
}
