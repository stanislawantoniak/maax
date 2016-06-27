jQuery(document).ready(function () {

    if(typeof lockerCity !== "undefined"){
        jQuery('[name=shipping_select_city] option[value="'+lockerCity+'"]').prop('selected', true);
        _makeMapRequest(lockerCity, true);
    }

    jQuery("[name=shipping_select_city]").change(function () {
        jQuery('[name=choose_inpost]').attr('disabled', 'disabled');
        var enteredSearchValue = jQuery("[name=shipping_select_city] option:selected").val();

        if (enteredSearchValue !== "undefined") {
            jQuery(".shipping_select_point_data").css("display","none");

            _makeMapRequest(enteredSearchValue, false);
        }
    });

    jQuery('[name=choose_inpost]').click(function () {
        jQuery('[name=choose_inpost]').text(jQuery('[name=choose_inpost]').attr('data-loading-text'));
        var inpost_name = jQuery('[name=choose_inpost]').attr('inpost-name');

        jQuery.ajax({
            url: "/udpo/inpost/updateInpostData",
            type: "POST",
            data: {inpostName: inpost_name, orderId: orderId},
            success: function (response) {
                location.reload();
            },
            error: function (response) {
                console.log(response);
            }
        });
    });
});

function _makeMapRequest(q, on_load) {
    jQuery.ajax({
        url: "/udpo/inpost/getInpostData",
        type: "POST",
        data: {town: q},
        success: function (response) {
            gmarkers = [];  //to collect only filtered markers (used in showMarkerWindow)
            data = jQuery.parseJSON(response);

            var pointsOnMap = data.map_points;

            if(on_load){
                constructShippingPointSelectOnLoad(pointsOnMap);
            }else{
                constructShippingPointSelect(pointsOnMap);
            }
        },
        error: function (response) {
            console.log(response);
        }
    });
}

function constructShippingPointSelectOnLoad(map_points){
    var options = [],
        map_point_long_name;

    options.push('<option value="">'+Inpost["shipping_map_method_select"]+'</option>');
    jQuery(map_points).each(function (i, map_point) {
        if(map_point.name == lockerName){
            map_point_long_name = map_point.street + " " + map_point.building_number + ", " + map_point.town  + " (" + map_point.postcode + ")";
            options.push('<option selected data-carrier-town="' + map_point.town + '" data-carrier-additional="' + map_point.additional + '" data-carrier-pointcode="' + map_point.name + '" data-carrier-pointid="' + map_point.id + '" value="' + map_point.name + '">' + map_point_long_name + '</option>');
        }else{
            map_point_long_name = map_point.street + " " + map_point.building_number + ", " + map_point.town  + " (" + map_point.postcode + ")";
            options.push('<option data-carrier-town="' + map_point.town + '" data-carrier-additional="' + map_point.additional + '" data-carrier-pointcode="' + map_point.name + '" data-carrier-pointid="' + map_point.id + '" value="' + map_point.name + '">' + map_point_long_name + '</option>');
        }
    });

    jQuery("select[name=shipping_select_point]")
        .html(options.join(""))
        .attr("disabled", false)
        .val("");

    jQuery('[name=shipping_select_point] option[value="'+lockerName+'"]').prop('selected', true);

    jQuery("select[name=shipping_select_point]")
        .select2({dropdownParent: jQuery("#select_inpost_point"), language: localeCode});

    prepareGroupPoints(map_points);
}

function constructShippingPointSelect(map_points) {
    var options = [],
        map_point_long_name;

    options.push('<option value="default">'+Inpost["shipping_map_method_select"]+'</option>');
    jQuery(map_points).each(function (i, map_point) {
        map_point_long_name = map_point.street + " " + map_point.building_number + ", " + map_point.town  + " (" + map_point.postcode + ")";
        options.push('<option data-carrier-town="' + map_point.town + '" data-carrier-additional="' + map_point.additional + '" data-carrier-pointcode="' + map_point.name + '" data-carrier-pointid="' + map_point.id + '" value="' + map_point.name + '">' + map_point_long_name + '</option>');
    });

    //Jeśli w mieście jest tylko jeden paczkomat,
    // niech wybiera go automatycznie

    if(map_points.length === 1){
        jQuery("select[name=shipping_select_point]")
            .html(options.join(""))
            .attr("disabled", false)
            .val(map_points[0].name);

        showShippingData(map_points[0]);

    } else {
        jQuery("select[name=shipping_select_point]")
            .html(options.join(""))
            .attr("disabled", false)
            .val("default");

        prepareGroupPoints(map_points);
    }

    jQuery("select[name=shipping_select_point]")
        .select2({dropdownParent: jQuery("#select_inpost_point"), language: localeCode});

    
}

function prepareGroupPoints(map_points){
    jQuery("[name=shipping_select_point]").change(function () {
        jQuery('[name=choose_inpost]').attr('disabled', 'disabled');
        jQuery(".shipping_select_point_data").css("display","none");
        var enteredSearchPointValue = jQuery("[name=shipping_select_point] option:selected").val();

        if (enteredSearchPointValue !== "undefined") {
            jQuery(map_points).each(function (i, map_point) {
                if(map_point.name == enteredSearchPointValue){
                    showShippingData(map_point);
                }
            });
        }
    });
}

function showShippingData(map_point){
    var html_data = pachkomatLocate + " " + map_point.name + "<br/>" + map_point.street + " " + map_point.building_number + "<br/>" + map_point.postcode + " " + map_point.town;
    jQuery('.shipping_select_point_data').css("display","block");
    jQuery('.shipping_select_point_data .address_data').html(html_data);
    jQuery('[name=choose_inpost]').removeAttr('disabled');
    jQuery('[name=choose_inpost]').attr('inpost-name', map_point.name);
}