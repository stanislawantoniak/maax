<?php
/**
 * js for size table settings
 */
class Zolago_Sizetable_Block_Adminhtml_Vendor_Edit extends ZolagoOs_OmniChannel_Block_Adminhtml_Vendor_Edit {
      
    public function getFormScripts()
    {
        ob_start();
        $vendor = Mage::registry('vendor_data');

        $collection = Mage::helper('udropship')->getShippingMethods();
        $carriers = array();
        foreach ($collection as $s) {
            if (!$s->getSystemMethods()) {
                $carriers[$s->getId()] = array();
                continue;
            }
            foreach ($s->getSystemMethods() as $k=>$v) {
                $carriers[$s->getId()][$k] = $v;
            }
        }
?>
<script type="text/javascript">
var updater = new RegionUpdater('country_id', 'region', 'region_id', <?php echo $this->helper('directory')->getRegionJson() ?>, 'disable');
var bUpdater = new RegionUpdater('billing_country_id', 'billing_region', 'billing_region_id', <?php echo $this->helper('directory')->getRegionJson() ?>, 'disable');

var allowedCarriers = <?php echo Zend_Json::encode($carriers) ?>;

if (typeof connect_dhlJsObject != "undefined") {
    if (!$('dhl_vendor').value) {
        $('dhl_vendor').value = '{}';
    }
    var vendorDHL = $('dhl_vendor').value.evalJSON();

    function changeVendorDHLProperty() {
        if (!vendorDHL[this.DHLId]) {
            vendorDHL[this.DHLId] = {};
        }
        if (!this.name) {
            return;
        }
        var fname = this.name.replace(/^_/, '');
        vendorDHL[this.DHLId][fname] = this.value;
        highlightDHLRow(this);
        $('dhl_vendor').value = Object.toJSON(vendorDHL);
    }

    function highlightDHLRow(input, changed) {
        return; // disabled until done properly
        $(input).up('tr').select('td').each(function (el) {
            el.style.backgroundColor = changed || typeof changed=='undefined' ? '#ffb' : '';
        });
    }

    connect_dhlJsObject.initCallback = function(self) {
        self.initGridRows && self.initGridRows();
    }

    connect_dhlJsObject.initRowCallback = function(self, row) {
        var inputs = $(row).select('input', 'select'), id, selected, fname;
        for (var i=0; i<inputs.length; i++) {
            if (inputs[i].type=='checkbox' && inputs[i].name=='') {
                id = inputs[i].value;
                if (vendorDHL[id] && (typeof vendorDHL[id]['on'] !== 'undefined')) {
                    selected = vendorDHL[id]['on'];
                    inputs[i].checked = selected;
                    highlightDHLRow(inputs[i]);
                } else {
                    selected = inputs[i].checked;
                }
            } else {
                inputs[i].disabled = !selected;
                inputs[i].DHLId = id;
                fname = inputs[i].name.replace(/^_/, '');
                if (vendorDHL[id] && vendorDHL[id][fname]) {
                    inputs[i].value = vendorDHL[id][fname];
                }
                $(inputs[i]).observe('change', changeVendorDHLProperty);
            }
        }
    }

    connect_dhlJsObject.checkboxCheckCallback = function(grid, element, checked){
        $(element).up('tr').select('input', 'select').each(function (el) {
            if (el.type=='checkbox' && el.name=='') {
                if (!vendorDHL[el.value]) {
                    vendorDHL[el.value] = {};
                }
                vendorDHL[el.value]['on'] = checked;
                highlightDHLRow(element);
            } else {
                el.disabled = !checked;
            }
        });
        $('dhl_vendor').value = Object.toJSON(vendorDHL);
    }

    connect_dhlJsObject.rowClickCallback = function(grid, event){
        var trElement = Event.findElement(event, 'tr');
        var isInput   = Event.element(event).tagName.match(/(input|select|option)/i);
        if(trElement){
            var checkbox = Element.getElementsBySelector(trElement, 'input');
            if(checkbox[0]){
                var checked = isInput ? checkbox[0].checked : !checkbox[0].checked;
                connect_dhlJsObject.setCheckboxChecked(checkbox[0], checked);
            }
        }
    }
    connect_dhlJsObject.initGrid();

}

if (typeof connect_attributesetJsObject != "undefined") {
    if (!$('vendor_attributeset').value) {
        $('vendor_attributeset').value = '{}';
    }
    var vendorAttributeset = $('vendor_attributeset').value.evalJSON();

    function changeVendorAttributesetProperty() {
        if (!vendorAttributeset[this.attributesetId]) {
            vendorAttributeset[this.attributesetId] = {};
        }
        if (!this.name) {
            return;
        }
        var fname = this.name.replace(/^_/, '');
        vendorAttributeset[this.attributesetId][fname] = this.value;
        highlightAttributesetRow(this);

        $('vendor_attributeset').value = Object.toJSON(vendorAttributeset);
    }

    function highlightAttributesetRow(input, changed) {
        return; // disabled until done properly
        $(input).up('tr').select('td').each(function (el) {
            el.style.backgroundColor = changed || typeof changed=='undefined' ? '#ffb' : '';
        });
    }

    connect_attributesetJsObject.initCallback = function(self) {
        self.initGridRows && self.initGridRows();
    }

    connect_attributesetJsObject.initRowCallback = function(self, row) {
        var inputs = $(row).select('input', 'select'), id, selected, fname;
        for (var i=0; i<inputs.length; i++) {
            if (inputs[i].type=='checkbox' && inputs[i].name=='') {
                id = inputs[i].value;
                if (vendorAttributeset[id] && (typeof vendorAttributeset[id]['on'] !== 'undefined')) {
                    selected = vendorAttributeset[id]['on'];
                    inputs[i].checked = selected;
                    highlightAttributesetRow(inputs[i]);
                } else {
                    selected = inputs[i].checked;
                }
            } else {
                inputs[i].disabled = !selected;
                inputs[i].attributesetId = id;
                fname = inputs[i].name.replace(/^_/, '');
                if (vendorAttributeset[id] && vendorAttributeset[id][fname]) {
                    inputs[i].value = vendorAttributeset[id][fname];
                }
                $(inputs[i]).observe('change', changeVendorAttributesetProperty);
            }
        }
    }

    connect_attributesetJsObject.checkboxCheckCallback = function(grid, element, checked){
        $(element).up('tr').select('input', 'select').each(function (el) {
            if (el.type=='checkbox' && el.name=='') {
                if (!vendorAttributeset[el.value]) {
                    vendorAttributeset[el.value] = {};
                }
                vendorAttributeset[el.value]['on'] = checked;
                highlightAttributesetRow(element);
            } else {
                el.disabled = !checked;
            }
        });
        $('vendor_attributeset').value = Object.toJSON(vendorAttributeset);
    }

    connect_attributesetJsObject.rowClickCallback = function(grid, event){
        var trElement = Event.findElement(event, 'tr');
        var isInput   = Event.element(event).tagName.match(/(input|select|option)/i);
        if(trElement){
            var checkbox = Element.getElementsBySelector(trElement, 'input');
            if(checkbox[0]){
                var checked = isInput ? checkbox[0].checked : !checkbox[0].checked;
                connect_attributesetJsObject.setCheckboxChecked(checkbox[0], checked);
            }
        }
    }
    connect_attributesetJsObject.initGrid();

}
if (typeof connect_brandJsObject != "undefined") {
    if (!$('vendor_brand').value) {
        $('vendor_brand').value = '{}';
    }
    var vendorBrand = $('vendor_brand').value.evalJSON();

    function changeVendorBrandProperty() {
        if (!vendorBrand[this.brandId]) {
            vendorBrand[this.brandId] = {};
        }
        if (!this.name) {
            return;
        }
        var fname = this.name.replace(/^_/, '');
        vendorBrand[this.brandId][fname] = this.value;
        highlightBrandRow(this);
        $('vendor_brand').value = Object.toJSON(vendorBrand);
    }

    function highlightBrandRow(input, changed) {
        return; // disabled until done properly
        $(input).up('tr').select('td').each(function (el) {
            el.style.backgroundColor = changed || typeof changed=='undefined' ? '#ffb' : '';
        });
    }

    connect_brandJsObject.initCallback = function(self) {
        self.initGridRows && self.initGridRows();
    }

    connect_brandJsObject.initRowCallback = function(self, row) {
        var inputs = $(row).select('input', 'select'), id, selected, fname;
        for (var i=0; i<inputs.length; i++) {
            if (inputs[i].type=='checkbox' && inputs[i].name=='') {
                id = inputs[i].value;
                if (vendorBrand[id] && (typeof vendorBrand[id]['on'] !== 'undefined')) {
                    selected = vendorBrand[id]['on'];
                    inputs[i].checked = selected;
                    highlightBrandRow(inputs[i]);
                } else {
                    selected = inputs[i].checked;
                }
            } else {
                inputs[i].disabled = !selected;
                inputs[i].brandId = id;
                fname = inputs[i].name.replace(/^_/, '');
                if (vendorBrand[id] && vendorBrand[id][fname]) {
                    inputs[i].value = vendorBrand[id][fname];
                }
                $(inputs[i]).observe('change', changeVendorBrandProperty);
            }
        }
    }

    connect_brandJsObject.checkboxCheckCallback = function(grid, element, checked){
        $(element).up('tr').select('input', 'select').each(function (el) {
            if (el.type=='checkbox' && el.name=='') {
                if (!vendorBrand[el.value]) {
                    vendorBrand[el.value] = {};
                }
                vendorBrand[el.value]['on'] = checked;
                highlightBrandRow(element);
            } else {
                el.disabled = !checked;
            }
        });
        $('vendor_brand').value = Object.toJSON(vendorBrand);
    }

    connect_brandJsObject.rowClickCallback = function(grid, event){
        var trElement = Event.findElement(event, 'tr');
        var isInput   = Event.element(event).tagName.match(/(input|select|option)/i);
        if(trElement){
            var checkbox = Element.getElementsBySelector(trElement, 'input');
            if(checkbox[0]){
                var checked = isInput ? checkbox[0].checked : !checkbox[0].checked;
                connect_brandJsObject.setCheckboxChecked(checkbox[0], checked);
            }
        }
    }
    connect_brandJsObject.initGrid();

}

if (typeof udropship_vendor_productsJsObject != "undefined") {
    if (!$('vendor_products').value) {
        $('vendor_products').value = '{}';
    }
    var vendorProducts = $('vendor_products').value.evalJSON();

    function changeVendorProductProperty() {
        if (!vendorProducts[this.productId]) {
            vendorProducts[this.productId] = {};
        }
        if (!this.name) {
            return;
        }
        var fname = this.name.replace(/^_/, '');
        vendorProducts[this.productId][fname] = this.value;
        highlightProductRow(this);
        $('vendor_products').value = Object.toJSON(vendorProducts);
    }

    function highlightProductRow(input, changed) {
        return; // disabled until done properly
        $(input).up('tr').select('td').each(function (el) {
            el.style.backgroundColor = changed || typeof changed=='undefined' ? '#ffb' : '';
        });
    }

    udropship_vendor_productsJsObject.initCallback = function(self) {
        self.initGridRows && self.initGridRows();
    }

    udropship_vendor_productsJsObject.initRowCallback = function(self, row) {
        var inputs = $(row).select('input', 'select'), id, selected, fname;
        for (var i=0; i<inputs.length; i++) {
            if (inputs[i].type=='checkbox' && inputs[i].name=='') {
                id = inputs[i].value;
                if (vendorProducts[id] && (typeof vendorProducts[id]['on'] !== 'undefined')) {
                    selected = vendorProducts[id]['on'];
                    inputs[i].checked = selected;
                    highlightProductRow(inputs[i]);
                } else {
                    selected = inputs[i].checked;
                }
            } else {
                inputs[i].disabled = !selected;
                inputs[i].productId = id;
                fname = inputs[i].name.replace(/^_/, '');
                if (vendorProducts[id] && vendorProducts[id][fname]) {
                    inputs[i].value = vendorProducts[id][fname];
                }
                $(inputs[i]).observe('change', changeVendorProductProperty);
            }
        }
    }

    udropship_vendor_productsJsObject.checkboxCheckCallback = function(grid, element, checked){
        $(element).up('tr').select('input', 'select').each(function (el) {
            if (el.type=='checkbox' && el.name=='') {
                if (!vendorProducts[el.value]) {
                    vendorProducts[el.value] = {};
                }
                vendorProducts[el.value]['on'] = checked;
                highlightProductRow(element);
            } else {
                el.disabled = !checked;
            }
        });
        $('vendor_products').value = Object.toJSON(vendorProducts);
    }

    udropship_vendor_productsJsObject.rowClickCallback = function(grid, event){
        var trElement = Event.findElement(event, 'tr');
        var isInput   = Event.element(event).tagName.match(/(input|select|option)/i);
        if(trElement){
            var checkbox = Element.getElementsBySelector(trElement, 'input');
            if(checkbox[0]){
                var checked = isInput ? checkbox[0].checked : !checkbox[0].checked;
                udropship_vendor_productsJsObject.setCheckboxChecked(checkbox[0], checked);
            }
        }
    }
    udropship_vendor_productsJsObject.initGrid();
}

if (typeof udropship_vendor_shippingJsObject != "undefined") {
    if (!$('vendor_shipping').value) {
        $('vendor_shipping').value = '{}';
    }
    var vendorShipping = $('vendor_shipping').value.evalJSON();

    function changeVendorShippingProperty() {
        if (!vendorShipping[this.shippingId]) {
            vendorShipping[this.shippingId] = {};
        }
        if (!this.name) {
            return;
        }
        var fname = this.name.replace(/^_/, '');
        vendorShipping[this.shippingId][fname] = this.value;
        highlightProductRow(this);
        $('vendor_shipping').value = Object.toJSON(vendorShipping);
    }

    function highlightProductRow(input, changed) {
        return; // disabled until done properly
        $(input).up('tr').select('td').each(function (el) {
            el.style.backgroundColor = changed || typeof changed=='undefined' ? '#ffb' : '';
        });
    }

    udropship_vendor_shippingJsObject.initCallback = function(self) {
        self.initGridRows && self.initGridRows();
    }

    udropship_vendor_shippingJsObject.initRowCallback = function(self, row) {
        var inputs = $(row).select('input', 'select'), id, selected, fname;
        for (var i=0; i<inputs.length; i++) {
            if (inputs[i].type=='checkbox' && inputs[i].name=='') {
                id = inputs[i].value;
                if (vendorShipping[id] && (typeof vendorShipping[id]['on'] !== 'undefined')) {
                    selected = vendorShipping[id]['on'];
                    inputs[i].checked = selected;
                    highlightProductRow(inputs[i]);
                } else {
                    selected = inputs[i].checked;
                }
            } else {
                inputs[i].disabled = !selected;
                inputs[i].shippingId = id;
                fname = inputs[i].name.replace(/^_/, '');
                if (vendorShipping[id] && vendorShipping[id][fname]) {
                    inputs[i].value = vendorShipping[id][fname];
                }
                if (inputs[i].tagName.match(/select/i) && inputs[i].name.match(/carrier_code/i)) {
                    for (var j=0; j<inputs[i].options.length; j++) {
                        if (inputs[i].options[j].value && inputs[i].options[j].value!='**estimate**' && !allowedCarriers[id][inputs[i].options[j].value]) {
                            Element.remove(inputs[i].options[j]);
                            j--;
                        }
                    }
                }
                $(inputs[i]).observe('change', changeVendorShippingProperty);
            }
        }
    }

    udropship_vendor_shippingJsObject.checkboxCheckCallback = function(grid, element, checked){
        $(element).up('tr').select('input', 'select').each(function (el) {
            if (el.type=='checkbox' && el.name=='') {
                if (!vendorShipping[el.value]) {
                    vendorShipping[el.value] = {};
                }
                vendorShipping[el.value]['on'] = checked;
                highlightProductRow(element);
            } else {
                el.disabled = !checked;
            }
        });
        $('vendor_shipping').value = Object.toJSON(vendorShipping);
    }

    udropship_vendor_shippingJsObject.rowClickCallback = function(grid, event){
        var trElement = Event.findElement(event, 'tr');
        var isInput   = Event.element(event).tagName.match(/(input|select|option)/i);
        if(trElement){
            var checkbox = Element.getElementsBySelector(trElement, 'input');
            if(checkbox[0]){
                var checked = isInput ? checkbox[0].checked : !checkbox[0].checked;
                udropship_vendor_shippingJsObject.setCheckboxChecked(checkbox[0], checked);
            }
        }
    }
    udropship_vendor_shippingJsObject.initGrid();
}

</script>
<?php
        return ob_get_clean();
    }
     
}
      