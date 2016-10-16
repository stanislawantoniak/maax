jQuery(document).ready(function () {

    if(lockerPwrCity){
        jQuery('[name=pwr_shipping_select_city] option[value="'+lockerPwrCity+'"]').prop('selected', true);
        _makePwrMapRequest(lockerPwrCity, true);
    }

    jQuery("[name=pwr_shipping_select_city]").change(function () {
        jQuery('[name=choose_inpost]').attr('disabled', 'disabled');
        var enteredSearchValue = jQuery("[name=pwr_shipping_select_city] option:selected").val();

        if (enteredSearchValue !== "undefined") {
            jQuery(".pwr_shipping_select_point_data").css("display","none");

            _makePwrMapRequest(enteredSearchValue, false);
        }
    });

    jQuery('[name=choose_inpost]').click(function (e) {
        jQuery(this).attr("disabled", true);
        jQuery(this).text(jQuery(this).attr('data-loading-text'));
        var inpost_name = jQuery(this).attr('inpost-name');

        jQuery.ajax({
            url: "/udpo/vendor/updatePwrData",
            type: "POST",
            data: {inpostName: inpost_name, poId: poId},
            success: function (response) {
                location.reload();
            },
            error: function (response) {
                console.log(response);
            }
        });
    });
});

function _makePwrMapRequest(q, on_load) {
	jQuery("select[name=pwr_delivery_point_name]").attr("disabled", true);
    jQuery.ajax({
        url: "/udpo/deliverypoint/getPwrData",
        type: "POST",
        data: {town: q},
        success: function (response) {
			jQuery("select[name=pwr_delivery_point_name]").attr("disabled", false);
            var pointsOnMap = jQuery.parseJSON(response);

            if(on_load){
                constructShippingPwrPointSelectOnLoad(pointsOnMap);
            }else{
                constructShippingPwrPointSelect(pointsOnMap);
            }
        },
        error: function (response) {
            console.log(response);
        }
    });
}

function constructShippingPwrPointSelectOnLoad(map_points){
    var options = [],
        map_point_long_name;

    options.push('<option value=0>'+Inpost["pwr_shipping_map_method_select"]+'</option>');
    jQuery(map_points).each(function (i, map_point) {
        if(map_point.name == lockerPwrName){
            map_point_long_name = map_point.street + " " + map_point.building_number + ", " + map_point.town  + " (" + map_point.postcode + ")";
            options.push('<option selected data-carrier-town="' + map_point.town + '" data-carrier-additional="' + map_point.additional + '" data-carrier-pointcode="' + map_point.name + '" data-carrier-pointid="' + map_point.id + '" value="' + map_point.name + '">' + map_point_long_name + '</option>');
        }else{
            map_point_long_name = map_point.street + " " + map_point.building_number + ", " + map_point.town  + " (" + map_point.postcode + ")";
            options.push('<option data-carrier-town="' + map_point.town + '" data-carrier-additional="' + map_point.additional + '" data-carrier-pointcode="' + map_point.name + '" data-carrier-pointid="' + map_point.id + '" value="' + map_point.name + '">' + map_point_long_name + '</option>');
        }
    });

    jQuery("select[name=pwr_delivery_point_name]")
        .html(options.join(""))
        .attr("disabled", false)
        .val("");

    jQuery('[name=pwr_delivery_point_name] option[value="'+lockerPwrName+'"]').prop('selected', true);

    jQuery("select[name=pwr_delivery_point_name]")
        .select2({
            dropdownParent: jQuery("#editShippingMethodModal"),
            language: localeCode
        });

    preparePwrGroupPoints(map_points);
}

function constructShippingPwrPointSelect(map_points) {
    var options = [],
        map_point_long_name;

    options.push('<option value=0>'+Inpost["pwr_shipping_map_method_select"]+'</option>');
    jQuery(map_points).each(function (i, map_point) {
        map_point_long_name = map_point.street + " " + map_point.building_number + ", " + map_point.town  + " (" + map_point.postcode + ")";
        options.push('<option data-carrier-town="' + map_point.town + '" data-carrier-additional="' + map_point.additional + '" data-carrier-pointcode="' + map_point.name + '" data-carrier-pointid="' + map_point.id + '" value="' + map_point.name + '">' + map_point_long_name + '</option>');
    });

    //Jeśli w mieście jest tylko jeden paczkomat,
    // niech wybiera go automatycznie
    if(typeof map_points !== "undefined" && map_points.length === 1){
        jQuery("select[name=pwr_delivery_point_name]")
            .html(options.join(""))
            .attr("disabled", false)
            .val(map_points[0].name);

        showPwrShippingData(map_points[0]);

    } else {
        jQuery("select[name=pwr_delivery_point_name]")
            .html(options.join(""))
            .attr("disabled", false)
            .val(0);

        preparePwrGroupPoints(map_points);
    }

    jQuery("select[name=pwr_delivery_point_name]")
        .select2({
            dropdownParent: jQuery("#editShippingMethodModal"),
            language: localeCode
        });

    
}

function preparePwrGroupPoints(map_points){
    jQuery("[name=pwr_delivery_point_name]").change(function () {
        jQuery('[name=choose_inpost]').attr('disabled', 'disabled');
        jQuery(".pwr_shipping_select_point_data").css("display","none");
        var enteredSearchPointValue = jQuery("[name=pwr_delivery_point_name] option:selected").val();

        if (enteredSearchPointValue !== "undefined") {
            jQuery(map_points).each(function (i, map_point) {
                if(map_point.name == enteredSearchPointValue){
                    showPwrShippingData(map_point);
                }
            });
        }
    });
}

function showPwrShippingData(map_point){
    var html_data = pwrLabel + " " + map_point.name + "<br/>" + map_point.street + " " + map_point.building_number + "<br/>" + map_point.postcode + " " + map_point.town;
    jQuery('.pwr_shipping_select_point_data').css("display","block");
    jQuery('.pwr_shipping_select_point_data .address_data').html(html_data);
    jQuery('[name=choose_inpost]').removeAttr('disabled');
    jQuery('[name=choose_inpost]').attr('inpost-name', map_point.name);
}