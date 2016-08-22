if (!Itoris) {
    var Itoris = {};
}

Itoris.GroupedProductPromotions = Class.create({
    templates : {},
    initialize : function(templates, dateFormat, idCurrentProduct, rulesData, subProductData, idSubProduct, lastRuleIdDb, storeId) {
        this.templates = templates;
        this.dateFormat = dateFormat;
        this.storeId = storeId;
        this.idCurrentProduct = idCurrentProduct;
        this.idSubProduct = idSubProduct;
        this.addEvents(subProductData);
        this.ruleId = 0;//lastRuleIdDb;
        if (rulesData.length) {
            for (var i = 0; i < rulesData.length; i++) {
                this.createRule(subProductData, rulesData[i]);
            }
        }
        var curObj = this;
        var productTimer  = null;
        productTimer = new PeriodicalExecuter(function() {
            curObj.saveProductButton(productTimer);
        }, 0.2);
    },
    addEvents: function(subProductData) {
        Event.observe($$('.itoris_groupedproductpromotions_button_create_rule')[0], 'click', this.createRule.bind(this, subProductData));
    },
    choosePriceMethod : function(block) {
        if (block.select('.itoris_grouped_promotions_price_method')[0].value == 1) {
            block.select('.itoris_grouped_discount_method').each(function(elm) {
                elm.show();
            });
            block.select('.itoris_grouped_fixed_price_method')[0].hide();
            this.discountFields(block, true);
        } else if (block.select('.itoris_grouped_promotions_price_method')[0].value == 2) {
            block.select('.itoris_grouped_discount_method').each(function(elm) {
                elm.hide();
            });
            block.select('.itoris_grouped_fixed_price_method')[0].show();
            this.discountFields(block, true);
        } else {
            block.select('.itoris_grouped_discount_method').each(function(elm) {
                elm.hide();
            });
            block.select('.itoris_grouped_fixed_price_method')[0].hide();
            this.discountFields(block, false);
        }
    },
    discountFields : function(block, hide) {
        if (hide) {
            block.select('.itoris_groupedproductpromotions_product_discount').each(function(el) {
                el.up('td').hide();
            });
            block.select('.itoris_groupedproductpromotions_product_type').each(function(el) {
                el.up('td').hide();
            });
            block.select('.discount')[0].hide();
            block.select('.discount')[1].hide();
        } else {
            block.select('.itoris_groupedproductpromotions_product_discount').each(function(el) {
                el.up('td').show();
            });
            block.select('.itoris_groupedproductpromotions_product_type').each(function(el) {
                el.up('td').show();
            });
            block.select('.discount')[0].show();
            block.select('.discount')[1].show();
        }
    },
    disabledFields : function(ruleBox) {
        if (this.storeId) {
            var ruleData = ruleBox.select('table')[0].select('tbody tr')[0].select('td');
            if (ruleBox.select('.itoris_groupedpromotions_store')[0].checked) {
                ruleBox.select('table input,table select').each(function(elm){elm.disabled=true;});
            } else {
                ruleBox.select('table input,table select').each(function(elm){elm.disabled=false;});
                ruleBox.select('.itoris_groupedproductpromotions_product_checkbox').each(function(elm){this.unDisabledFields(elm);}.bind(this));
            }
        } else {
            ruleBox.select('.store_box')[0].hide();
        }
    },
    saveProductButton : function(t) {
        if ($$('.form-buttons')[0].select('.save')[0] && $$('.form-buttons')[0].select('.save')[1]) {
            t.stop();
            Event.observe($$('.form-buttons')[0].select('.save')[0], 'click', this.validateProducts.bind(this, $$('.form-buttons')[0].select('.save')[0]));
            Event.observe($$('.form-buttons')[0].select('.save')[1], 'click', this.validateProducts.bind(this, $$('.form-buttons')[0].select('.save')[1]));
        }
    },
    validateProducts : function(b) {
        for (var i = 0; i < $$('.itoris_groupedproductpromotions_rule_box').length; i++) {
            var ruleBox = $$('.itoris_groupedproductpromotions_rule_box')[i];
            var countChecked = 0;
            ruleBox.select('.itoris_groupedproductpromotions_product_checkbox').each(function(elm){
                if (elm.checked) {
                    countChecked++;
                }
            });
            if (countChecked >= 2) {
                ruleBox.select('.itoris_groupedproductpromotions_rule_hidden')[0].value = 1;
            } else {
                ruleBox.select('.itoris_groupedproductpromotions_rule_hidden')[0].value = null;
                alert('Please select at least 2 sub-products for promo set');
                break;
            }
        }
        b.click();
    },
    createRule: function(subProductData, rulesData) {
        var ruleIdDb = 0;
        var parentId = 0;
        if (rulesData.rule_id) {
            var ruleId = rulesData.rule_id;
        } else {
            this.ruleId++;
            var ruleId = this.ruleId;

        }

        if (this.storeId) {
            if (rulesData.rule_id && rulesData.store_id && rulesData.store_id == this.storeId) {
                ruleIdDb = ruleId;
                parentId = rulesData.parent_id;
            } else if (rulesData.rule_id) {
                parentId = ruleId;
            }
        } else {
            if (rulesData.rule_id) {
                ruleIdDb = ruleId;
            }
        }
        if ((ruleIdDb != 0 && (parentId == 0 || parentId != 0)) || (ruleIdDb == 0 && parentId == 0)) {
            var useDefaultValue = '';
        } else {
            var useDefaultValue = 'checked="checked"';
        }
        if (this.storeId && !rulesData.rule_id) {
            var useDefaultValue = '';
        }
        var templateData = {
            currentProductId : this.idCurrentProduct,
            title            : rulesData.title,
            rule_id          : ruleId,
            rule_id_db       : ruleIdDb,
            parent_id        : parentId,
            position         : rulesData.position ? rulesData.position : 0,
            use_default_value: useDefaultValue,
            fixed_price      : rulesData.fixed_price,
            discount_promoset: rulesData.discount_promoset
        };
        var t = new Template(this.templates.set_rules);
        var setBlock = document.createElement('div');
        Element.extend(setBlock);
        setBlock.addClassName('itoris_groupedproductpromotions_rule_box');
        setBlock.update(t.evaluate(templateData));
        if (!rulesData.rule_id) {
            setBlock.select('.note_default')[0].hide();
        }
        if (this.storeId && !rulesData.rule_id) {
            setBlock.select('.store_box')[0].hide();
        } else if (this.storeId) {
            setBlock.select('.note_default')[0].hide();
            setBlock.select('.note_for_store')[0].show();
        }
        if (parentId == 0 && ruleIdDb) {
            setBlock.select('.note_default')[0].show();
            setBlock.select('.note_for_store')[0].hide();
            setBlock.select('.store_box')[0].hide();
        }
        Event.observe(setBlock.select('.itoris_groupedpromotions_store')[0], 'click', this.disabledFields.bind(this, setBlock));
        if (rulesData.status) {
            var productStatus = rulesData.status;
            setBlock.select('.itoris_groupedproductpromotions_status')[0].value = productStatus;
        }
        if (rulesData.active_from) {
            setBlock.select('.itoris_groupedproductpromotions_active_from')[0].value = rulesData.active_from;
        }
        if (rulesData.active_to) {
            setBlock.select('.itoris_groupedproductpromotions_active_to')[0].value = rulesData.active_to;
        }
        if (rulesData.price_method && setBlock.select('.itoris_grouped_promotions_price_method')[0]) {
            setBlock.select('.itoris_grouped_promotions_price_method')[0].value = rulesData.price_method;
        }
        if (rulesData.code_promoset && setBlock.select('.itoris_grouped_discount_method')[1]) {
            setBlock.select('.itoris_grouped_discount_method')[1].value = rulesData.code_promoset;
        }
        if (rulesData.group_id) {
            var groupSelected = rulesData.group_id.split(',');
            var allValueGroup = setBlock.select('.itoris_groupedproductpromotions_group option');
            if (Prototype.Browser.IE) {
                allValueGroup[0].selected = false;
            } else {
                allValueGroup[0].removeAttribute('selected');
            }
            for (var  i = 0; i < groupSelected.length; i++) {
                for (var j = 0; j < allValueGroup.length; j++) {
                    if (groupSelected[i] == allValueGroup[j].value) {
                        allValueGroup[j].selected = true;
                    }
                }
            }
        }
        for (var i = 0; i < this.idSubProduct.length; i++) {
            var templateProduct = new Template(this.templates.sub_product);
            var tr = document.createElement('tr');
            Element.extend(tr);
            var productType = 0;
            if (rulesData.rule_id && subProductData[rulesData.rule_id] && subProductData[rulesData.rule_id][this.idSubProduct[i]]) {
                var productConfig = subProductData[rulesData.rule_id][this.idSubProduct[i]];
                if (productConfig.in_set) {
                    var checkedInSet = parseInt(productConfig.in_set) ? 'checked="checked"' : '';
                } else {
                    var checkedInSet = '';
                }
                if (productConfig.show_promoset) {
                    var showPromoset = parseInt(productConfig.show_promoset) ? 'checked="checked"' : '';
                } else {
                    var showPromoset = '';
                }
                if (productConfig.type) {
                    productType = parseInt(productConfig.type);
                }
                var templateProductData = {
                    qty                   : productConfig.qty ? productConfig.qty : 1,
                    discount              : productConfig.discount ? productConfig.discount : 0,
                    subProductName        : productConfig.name,
                    subProductId          : productConfig.product_id,
                    product_rule_id_db    : ruleIdDb && productConfig.rule_product_id ? productConfig.rule_product_id : 0,
                    rule_id               : ruleId,
                    checked_in_set        : checkedInSet,
                    checked_show_promoset : showPromoset
                };
            } else {
                var name = subProductData['name'][this.idSubProduct[i]];
                var templateProductData = {
                    qty                   : 1,
                    discount              : 0,
                    subProductName        : name,
                    subProductId          : this.idSubProduct[i],
                    rule_id               : ruleId,
                    checked_show_promoset : 'checked="checked"'
                };
            }
            tr.update(templateProduct.evaluate(templateProductData));
            setBlock.select('.itoris_groupedproductpromotions_associated_product')[0].appendChild(tr);
            tr.select('.itoris_groupedproductpromotions_product_type')[0].value = productType;
            this.validateDiscount(tr.select('.itoris_groupedproductpromotions_product_discount')[0], tr.select('.itoris_groupedproductpromotions_product_type')[0]);
            Event.observe(tr.select('.itoris_groupedproductpromotions_product_type')[0], 'change', this.validateDiscount.bind(this, tr.select('.itoris_groupedproductpromotions_product_discount')[0], tr.select('.itoris_groupedproductpromotions_product_type')[0]));
            this.unDisabledFields(tr.select('.itoris_groupedproductpromotions_product_checkbox')[0]);
            Event.observe(tr.select('.itoris_groupedproductpromotions_product_checkbox')[0], 'click', this.unDisabledFields.bind(this, tr.select('.itoris_groupedproductpromotions_product_checkbox')[0]));

        }
        var tdForGroups = document.createElement('td');
        Element.extend(tdForGroups);
        tdForGroups.rowSpan = 10;
        tdForGroups.addClassName('groups');
        if (setBlock.select('.itoris_groupedproductpromotions_associated_product')[0].select('tr')[2]) {
            setBlock.select('.itoris_groupedproductpromotions_associated_product')[0].select('tr')[2].appendChild(tdForGroups);
            tdForGroups.appendChild(setBlock.select('.itoris_groupedproductpromotions_group')[0]);
        }
        if ($$('.itoris_groupedproductpromotions_rule_box').length && $$('.itoris_groupedproductpromotions_rule_box')[0]) {
            $$('.itoris_groupedproductpromotions_content')[0].insertBefore(setBlock, $$('.itoris_groupedproductpromotions_rule_box')[0]);
        } else {
            $$('.itoris_groupedproductpromotions_content')[0].appendChild(setBlock);
        }
        Event.observe(setBlock.select('.itoris_groupedproductpromotions_button_delete')[0], 'click', this.deleteRule.bind(this, setBlock.select('.itoris_groupedproductpromotions_button_delete')[0]));
        $$('.itoris_groupedproductpromotions_content')[0].show();
        this.disabledFields(setBlock);
        if ($('itoris_groupedproductpromotions_active_from_' + ruleId)) {
            this.actionFromToCalendar(ruleId);
        }
        this.choosePriceMethod(setBlock);
        Event.observe(setBlock.select('.itoris_grouped_promotions_price_method')[0], 'click', this.choosePriceMethod.bind(this, setBlock));
    },
    validateDiscount: function(discountInput, typeElm) {
        if (typeElm.value == 1) {
            discountInput.addClassName('validate-number validate-number-range number-range-0-100');
        } else if (discountInput.hasClassName('number-range-0-100')) {
            discountInput.removeClassName('number-range-0-100');
        }
    },
    actionFromToCalendar: function(rule_id) {
        Calendar.setup({
            inputField: 'itoris_groupedproductpromotions_active_from_'+ rule_id,
            ifFormat: this.dateFormat,
            showsTime: false,
            button: 'icon_active_from_'+ rule_id,
            singleClick: true
        });
        Calendar.setup({
            inputField: 'itoris_groupedproductpromotions_active_to_' + rule_id,
            ifFormat: this.dateFormat,
            showsTime: false,
            button: 'icon_active_to_'+ rule_id,
            singleClick: true
        });
    },
    unDisabledFields: function(checkbox) {
        if (checkbox && checkbox.up('tr')) {
            var tr = checkbox.up('tr');
            if (checkbox.checked) {
                tr.select('.itoris_groupedproductpromotions_product_qty')[0].disabled = '';
                tr.select('.itoris_groupedproductpromotions_product_discount')[0].disabled = '';
                tr.select('.itoris_groupedproductpromotions_product_type')[0].disabled = '';
                tr.select('.itoris_groupedproductpromotions_product_show_promoset')[0].disabled = '';
            } else {
                tr.select('.itoris_groupedproductpromotions_product_qty')[0].disabled = 'disabled';
                tr.select('.itoris_groupedproductpromotions_product_discount')[0].disabled = 'disabled';
                tr.select('.itoris_groupedproductpromotions_product_type')[0].disabled = 'disabled';
                tr.select('.itoris_groupedproductpromotions_product_show_promoset')[0].disabled = 'disabled';
            }
        }
    },
    deleteRule: function(deleteButton) {
        if (confirm('Do you really want to remove the rule?')) {
            deleteButton.up('div').up().remove();
        }
    }
});