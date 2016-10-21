jQuery(document).ready(function () {

    if(lockerCity){
        jQuery('[name=shipping_select_city] option[value="'+lockerCity+'"]').prop('selected', true);
        _makeMapRequest(lockerCity, true);
    }

    jQuery("[name=shipping_select_city]").change(function () {
        var enteredSearchValue = jQuery("[name=shipping_select_city] option:selected").val();
        console.log(enteredSearchValue);

        if (enteredSearchValue !== "undefined") {
            jQuery(".inpost_shipping_select_point_data").css("display","none");

            _makeMapRequest(enteredSearchValue, false);
        }
    });

    jQuery('[name=choose_inpost]').click(function (e) {
        jQuery(this).attr("disabled", true);
        jQuery(this).text(jQuery(this).attr('data-loading-text'));
        var inpost_name = jQuery(this).attr('inpost-name');

        jQuery.ajax({
            url: "/udpo/vendor/updateInpostData",
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

function _makeMapRequest(q, on_load) {
	jQuery("select[name=inpost_delivery_point_name]").attr("disabled", true);
    jQuery.ajax({
        url: "/udpo/deliverypoint/getInpostData",
        type: "POST",
        data: {town: q},
        success: function (response) {
			jQuery("select[name=inpost_delivery_point_name]").attr("disabled", false);
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

    options.push('<option value=0>'+Inpost["shipping_map_method_select"]+'</option>');
    jQuery(map_points).each(function (i, map_point) {
        if(map_point.name == lockerName){
            map_point_long_name = map_point.street + " " + map_point.building_number + ", " + map_point.town  + " (" + map_point.postcode + ")";
            options.push('<option selected data-carrier-town="' + map_point.town + '" data-carrier-additional="' + map_point.additional + '" data-carrier-pointcode="' + map_point.name + '" data-carrier-pointid="' + map_point.id + '" value="' + map_point.name + '">' + map_point_long_name + '</option>');
        }else{
            map_point_long_name = map_point.street + " " + map_point.building_number + ", " + map_point.town  + " (" + map_point.postcode + ")";
            options.push('<option data-carrier-town="' + map_point.town + '" data-carrier-additional="' + map_point.additional + '" data-carrier-pointcode="' + map_point.name + '" data-carrier-pointid="' + map_point.id + '" value="' + map_point.name + '">' + map_point_long_name + '</option>');
        }
    });

    jQuery("select[name=inpost_delivery_point_name]")
        .html(options.join(""))
        .attr("disabled", false)
        .val(0);

    jQuery('[name=inpost_delivery_point_name] option[value="'+lockerName+'"]')
        .prop('selected', true);

    jQuery("select[name=inpost_delivery_point_name]")
        .select2({
            dropdownParent: jQuery("#editShippingMethodModal"),
            language: localeCode
        });

    prepareGroupPoints(map_points);
}

function constructShippingPointSelect(map_points) {
    var options = [],
        map_point_long_name;

    options.push('<option value=0>'+Inpost["shipping_map_method_select"]+'</option>');
    jQuery(map_points).each(function (i, map_point) {
        map_point_long_name = map_point.street + " " + map_point.building_number + ", " + map_point.town  + " (" + map_point.postcode + ")";
        options.push('<option data-carrier-town="' + map_point.town + '" data-carrier-additional="' + map_point.additional + '" data-carrier-pointcode="' + map_point.name + '" data-carrier-pointid="' + map_point.id + '" value="' + map_point.name + '">' + map_point_long_name + '</option>');
    });

    //Jeśli w mieście jest tylko jeden paczkomat,
    // niech wybiera go automatycznie
    if(typeof map_points !== "undefined" && map_points.length === 1){
        jQuery("select[name=inpost_delivery_point_name]")
            .html(options.join(""))
            .attr("disabled", false)
            .val(map_points[0].name);

        showShippingData(map_points[0]);

    } else {
        jQuery("select[name=inpost_delivery_point_name]")
            .html(options.join(""))
            .attr("disabled", false)
            .val(0);

        prepareGroupPoints(map_points);
    }

    jQuery("select[name=inpost_delivery_point_name]")
        .select2({dropdownParent: jQuery("#editShippingMethodModal"), language: localeCode});

    
}

function prepareGroupPoints(map_points){
    jQuery("[name=inpost_delivery_point_name]").change(function () {
        jQuery('[name=choose_inpost]').attr('disabled', 'disabled');
        jQuery(".inpost_shipping_select_point_data").css("display","none");
        var enteredSearchPointValue = jQuery("[name=inpost_delivery_point_name] option:selected").val();

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
    var html_data = inPostLabel + " " + map_point.name + "<br/>" + map_point.street + " " + map_point.building_number + "<br/>" + map_point.postcode + " " + map_point.town;
    jQuery('.inpost_shipping_select_point_data').css("display","block");
    jQuery('.inpost_shipping_select_point_data .address_data').html(html_data);
    jQuery('[name=choose_inpost]').removeAttr('disabled');
    jQuery('[name=choose_inpost]').attr('inpost-name', map_point.name);
}