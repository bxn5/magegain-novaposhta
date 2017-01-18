<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Magegain\Novaposhta\Api\Data;

interface WarhouseInterface {

    public function setCityId($city_id);

    public function setName($warhouse_name);

    public function setNameRu($warhouse_name_ru);

    public function setRef($warhouse_name_ru);
}
