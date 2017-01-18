<?php

/**
 * THIS IS THE REPOSITORY
 */

namespace Magegain\Novaposhta\Model;

use Magento\Framework\Api\Search\FilterGroup;
use Magegain\Novaposhta\Api\WarhouseRepositoryInterface;
use Magegain\Novaposhta\Model\ResourceModel\Warhouse as WarhouseResource;
use Magegain\Novaposhta\Model\WarhouseFactory;
use Magegain\Novaposhta\Model\ResourceModel\Warhouse\CollectionFactory;
use Magegain\Novaposhta\Model\ResourceModel\Warhouse\Collection;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magegain\Novaposhta\Api\Data\WarhouseInterfaceFactory as WarhouseDataFactory;

class WarhouseRepository implements WarhouseRepositoryInterface
{

    /**
     * @var customResource
     */
    private $warhouseResource;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CustomSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;
    private $warhouseDataFactory;
    private $warhouseFactory;

    public function __construct(
        WarhouseResource $warhouseResource, CollectionFactory $collectionFactory, WarhouseDataFactory $warhouseDataFactory, \Magegain\Novaposhta\Api\Data\WarhouseSearchResultsInterfaceFactory $searchResultsFactory, WarhouseFactory $warhouseFactory
    )
    {
        $this->warhouseResource = $warhouseResource;
        $this->collectionFactory = $collectionFactory;
        $this->warhouseDataFactory = $warhouseDataFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->warhouseFactory = $warhouseFactory;
    }

    public function save(\Magegain\Novaposhta\Api\Data\WarhouseInterface $warhouse)
    {
        $this->warhouseResource->save($warhouse);
        return $warhouse->getId();
    }

    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var ExampleCollection $collection */
        $collection = $this->warhouseFactory->create()->getCollection();
        /** @var ExampleSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $this->applySearchCriteriaToCollection($searchCriteria, $collection);
        $warhouses = $this->convertCollectionToDataItemsArray($collection);
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($warhouses);
        return $searchResults;
    }

    private function convertCollectionToDataItemsArray(
        Collection $collection
    )
    {
        $examples = array_map(function (Warhouse $warhouse) {
            /** @var ExampleInterface $dataObject */
            $dataObject = $this->warhouseDataFactory->create();
            $dataObject->setId($warhouse->getId());
            $dataObject->setCityId($warhouse->getCityId());
            $dataObject->setName($warhouse->getName());
            $dataObject->setNameRu($warhouse->getNameRu());
            $dataObject->setRef($warhouse->getRef());
            return $dataObject;
        }, $collection->getItems());
        return $examples;
    }

    private function applySearchCriteriaToCollection(
        SearchCriteriaInterface $searchCriteria, Collection $collection
    )
    {
        $this->applySearchCriteriaFiltersToCollection(
            $searchCriteria, $collection
        );
        $this->applySearchCriteriaSortOrdersToCollection(
            $searchCriteria, $collection
        );
        $this->applySearchCriteriaPagingToCollection(
            $searchCriteria, $collection
        );
    }

    private function applySearchCriteriaFiltersToCollection(
        SearchCriteriaInterface $searchCriteria, Collection $collection
    )
    {
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
    }

    private function addFilterGroupToCollection(
        FilterGroup $filterGroup, Collection $collection
    )
    {
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
        SearchCriteriaInterface $searchCriteria, Collection $collection
    )
    {
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            $isAscending = $sortOrders->getDirection() == SearchCriteriaInterface::SORT_ASC;
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(), $isAscending ? 'ASC' : 'DESC'
                );
            }
        }
    }

    private function applySearchCriteriaPagingToCollection(
        SearchCriteriaInterface $searchCriteria, Collection $collection
    )
    {
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
    }

}
