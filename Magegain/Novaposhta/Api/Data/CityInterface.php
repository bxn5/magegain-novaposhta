<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Magegain\Novaposhta\Api\Data;

interface CityInterface {

    public function setCityId($city_id);

    public function setCityName($city_name);

    public function setCityNameRu($city_name_ru);

    public function setRef($ref);
}
