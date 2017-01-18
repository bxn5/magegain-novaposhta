<?php

namespace Magegain\Novaposhta\Model\ResourceModel\Warhouse;

use Magento\Framework\Api\Search\SearchResultInterface;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection implements SearchResultInterface {

    protected $_idFieldName = 'main_table.id';

    protected function _construct() {
        $this->_init('Magegain\Novaposhta\Model\Warhouse', 'Magegain\Novaposhta\Model\ResourceModel\Warhouse');
    }

    /**
     * @return AggregationInterface
     */
    public function getAggregations() {
        return $this->aggregations;
    }

    /**
     * @param AggregationInterface $aggregations
     * @return $this
     */
    public function setAggregations($aggregations) {
        $this->aggregations = $aggregations;
    }

    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria() {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null) {
        return $this;
    }

    protected function _renderFiltersBefore() {
        $joinTable = $this->getTable('novaposhta_cities');
        $this->getSelect()->join($joinTable . ' as city', 'main_table.city_id = city.id', array('city.city_name'));
        parent::_renderFiltersBefore();
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount() {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount) {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setItems(array $items = null) {
        return $this;
    }

}
