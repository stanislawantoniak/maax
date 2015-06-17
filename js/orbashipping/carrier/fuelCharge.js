"use strict";

jQuery(document).ready(function ($) {

    $("#addFuelChargeItem").click(function(){
        var maxIndex = getMaxIndex();
        $("#"+fuelChargeTableId).find("tbody").append(getFuelRow(maxIndex+1));
    })

})

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
    "<td><input type='text' name='"+fieldName+"["+N+"][fuel_percent_date_from]' /></td>" +
    "<td><input type='text' name='"+fieldName+"["+N+"][fuel_percent_date_to]' /></td>" +
    "<td><button type='button' class='scalable delete delete-fieldset' onclick='"+onclickAttribute+"'><span></span></button></td>" +
    "</tr>";
}