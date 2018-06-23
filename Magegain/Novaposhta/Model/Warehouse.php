<?php

namespace Magegain\Novaposhta\Model;

use Magegain\Novaposhta\Api\Data\WarehouseInterface;

class Warehouse extends \Magento\Framework\Model\AbstractExtensibleModel implements WarehouseInterface
{

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magegain\Novaposhta\Model\ResourceModel\Warehouse');
    }

    public function getCustomAttributesCodes()
    {
        return array('id', 'warehouse_name', 'warehouse_name_ru', 'city_name');
    }
    public function getId()
    {
        return $this->_getData('id');
    }
    public function setCityId($city_id)
    {
        return $this->setData('city_id', $city_id);
    }

    public function getCityId()
    {
        return $this->_getData('city_id');
    }

    public function setName($name)
    {
        return $this->setData('warehouse_name', $name);
    }

    public function getName()
    {
        return $this->_getData('warehouse_name');
    }

    public function setNameRu($warehouse_name_ru)
    {
        return $this->setData('warehouse_name_ru', $warehouse_name_ru);
    }

    public function getNameRu()
    {
        return $this->_getData('warehouse_name_ru');
    }

    public function setRef($ref)
    {
        return $this->setData('ref', $ref);
    }

    public function getRef()
    {
        return $this->_getData('ref');
    }
}
