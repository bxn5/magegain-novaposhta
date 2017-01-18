<?php

namespace Magegain\Novaposhta\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface CityRepositoryInterface {

    public function save(\Magegain\Novaposhta\Api\Data\CityInterface $request);

    public function getList(SearchCriteriaInterface $searchCriteria);
}
