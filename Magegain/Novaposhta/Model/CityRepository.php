<?php

/**
 * THIS IS THE REPOSITORY
 */

namespace Magegain\Novaposhta\Model;

use Magento\Framework\Api\Search\FilterGroup;
use Magegain\Novaposhta\Api\CityRepositoryInterface;
use Magegain\Novaposhta\Model\ResourceModel\City as CityResource;
use Magegain\Novaposhta\Model\ResourceModel\City\Collection;
use Magegain\Novaposhta\Model\ResourceModel\City\CollectionFactory;
use Magegain\Novaposhta\Api\Data\CitySearchResultsInterfaceFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magegain\Novaposhta\Api\Data\CityInterfaceFactory as CityDataFactory;

class CityRepository implements CityRepositoryInterface
{

    /**
     * @var customResource
     */
    private $cityResource;

    /**
     * @var customFactory
     */
    private $cityFactory;

    /**
     * @var CollectionFactory
     */
    private $cityDataFactory;
    private $collectionFactory;

    /**
     * @var CustomSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    public function __construct(
        CityResource $cityResource, CityFactory $cityFactory, CollectionFactory $collectionFactory, CitySearchResultsInterfaceFactory $searchResultsFactory, Collection $citycollection, CityDataFactory $cityDataFactory
    )
    {
        $this->cityResource = $cityResource;
        $this->cityFactory = $cityFactory;
        $this->collectionFactory = $collectionFactory;
        $this->citycollection = $citycollection;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->cityDataFactory = $cityDataFactory;
    }

    /**
     * @param \Magegain\Novaposta\Api\Data\CityInterface $city
     * @return int
     */
    public function save(\Magegain\Novaposhta\Api\Data\CityInterface $city)
    {
        $this->cityResource->save($city);
        return $city->getId();
    }

    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var ExampleCollection $collection */
        $collection = $this->cityFactory->create()->getCollection();
        /** @var ExampleSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $this->applySearchCriteriaToCollection($searchCriteria, $collection);
        $cities = $this->convertCollectionToDataItemsArray($collection);
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($cities);
        return $searchResults;
    }

    private function convertCollectionToDataItemsArray(
        Collection $collection
    )
    {
        $examples = array_map(function (City $city) {
            /** @var ExampleInterface $dataObject */
            $dataObject = $this->cityDataFactory->create();
            $dataObject->setId($city->getId());
            $dataObject->setCityId($city->getCityId());
            $dataObject->setCityName($city->getCityName());
            $dataObject->setCityNameRu($city->getCityNameRu());
            $dataObject->setRef($city->getRef());
            return $dataObject;
        }, $collection->getItems());
        return $examples;
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
