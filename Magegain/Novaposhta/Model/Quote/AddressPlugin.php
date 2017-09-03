<?php

namespace Magegain\Novaposhta\Model\Quote;

class AddressPlugin
{
    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        $shippingAddress = $addressInformation->getShippingAddress();
        $ext =  $shippingAddress->getExtensionAttributes();
        $shippingAddress->setCarrierDepartment($ext->getCarrierDepartment());
    }
}
