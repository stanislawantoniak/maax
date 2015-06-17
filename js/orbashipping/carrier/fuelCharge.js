"use strict";

jQuery(document).ready(function ($) {

    $("#addFuelChargeItem").click(function(){
        var maxIndex = getMaxIndex();
        $("#"+fuelChargeTableId).find("tbody").append(getFuelRow(maxIndex+1));
        initDatepicker();
    })

    initDatepicker();

})

function initDatepicker(){
    jQuery("[class^=datepicker]").datepicker({
        dateFormat: 'dd-mm-yy',
        onSelect: function(dateText) {
            var borderOk = "1px solid #ccc";
            var borderError = "1px solid red";

            var tr = jQuery(this).closest("tr");

            var datepicker_from = tr.find(".datepicker_from");
            var datepicker_to = tr.find(".datepicker_to");

            datepicker_from.css({"border": borderOk});
            datepicker_to.css({"border": borderOk});

            var f = datepicker_from.val();
            var t = datepicker_to.val();

            var begD = jQuery.datepicker.parseDate('dd-mm-yy', f);
            var endD = jQuery.datepicker.parseDate('dd-mm-yy', t);
            

            if(begD > endD){
                alert("Fuel Charge: Date to can not be earlier than Date from");
                datepicker_from.css({"border": borderError});
                datepicker_to.css({"border": borderError});
            }


        }
    });
}

function getMaxIndex(){
    var indexes = [];
    var tr = jQuery("#"+fuelChargeTableId).find("tbody tr");
    tr.each(function(i,val){
        indexes.push(parseInt(jQuery(val).data("index")));
    })
    return (indexes.length > 0) ? Math.max.apply(Math,indexes) : 0;
}

function getFuelRow(N){
    var onclickAttribute = 'jQuery(this).closest("tr").remove()';
    return "<tr data-index='"+N+"'>" +
    "<td><input type='text' name='"+fieldName+"["+N+"][fuel_percent]' /></td>" +
    "<td><input class='datepicker' type='text' name='"+fieldName+"["+N+"][fuel_percent_date_from]' /></td>" +
    "<td><input class='datepicker' type='text' name='"+fieldName+"["+N+"][fuel_percent_date_to]' /></td>" +
    "<td><button type='button' class='scalable delete delete-fieldset' onclick='"+onclickAttribute+"'><span></span></button></td>" +
    "</tr>";
}