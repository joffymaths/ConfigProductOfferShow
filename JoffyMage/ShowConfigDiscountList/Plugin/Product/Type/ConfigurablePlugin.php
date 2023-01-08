<?php

namespace JoffyMage\ShowConfigDiscountList\Plugin\Product\Type;

class ConfigurablePlugin
{
    public function afterGetUsedProductCollection(
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $subject,
        $result
    ) {
        $result->addAttributeToSelect('discount');
        return $result;
    }
}
