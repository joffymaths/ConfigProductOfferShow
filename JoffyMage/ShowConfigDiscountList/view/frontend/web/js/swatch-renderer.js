define(
    ['jquery'],
    function ($) {
    'use strict';
    return function (widget) {
        $.widget('mage.SwatchRenderer', widget, {
            _RenderControls: function () {
                this._super();
            },
            _OnClick: function ($this, $widget) {
                var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                    $wrapper = $this.parents('.' + $widget.options.classes.attributeOptionsWrapper),
                    $label = $parent.find('.' + $widget.options.classes.attributeSelectedOptionLabelClass),
                    attributeId = $parent.data('attribute-id'),
                    $input = $parent.find('.' + $widget.options.classes.attributeInput),
                    checkAdditionalData = JSON.parse(this.options.jsonSwatchConfig[attributeId]['additional_data']),
                    $priceBox = $widget.element.parents($widget.options.selectorProduct)
                        .find(this.options.selectorProductPrice);

                if ($widget.inProductList) {
                    $input = $widget.productForm.find(
                        '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                    );
                }

                if ($this.hasClass('disabled')) {
                    return;
                }

                if ($this.hasClass('selected')) {
                    $parent.removeAttr('data-option-selected').find('.selected').removeClass('selected');
                    $input.val('');
                    $label.text('');
                    $this.attr('aria-checked', false);
                } else {
                    $parent.attr('data-option-selected', $this.data('option-id')).find('.selected').removeClass('selected');
                    $label.text($this.data('option-label'));
                    $input.val($this.data('option-id'));
                    $input.attr('data-attr-name', this._getAttributeCodeById(attributeId));
                    $this.addClass('selected');
                    $widget._toggleCheckedAttributes($this, $wrapper);

                    /* CUSTOM CODE START */
                    if (jQuery('[data-ui-id="page-discount-wrapper"]').length && this.options.jsonConfig.discount) {
                        if (this.getProduct()) {
                            var idiscount = this.options.jsonConfig.discount[this.getProduct()][0].discount
                            if (idiscount != '') {
                                jQuery('[data-ui-id="page-discount-wrapper"]').html(idiscount);
                            }
                        }
                        else {
                            var productDiscount = this.options.jsonConfig.productDiscount
                            if (productDiscount) {
                                jQuery('[data-ui-id="page-discount-wrapper"]').html(productDiscount);
                            }
                        }
                    }
                    /* CUSTOM CODE END */

                }

                $widget._Rebuild();

                if ($priceBox.is(':data(mage-priceBox)')) {
                    $widget._UpdatePrice();
                }

                $(document).trigger('updateMsrpPriceBlock',
                    [
                        this._getSelectedOptionPriceIndex(),
                        $widget.options.jsonConfig.optionPrices,
                        $priceBox
                    ]);

                if (parseInt(checkAdditionalData['update_product_preview_image'], 10) === 1) {
                    $widget._loadMedia();
                }

                $input.trigger('change');

            },
            _OnChange: function ($this, $widget) {
                var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                    attributeId = $parent.data('attribute-id'),
                    $input = $parent.find('.' + $widget.options.classes.attributeInput);

                if ($widget.productForm.length > 0) {
                    $input = $widget.productForm.find(
                        '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                    );
                }

                if ($this.val() > 0) {
                    $parent.attr('data-option-selected', $this.val());
                    $input.val($this.val());
                    /* CUSTOM CODE START */
                    if (jQuery('[data-ui-id="page-discount-wrapper"]').length && this.options.jsonConfig.discount) {
                        if (this.getProduct()) {
                            var idiscount = this.options.jsonConfig.discount[this.getProduct()][0].discount
                            if (idiscount != '') {
                                jQuery('[data-ui-id="page-discount-wrapper"]').html(idiscount);
                            }
                        }
                        else {
                            var productDiscount = this.options.jsonConfig.productDiscount
                            if (productDiscount) {
                                jQuery('[data-ui-id="page-discount-wrapper"]').html(productDiscount);
                            }
                        }
                    }
                    /* CUSTOM CODE END */
                } else {
                    $parent.removeAttr('data-option-selected');
                    $input.val('');
                }

                $widget._Rebuild();
                $widget._UpdatePrice();
                $widget._loadMedia();
                $input.trigger('change');
            },
        });
        return $.mage.SwatchRenderer;
    };
});
