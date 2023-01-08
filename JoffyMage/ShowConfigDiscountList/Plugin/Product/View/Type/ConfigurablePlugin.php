<?php

namespace JoffyMage\ShowConfigDiscountList\Plugin\Product\View\Type;

class ConfigurablePlugin
{
    protected $jsonEncoder;
    protected $jsonDecoder;
    protected $discountHelper;

    public function __construct(
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \JoffyMage\ShowConfigDiscountList\Helper\Data $discountHelper
    ) {
        $this->jsonEncoder = $jsonEncoder;
        $this->jsonDecoder = $jsonDecoder;
        $this->discountHelper = $discountHelper;
    }

    public function afterGetJsonConfig(
        \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject,
        $result
    ) {
        $result = $this->jsonDecoder->decode($result);
        $currentProduct = $subject->getProduct();
        $module_status = 1;
        if ($module_status == 1) {
            $result['productDiscount'] = 0;
            foreach ($subject->getAllowProducts() as $product) {
                // get discount calculation
                $basePrice = number_format($product->getPrice());
                $specialPrice = number_format($product->getFinalPrice());
                if ($basePrice != $specialPrice) {
                    $discount = $this->discountHelper->getDiscountPersentage($basePrice, $specialPrice);
                    $discount = $discount."%";
                } else {
                    $discount = "";
                }
                $result['discount'][$product->getId()][] =
                    [
                        'discount' => $discount,
                    ];
            }
        }
        return $this->jsonEncoder->encode($result);
    }
}
