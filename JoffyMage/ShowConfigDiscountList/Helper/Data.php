<?php

namespace JoffyMage\ShowConfigDiscountList\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    protected $_storeManager;
    protected $ruledatetime;
    protected $ruleFactory;
    protected $productdata;
    protected $registry;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $ruledatetime,
        \Magento\CatalogRule\Model\RuleFactory $ruleFactory,
        \Magento\Catalog\Model\ProductFactory $productdata,
        \Magento\Framework\Registry $registry
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->ruledatetime = $ruledatetime;
        $this->ruleFactory = $ruleFactory;
        $this->productdata = $productdata;
        $this->registry = $registry;
    }

    public function getDiscountPersentage($list_price, $sale_price)
    {
        $discount = 0;
        if ($list_price) {
            $discount = ($list_price - $sale_price) / $list_price;
            $discount = $discount * 100;
            $discount = round($discount, 2);
        }
        return $discount;
    }

    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    public function getStringArray($value)
    {
        return array_filter(explode(',', $value));
    }

    public function getDiscountData($id)
    {
        $dicount_data = $this->discountCollection->create();
        $dicount_data->addFieldToFilter('label_id', ['eq' => $id]);
        return $dicount_data;
    }

    public function getDiscount($prd, $group)
    {
        // get prodcut discount
        $discount = 0;
        // get website id
        $storeId = $prd->getStoreId();
        $store = $this->_storeManager->getStore($storeId);
        $websiteId = $store->getWebsiteId();
        // get rule date
        $rule_date = $this->ruledatetime->gmtDate();
        $rule = $this->ruleFactory->create();
        // for simple product and Group product  virtual
        if (($prd->getTypeId() == 'simple') ||
            ($prd->getTypeId() == 'downloadable') ||
            ($prd->getTypeId() == 'virtual')) {
            $discount = 0;
            // get product regular price
            $simple_regular_pice = $prd->getPrice();
            //get product final price
            $simple_final_pice = $prd->getFinalPrice();
            // check discount
            if ($simple_regular_pice != $simple_final_pice) {
                $discount = $this->getDiscountPersentage($simple_regular_pice, $simple_final_pice);
                return $discount;
            }
        }
        // for group product
        if ($prd->getTypeId() == 'grouped') {
            // initial discount
            $discount = 0;
            // initial group product price
            $group_regularPrice = $group_specialPrice = 0;
            $usedProds = $prd->getTypeInstance(true)->getAssociatedProducts($prd);
            foreach ($usedProds as $child) {
                if ($child->getId() != $prd->getId()) {
                    $group_regularPrice += $child->getPrice();
                    $group_specialPrice += $child->getFinalPrice();
                }
            }
            if ($group_regularPrice != $group_specialPrice) {
                $discount = $this->getDiscountPersentage($group_regularPrice, $group_specialPrice);
                return $discount;
            }
        } elseif ($prd->getTypeId() == 'configurable') {
            $basePrice = $prd->getPriceInfo()->getPrice('regular_price');
            $regularPrice = $basePrice->getMinRegularAmount()->getValue();
            $specialPrice = $prd->getFinalPrice();
            // initial discount
            $discount = 0;
            $children = $prd->getTypeInstance()->getUsedProducts($prd);
            $discount_arry = [];
            foreach ($children as $child) {
                $prddata = $this->getProductData($child->getEntityId());
                $r_prd = $prddata->getPrice();
                $s_prd = $prddata->getFinalPrice();
                $discount_config = $this->getDiscountPersentage($r_prd, $s_prd);
                if ($discount_config) {
                    $discount_arry[] = $discount_config;
                }
            }
            if (!empty($discount_arry)) {
                $discount = min($discount_arry);
            }
            return $discount;
        } elseif ($prd->getTypeId() == 'bundle') {
            $discount_bundel = [];
            $typeInstance = $prd->getTypeInstance();
            $requiredChildrenIds = $typeInstance->getChildrenIds($prd->getId(), false);
            $i = 0;
            foreach ($requiredChildrenIds as $Childrenkey => $Childrenvalue) {
                foreach ($Childrenvalue as $key => $value) {
                    $child = $this->getProductData($value);
                    $r_bprd = $child->getPrice();
                    $s_bprd = $child->getFinalPrice();
                    $discount_b = $this->getDiscountPersentage($r_bprd, $s_bprd);
                    if ($discount_b) {
                        $discount_bundel[] = $discount_b;
                    }
                }
                $i++;
            }
            if (!empty($discount_bundel)) {
                $discount = min($discount_bundel);
            }
        } else {
            $discount = 0;
        }
        return $discount;
    }

    public function getProductData($id)
    {
        return $this->productdata->create()->load($id);
    }
}
