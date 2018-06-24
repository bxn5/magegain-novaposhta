<?php

namespace Magegain\Novaposhta\Api\Data;

interface WarehouseSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * @api
     * @return \Magegain\Novaposhta\Api\Data\CityInterface[]
     */
    public function getItems();

    /**
     * @api
     * @param \Magegain\Novaposhta\Api\Data\CityInterface[] $items
     * @return $this
     */
    public function setItems(array $items = null);
}
