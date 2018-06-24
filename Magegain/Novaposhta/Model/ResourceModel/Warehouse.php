<?php

namespace Magegain\Novaposhta\Model\ResourceModel;

class Warehouse extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('novaposhta_warehouse', 'id');
    }
}
