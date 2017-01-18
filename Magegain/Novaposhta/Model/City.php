<?php

namespace Magegain\Novaposhta\Model;

use Magegain\Novaposhta\Api\Data\CityInterface;

class City extends \Magento\Framework\Model\AbstractExtensibleModel implements CityInterface {

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct() {
        $this->_init('Magegain\Novaposhta\Model\ResourceModel\City');
    }

    public function getCustomAttributesCodes() {
        return array('id', 'city_name', 'city_name_ru', 'areae');
    }

    public function setCityId($city_id) {
        return $this->setData('city_id', $city_id);
    }

    public function getCityId() {
        return $this->_getData('city_id');
    }

    public function setCityName($city_name) {
        return $this->setData('city_name', $city_name);
    }

    public function getCityName() {
        return $this->_getData('city_name');
    }

    public function setCityNameRu($city_name_ru) {
        return $this->setData('city_name_ru', $city_name_ru);
    }

    public function getCityNameRu() {
        return $this->_getData('city_name_ru');
    }

    public function setRef($ref) {
        return $this->setData('ref', $ref);
    }

    public function getRef() {
        return $this->_getData('ref');
    }

}
