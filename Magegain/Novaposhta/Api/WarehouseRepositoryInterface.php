<?php

namespace Magegain\Novaposhta\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface WarehouseRepositoryInterface
{


    public function save(Data\WarehouseInterface $request);

    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param $postcode
     * @param $city
     * @return mixed
     */
    public function getWarehouseByCity($postcode, $city);
}
