<?php

namespace Magegain\Novaposhta\Model;

use Magegain\Novaposhta\Api\Data\WarhouseInterface;

class Warhouse extends \Magento\Framework\Model\AbstractExtensibleModel implements WarhouseInterface
{

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magegain\Novaposhta\Model\ResourceModel\Warhouse');
    }

    public function getCustomAttributesCodes()
    {
        return array('id', 'warhouse_name', 'warhouse_name_ru', 'city_name');
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
        return $this->setData('warhouse_name', $name);
    }

    public function getName()
    {
        return $this->_getData('warhouse_name');
    }

    public function setNameRu($warhouse_name_ru)
    {
        return $this->setData('warhouse_name_ru', $warhouse_name_ru);
    }

    public function getNameRu()
    {
        return $this->_getData('warhouse_name_ru');
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
