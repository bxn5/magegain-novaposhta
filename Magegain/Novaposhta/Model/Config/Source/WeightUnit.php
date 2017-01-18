<?php

namespace Magegain\Novaposhta\Model\Config\Source;

class WeightUnit implements \Magento\Framework\Option\ArrayInterface {

    /**
     * {@inheritdoc}
     */
    public function toOptionArray() {
        return [
            [
                'value' => 'kg',
                'label' => 'Кілограми',
            ],
            [
                'value' => 'gr',
                'label' => 'Грами',
            ]
        ];
    }

}

?>