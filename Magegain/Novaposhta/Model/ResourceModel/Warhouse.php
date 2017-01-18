<?php

namespace Magegain\Novaposhta\Model\ResourceModel;

class Warhouse extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {

    protected function _construct() {
        $this->_init('novaposhta_warhouse', 'id');
    }

}
