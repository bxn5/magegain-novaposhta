<?php

namespace Magegain\Novaposhta\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface WarhouseRepositoryInterface {

    public function save(\Magegain\Novaposhta\Api\Data\WarhouseInterface $request);

    public function getList(SearchCriteriaInterface $searchCriteria);
}
