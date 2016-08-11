"use strict";

jQuery(document).ready(function ($) {

    $(".addFuelChargeItem").click(function(){
       var table = jQuery(this).closest("table");

        var fieldName = table.parents("tr[id^=row_carriers]").find("input[type=hidden]").attr("id");
        var maxIndex = getMaxIndex(table);
        table.find("tbody").append(getFuelRow(fieldName,maxIndex+1));
        initDatepicker();
    })

    initDatepicker();

})

function initDatepicker(){
    jQuery("[class^=datepicker]").datepicker({
        dateFormat: 'dd-mm-yy',
        firstDay: 1

    });
}

function getMaxIndex(table){
    var indexes = [];
    var tr = table.find("tbody tr");
    tr.each(function(i,val){
        indexes.push(parseInt(jQuery(val).data("index")));
    })
    return (indexes.length > 0) ? Math.max.apply(Math,indexes) : 0;
}

function getFuelRow(fieldName, N){
    var onclickAttribute = 'jQuery(this).closest("tr").remove()';
    return "<tr data-index='"+N+"'>" +
    "<td><input type='text' name='"+fieldName+"["+N+"][fuel_percent]' /></td>" +
    "<td><input class='datepicker' type='text' name='"+fieldName+"["+N+"][fuel_percent_date_from]' /></td>" +
    "<td><button type='button' class='scalable delete delete-fieldset' onclick='"+onclickAttribute+"'><span></span></button></td>" +
    "</tr>";
}