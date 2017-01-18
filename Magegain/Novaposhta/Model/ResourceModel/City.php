<?php

namespace Magegain\Novaposhta\Model\ResourceModel;

class City extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {

    protected function _construct() {
        $this->_init('novaposhta_cities', 'id');
    }

}
