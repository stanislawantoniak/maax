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
        onSelect: function() {
            var borderOk = "1px solid #ccc";
            var borderError = "1px solid red";

            var tr = jQuery(this).closest("tr");

            var datepicker_from = tr.find("input[name*=fuel_percent_date_from]");
            var datepicker_to = tr.find("input[name*=fuel_percent_date_to]");
            var f = datepicker_from.val();
            var t = datepicker_to.val();

            datepicker_from.css({"border": borderOk});
            datepicker_to.css({"border": borderOk});

            var begD = jQuery.datepicker.parseDate('dd-mm-yy', f);
            var endD = jQuery.datepicker.parseDate('dd-mm-yy', t);

            if(endD !== null && begD > endD){
                alert("Fuel Charge: Date to can not be earlier than Date from");
                datepicker_from.css({"border": borderError});
                datepicker_to.css({"border": borderError});
            }


        },
        beforeShowDay: function (date) {

            var trs = jQuery(this).closest("table.data").find("tbody tr");
            var dateRange = [];
            trs.each(function (i, tr) {
                var datepicker_from, datepicker_to, f, t, startDate, endDate;

                datepicker_from = jQuery(tr).find("input[name*=fuel_percent_date_from]");
                datepicker_to = jQuery(tr).find("input[name*=fuel_percent_date_to]");

                f = datepicker_from.val();
                t = datepicker_to.val();


                if (f.length > 0 && t.length) {
                    startDate = jQuery.datepicker.parseDate('dd-mm-yy', f);
                    endDate = jQuery.datepicker.parseDate('dd-mm-yy', t);

                    for (var d = new Date(startDate); d <= new Date(endDate); d.setDate(d.getDate() + 1)) {
                        dateRange.push(jQuery.datepicker.formatDate('dd-mm-yy', d));
                    }
                }


            })
            var dateString = jQuery.datepicker.formatDate('dd-mm-yy', date);
            return [dateRange.indexOf(dateString) == -1];
        }

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
    "<td><input class='datepicker' type='text' name='"+fieldName+"["+N+"][fuel_percent_date_to]' /></td>" +
    "<td><button type='button' class='scalable delete delete-fieldset' onclick='"+onclickAttribute+"'><span></span></button></td>" +
    "</tr>";
}