/* global inPostPoints */

(function () {
    "use strict";

    Mall.Cart.Shipping = {
        form_id: "cart-shipping-methods-form",
        content: "#cart-shipping-methods",
        init: function () {
            var self = this;
            self.updateTotals();

            self.handleShippingMethodSelect();

            //Validation
            self.attachShippingFormValidation();
            //--Validation

            jQuery("#change-shipping-type").click(function () {
                jQuery(".shipping-method-selector").slideDown();
                jQuery(".shipping-method-selected").slideUp();

                //Clear selected shipping
                jQuery("[name=_shipping_method]").prop("checked", false);
            });

            jQuery(".data_shipping_item").click(function(){
                jQuery(this).find("input[name=_shipping_method]")
                    .prop("checked",true)
                    .change();
            });

            if (jQuery("#cart-shipping-methods [name=_shipping_method]").length == 1) {
                jQuery("#cart-shipping-methods [name=_shipping_method]").click();
            }

            if (typeof self.getSelectedShipping().val() !== "undefined") {
                jQuery.ajax({
                    url: "/checkout/singlepage/saveBasketShipping/",
                    type: "POST",
                    data: jQuery("#cart-shipping-methods-form").serializeArray()
                });
            }

            self.implementMapSelections();
        },

        getVendors: function () {
            return Mall.reg.get("vendors");
        },
        getVendorCosts: function () {
            return Mall.reg.get("vendor_costs");
        },
        getSelectedShipping: function () {
            return jQuery(Mall.Cart.Shipping.content).find("input[name=_shipping_method]:checked");
        },
        updateTotals: function () {
            var content = jQuery("#cart-shipping-methods");

            var methodRadio = content.find("input[name=_shipping_method]:checked");
            var shippingCost;

            if (jQuery.type(methodRadio.val()) !== "undefined") {
                //shipping total
                shippingCost = jQuery(methodRadio).attr("data-method-cost");
                var shippingCostFormatted = jQuery(methodRadio).attr("data-method-cost-formatted");
                jQuery('#product_summary li[data-target="val_delivery_cost"]').find("span.price").html(shippingCostFormatted);

            } else {
                shippingCost = 0; //not selected yet
            }
            //Grand total
            var quote_products_total = (jQuery.type(Mall.reg.get("quote_products_total")) !== "undefined") ? Mall.reg.get("quote_products_total") :0;
            var quote_discount_total = (jQuery.type(Mall.reg.get("quote_discount_total")) !== "undefined") ? Mall.reg.get("quote_discount_total") :0;

            var totalSum = parseFloat(parseFloat(quote_products_total) + parseFloat(shippingCost) + parseFloat(quote_discount_total));
            jQuery("#sum_price .value_sum_price").html(Mall.currency(totalSum));
        },
        handleShippingMethodSelect: function () {
            jQuery(document).delegate("input[data-select-shipping-method-trigger=1]",
                "change",
                function (e) {
                    Mall.Cart.Shipping.setShippingMethod(this);
                });
            jQuery(document).delegate("a[data-select-shipping-method-trigger=1]",
                "click",
                function (e) {
                    e.preventDefault();
                    var parentModal = jQuery(this).parents(".modal");
                    var deliveryMethod = parentModal.attr("data-carrier-points");
                    console.log(deliveryMethod);
                    switch(deliveryMethod){
                        case 'zolagopickuppoint':
                            parentModal.modal("hide");
                            var selectedOption = parentModal.find("[name=shipping_select_pos] option:selected");
                            Mall.Cart.Shipping.setShippingMethod(selectedOption);
                            break;
                        case 'ghinpost':
                            Mall.Cart.Shipping.setShippingMethod(this);
                            parentModal.modal("hide");
                            showMarkerOnMap(jQuery(e.target).attr("data-carrier-pointcode"));
                            break;
                    }
                    return false;

                });
        },
        implementMapSelections: function () {
            var self = this;
            if (jQuery("input[data-select-shipping-method-trigger=0]").length == 0)
                return;

            // FIXME not sure this part is multi map
            //Init map
            google.maps.event.addDomListener(window, 'load', initMapInPost);
            google.maps.event.addDomListener(window, "resize", resizingMap());

            var inpostModal = jQuery(".carrier-points-modal[data-carrier-points='ghinpost']");
            jQuery("[name=shipping_select_point]")
                .select2({dropdownParent: jQuery(".carrier-points-modal[data-carrier-points='ghinpost']"),language: Mall.reg.get("localeCode")})
                .change(function () {
                    var el = jQuery(this), val = el.val();
                    el.addClass("onchange_shipping_select_point");
                    if (typeof val !== "undefined" && val.length > 0) {
                        showMarkerOnMap(val);
                    }


                    jQuery(".nearest_stores_container_list").hide();
                    jQuery(".nearest_stores_container_link").text(Mall.translate.__("shipping_map_show_nearest_link"));

                });
            if (Mall.getIsBrowserMobile()) {
                inpostModal.find('.select2').on('select2:open', function (e) {
                    jQuery('.select2-search input').prop('focus', false);
                });
            }
            jQuery("[data-select-shipping-method-trigger=0]").change(function (e) {
                //1. populate popup
                var deliveryType = jQuery(this).attr("data-carrier-delivery-type");
                var deliverySelectPointsModal = jQuery("div.modal[data-carrier-points='"+deliveryType+"']");

                if (deliveryType == "zolagopickuppoint" && deliverySelectPointsModal.find("[name=shipping_select_pos] option").length == 1) {
                    //but if there is one POS available it is selected already
                    deliverySelectPointsModal.find('a[data-select-shipping-method-trigger="1"]').click();
                } else {
                    deliverySelectPointsModal.modal("show");
                }

                handleGeoLocationInPost();
            });
            jQuery("[name=shipping_select_city]").select2({
                placeholder: Mall.translate.__("shipping_map_select_city"),
                dropdownParent: inpostModal,
                language: Mall.reg.get("localeCode")
            });

            jQuery("[name=shipping_select_point]")
                .attr("disabled", true)
                .val("")
                .select2({
                    dropdownParent: inpostModal,
                    language: Mall.reg.get("localeCode")
                });


            inpostModal.on('show.bs.modal', function () {
                //Must wait until the render of the modal appear,
                // that's why we use the resizeMap and NOT resizingMap!! ;-)
                var sessionPoint = jQuery("[name=shipping_point_code]");

                resizeMap(sessionPoint.val());

                jQuery("#cart-shipping-methods input[name=shipping_point_code]").val("");
            });
            inpostModal.on('hide.bs.modal', function () {
                //If inPost selected but paczkomat not selected
                if (
                    (jQuery("#cart-shipping-methods input[data-select-shipping-method-trigger=0]:checked").length > 0
                        && (typeof(jQuery("#cart-shipping-methods input[name=shipping_point_code]").val()) == "undefined")
                    )
                    ||
                    (typeof(jQuery("#cart-shipping-methods input[name=shipping_point_code]").val()) !== "undefined"
                        && jQuery("#cart-shipping-methods input[name=shipping_point_code]").val().length == 0
                    )

                ) {
                    //Clear selected shipping
                    jQuery("[name=_shipping_method]").prop("checked", false);
                }
            });
            self.attachShowHideMapOnMobile();
            self.attachShowHideNearestPointsList();

        },
        attachShowOnMapSavedInSessionPoint: function () {
            // FIXME not sure this part is multi map
            var sessionPoint = jQuery("[name=shipping_point_code]");
            var inpostModal = jQuery(".carrier-points-modal[data-carrier-points='ghinpost']");
            var sessionPointTown;
            if (sessionPointName) {
                sessionPointTown = sessionPoint.attr("data-town");

                jQuery(".shipping_select_point_data").html("");
                jQuery("[name=shipping_select_city]")
                    .val(sessionPointTown)
                    .select2({dropdownParent: inpostModal, language: Mall.reg.get("localeCode")});

                searchOnMap(sessionPointTown, sessionPointName);
            }


        },
        attachShippingFormValidation: function(){
            jQuery("#cart-shipping-methods-form").validate({
                ignore: "",

                rules: {
                    '_shipping_method': {
                        required: true
                    }

                },
                messages: {
                    _shipping_method: {required: Mall.translate.__("validation_please_select_shipping")},

                },
                invalidHandler: function (form, validator) {
                    if (!validator.numberOfInvalids()) {
                        return true;
                    }

                },
                errorPlacement: function(error, element) {
                    jQuery('#cart-shipping-methods-form .data-validate').append(error);
                }
            });
            jQuery("#cart-buy").on('click', function() {
                if(!jQuery("#cart-shipping-methods-form").valid() || jQuery("#cart-buy").is(":disabled")){
                    return false;
                } else {
                    jQuery(this).find('i').addClass('fa fa-spinner fa-spin');
                    window.location = jQuery("#cart-buy").attr("href");
                }
            });
        },
        attachShowHideNearestPointsList: function(){

            jQuery("body").delegate(".nearest_stores_container_link",
                "click",
                function (e) {
                    e.preventDefault();
                    jQuery(this).text(Mall.translate.__("shipping_map_hide_nearest_link"));
                    if (jQuery('.nearest_stores_container_list').is(':visible')) {
                        jQuery(this).text(Mall.translate.__("shipping_map_show_nearest_link"));
                    } else {
                        jQuery(this).text(Mall.translate.__("shipping_map_hide_nearest_link"));
                    }
                    jQuery(".nearest_stores_container_list").slideToggle();
                });
        },
        attachShowHideMapOnMobile: function(){
            if(Mall.getIsBrowserMobile()){
                jQuery(".map_delivery_container").hide();
            }
            jQuery(document).delegate(".map_delivery_container_show",
                "click",
                function (e) {
                    e.preventDefault();
                    resizeMapMobile();
                    jQuery(this).text(Mall.translate.__("shipping_map_hide_map_link"));
                    if (jQuery('.map_delivery_container').is(':visible')) {
                        jQuery(this).text(Mall.translate.__("shipping_map_show_map_link"));
                    } else {
                        jQuery(this).text(Mall.translate.__("shipping_map_hide_map_link"));
                    }
                    jQuery(".map_delivery_container").slideToggle();
                });
        },
        appendSelectedCartShipping: function (selectedMethodData) {

            var shippingMethodSelectedContainer = jQuery(".shipping-method-selected");

            shippingMethodSelectedContainer.find('[data-item="method"]').html(selectedMethodData["method"]);
            shippingMethodSelectedContainer.find('[data-item="description"]').html(selectedMethodData["description"]);
            shippingMethodSelectedContainer.find('[data-item="logo"]').html(selectedMethodData["logo"]);

            shippingMethodSelectedContainer.find('[data-item="additional"]').html(selectedMethodData["additional"]);


            jQuery(".shipping-method-selector").slideUp();
            jQuery(".shipping-method-selected").slideDown();
        },
        setShippingMethod: function (target) {
            var selectedMethodData = [];

            var vendors = Mall.Cart.Shipping.getVendors(),
                content = jQuery(Mall.Cart.Shipping.content);

            var methodRadio = content.find("input[name=_shipping_method]:checked");
            var shipping = methodRadio.val();


            selectedMethodData["logo"] = jQuery(methodRadio).attr("data-carrier-logo");
            selectedMethodData["method"] = jQuery(methodRadio).attr("data-carrier-method");
            selectedMethodData["description"] = jQuery(methodRadio).attr("data-carrier-description");
            selectedMethodData["additional"] = jQuery(target).attr("data-carrier-additional");

            var pointCode = (jQuery(target).attr("data-carrier-pointcode") !== "undefined") ? jQuery(target).attr("data-carrier-pointcode") : "";
            var pointId = (jQuery(target).attr("data-carrier-pointid") !== "undefined") ? jQuery(target).attr("data-carrier-pointid") : "";
            var pointTown = (jQuery(target).attr("data-carrier-town") !== "undefined") ? jQuery(target).attr("data-carrier-town") : "";

            if (jQuery.type(shipping) !== "undefined") {
                var inputs = '';
                jQuery.each(vendors, function (i, vendor) {
                    inputs += '<input type="hidden" name="shipping_method[' + vendor + ']" value="' + shipping + '" required="required" />';
                });
                if (jQuery.type(pointId) !== "undefined") {
                    inputs += '<input type="hidden" data-id="' + pointId + '" data-town="' + pointTown + '" name="shipping_point_code" value="' + pointCode + '"  />';
                }
                content.find("form .shipping-collect").html(inputs);
            }

            Mall.Cart.Shipping.appendSelectedCartShipping(selectedMethodData);


            var formData = jQuery("#cart-shipping-methods-form").serializeArray();
            jQuery("#cart-buy")
                .prop("disabled",true)
                .find('i')
                .addClass('fa fa-spinner fa-spin');

            jQuery("#cart-buy-overlay").removeClass("hidden");
            jQuery.ajax({
                url: "/checkout/singlepage/saveBasketShipping/",
                type: "POST",
                data: formData
            }).done(function (response) {
                jQuery("#cart-buy")
                    .prop("disabled", false);
                jQuery("#cart-buy").find('i')
                    .removeClass('fa fa-spinner fa-spin');

                jQuery("#cart-buy-overlay").addClass("hidden");
            });

            Mall.Cart.Shipping.updateTotals();
        }
    }
})();



var clusterStyles = [
    {
        textColor: 'white',
        url: '/js/gh/storemap/cluster_icons/circle1.png',
        textSize: 14,
        backgroundPosition: '1px 0px',
        height: 40,
        width: 40
    },
    {
        textColor: 'white',
        url: '/js/gh/storemap/cluster_icons/circle2.png',
        textSize: 18,
        backgroundPosition: '1px 0px',
        height: 60,
        width: 60
    },
    {
        textColor: 'white',
        url: '/js/gh/storemap/cluster_icons/circle2.png',
        textSize: 18,
        backgroundPosition: '1px 0px',
        height: 60,
        width: 60
    }
];
var markerClusterer = null;
var map = null;
var infowindow = null;

var defaultCenterLang = 52.4934482;
var defaultCenterLat = 18.8979594;

var minDist = 30; //km
var minDistFallBack = 100; //km
var nearestStores = [];

var gmarkers = [];
var gmarkersNameRelation = [];


function initMapInPost() {

    var mapOptions = {
        zoom: 8,
        center: new google.maps.LatLng(defaultCenterLang, defaultCenterLat),
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        mapTypeControl: false,
        mapTypeControlOptions: {
            style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
            position: google.maps.ControlPosition.TOP_RIGHT
        },
        panControl: true,
        panControlOptions: {
            position: google.maps.ControlPosition.TOP_RIGHT
        },
        zoomControl: true,
        zoomControlOptions: {
            style: google.maps.ZoomControlStyle.LARGE,
            position: google.maps.ControlPosition.TOP_RIGHT
        },
        scaleControl: true,
        streetViewControl: false
    };

    if (window.innerWidth < Mall.Breakpoint.sm) {
        mapOptions.zoomControlOptions.position = google.maps.ControlPosition.RIGHT_CENTER;
        mapOptions.zoomControlOptions.style = google.maps.ZoomControlStyle.SMALL;
        mapOptions.panControl = false;
    }

    map = new google.maps.Map(document.getElementById('map_delivery'), mapOptions);

    infowindow = new google.maps.InfoWindow({
        buttons: {close: {show: 0}}
    });
    data = []; //No city no points
}

function refreshMapInPost(filteredData, nearestStores) {

    var imageUrl = "/js/gh/gmap/icons/gmap_marker_black.png";

    if (typeof filteredData !== "undefined")
        data = filteredData;

    //Points count (not including nearest)
    var pointsCount = data.length;

    var markers = [];
    if (markerClusterer) {
        markerClusterer.clearMarkers();
    }

    var markerImage = new google.maps.MarkerImage(imageUrl,
        new google.maps.Size(40, 40));

    //setMarkers
    //Join nearest stores (if GEO localization on)
    if (nearestStores.length > 0) {
        for (var k = 0; k < nearestStores.length; k++) {
            data.push(nearestStores[k]);
        }
    }

    for (var i = 0; i < data.length; i++) {
        var pos = data[i];

        var posLatLng = new google.maps.LatLng(pos.latitude, pos.longitude);
        var marker = new google.maps.Marker({
            id: pos.id,
            name: pos.name,
            town: pos.town,
            nearest: pos.nearest,
            position: posLatLng,
            map: map,
            icon: markerImage,
            html: formatInfoWindowContent(pos),
            details: formatDetailsContent(pos)
        });

        var zoomOnShowCity = 10,
            zoomOnShowCityMobile = 10,

            zoomOnShowPoint = 13,
            zoomOnShowPointBigCities = 15;

        google.maps.event.addListener(marker, "click", function () {
            //this - clicked marker
            /*
             Jeśli kliknie się w dowolny paczkomat na mapie
             szczegóły pojawiają się z lewej i punkt pojawia się w polu adresu
             */
            jQuery(".shipping_select_point_data").html(this.details);
            infowindow.setContent(this.html);

            var inpostModal = jQuery(".carrier-points-modal[data-carrier-points='ghinpost']");

            //Refresh markers and "City", "Address" filters
            //if nearest store marker clicked, but the city is different from selected
            if (this.nearest === 1 && jQuery("select[name=shipping_select_city]").val() !== this.town) {
                jQuery("select[name=shipping_select_city]")
                    .val(this.town)
                    .select2({
                        dropdownParent: inpostModal,
                        language: Mall.reg.get("localeCode")
                    });
                jQuery(".shipping_select_point_data").html("");

                searchOnMap(this.town, this.name);
            }

            var inpostModal = jQuery(".carrier-points-modal[data-carrier-points='ghinpost']");
            jQuery("select[name=shipping_select_point]").val(this.name);
            if (!jQuery("select[name=shipping_select_point]").hasClass("onchange_shipping_select_point")) {
                jQuery("select[name=shipping_select_point]")
                    .select2({
                        dropdownParent: inpostModal,
                        language: Mall.reg.get("localeCode")
                    });
            }
            jQuery("[name=shipping_select_point]").removeClass("onchange_shipping_select_point");


            if(data.length > 10){
                zoomOnShowPoint = zoomOnShowPointBigCities;
            }
            map.setCenter(this.getPosition());
            map.setZoom(((map.getZoom() > zoomOnShowPoint) ? map.getZoom() : zoomOnShowPoint));

            infowindow.open(map, this);

            jQuery(".nearest_stores_container_list").hide();
            jQuery(".nearest_stores_container_link").text(Mall.translate.__("shipping_map_show_nearest_link"));
        });

        //Selected city case
        if (typeof filteredData !== "undefined") {
            if (window.innerWidth < Mall.Breakpoint.sm) {
                map.setZoom(zoomOnShowCity);
            } else {
                map.setZoom(zoomOnShowCityMobile);
            }
            map.setCenter(new google.maps.LatLng(filteredData[0].latitude,filteredData[0].longitude));
        }

        markers.push(marker);
        gmarkers.push(marker);
        gmarkersNameRelation[pos.name] = i;

    }
    //--setMarkers
    var markerClusterOptions = {
        maxZoom: 10,
        gridSize: 14,
        styles: clusterStyles
    };

    markerClusterer = new MarkerClusterer(map, markers, markerClusterOptions);

    //Jeśli w mieście jest tylko jeden paczkomat, niech wybiera go automatycznie
    if(pointsCount === 1){
        showMarkerOnMap(filteredData[0].name);
    }
}

function showMarkerOnMap(name) {
    markerId = parseInt(gmarkersNameRelation[name]);
    if(typeof gmarkers[markerId] !== "undefined"){
        google.maps.event.trigger(gmarkers[markerId], "click");
    }
}

function resizeMapMobile(){
    if (map === null)
        return;
    setTimeout(function () {
        resizingMapMobile();
    }, 200);
}

function resizingMapMobile() {
    if (map === null)
        return;

    var center = map.getCenter();
    google.maps.event.trigger(map, "resize");
    map.setCenter(center);

}

function resizeMap(point) {
    if (map === null)
        return;
    setTimeout(function () {
        resizingMap(point);
    }, 400);
}
function resizingMap(point) {
    if (map === null)
        return;

    var center = map.getCenter();
    google.maps.event.trigger(map, "resize");
    map.setCenter(center);

    //Show on map session paczkomat
    if(typeof window.geoposition === "undefined"){
        Mall.Cart.Shipping.attachShowOnMapSavedInSessionPoint();
    }

    //Show on map session paczkomat
    if(typeof point !== "undefined"){
        showMarkerOnMap(point);
    }

}
//GEO
function showPositionInPost(position) {
    //Try to find in 30 km
    var closestStores = calculateTheNearestStoresInPost(position, minDist, false);

    //Try to find in 100 km
    if (closestStores.length <= 0) {
        closestStores = calculateTheNearestStoresInPost(position, minDistFallBack, true);
    }
    closestStores.sort(sortByDirection);
    closestStores = closestStores.slice(0,3);

    buildStoresList(closestStores);
    nearestStores = closestStores;

    if (jQuery("[name=shipping_select_city]").val().length === 0) {
        refreshMapInPost([], nearestStores);
        jQuery("#map_delivery")
            .css({"visibility": "visible", "display": "block"});
    }

}

//Get the latitude and the longitude;
function successGeolocationFunctionInPost(position) {
    window.geoposition = position;
    showPositionInPost(window.geoposition);
    //Show on map session paczkomat
    Mall.Cart.Shipping.attachShowOnMapSavedInSessionPoint();

}

function handleGeoLocationInPost() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(successGeolocationFunctionInPost);
    }
}

function calculateTheNearestStoresInPost(position, minDistance, fallback) {
    // find the closest location to the user's location
    var pos;
    var closestStores = [];

    for (var i = 0; i < inPostPoints.length; i++) {
        pos = inPostPoints[i];
        // get the distance between user's location and this point
        var dist = MapsHelper.Haversine(inPostPoints[i].latitude, inPostPoints[i].longitude, position.coords.latitude, position.coords.longitude);

        // check if this is the shortest distance so far
        if (dist < minDistance) {
            inPostPoints[i].distance = dist;
            inPostPoints[i].nearest = 1;
            closestStores.push(inPostPoints[i]);
        }
    }

    return closestStores;
}
//sort by distance
function sortByDirection(a, b) {
    return ((a.distance < b.distance) ? -1 : ((a.distance > b.distance) ? 1 : 0));
}
//--GEO


function formatDetailsContent(pos) {
    var payment_point_description = "";
    if(typeof (pos.payment_point_description) !== "undefined"
        && pos.payment_point_description.length > 0
    ){
        payment_point_description = "<div><span><i class='fa fa-credit-card fa-1x'></i> " +pos.payment_point_description+ "</span></div>";
    }
    var pos_additional =
        '<div>' + pos.street + ' ' + pos.building_number + '</div>' +
        '<div>' + pos.postcode + ' ' + pos.town + '</div>' +
        '<div>(' + pos.location_description + ')</div>';

    return '<div class="row">' +
        '<div class="col-sm-6">' +
        '<div><b>' + pos.street + ' ' + pos.building_number + '</b></div>' +
        '<div>' + pos.postcode + ' ' + pos.town + '</div>' +
        '<div>(' + pos.location_description + ')</div>'+ payment_point_description+
        '</div>' +
        '<div class="col-sm-6 pointselect-container">' +
        '<a class="button button-primary pointselect" data-select-shipping-method-trigger="1" data-carrier-town="' + pos.town + '" data-carrier-pointid="' +pos.id+ '" data-carrier-pointcode="' +pos.name+ '" data-carrier-additional="' + pos_additional + '" href="">' + Mall.translate.__("shipping_map_method_select") + '</a>' +
        '</div>' +
        '</div>';
}

function formatInfoWindowContent(pos) {
    var payment_point_description = "";
    if(typeof (pos.payment_point_description) !== "undefined"
        && pos.payment_point_description.length > 0
    ){
        payment_point_description = "<div><span><i class='fa fa-credit-card fa-1x'></i> " +pos.payment_point_description+ "</span></div>";
    }

    var pos_additional =
        '<div>' + pos.street + ' ' + pos.building_number + '</div>' +
        '<div>' + pos.postcode + ' ' + pos.town + '</div>';

    return '<div class="delivery-marker-window">' +
        '<div class="info_window_text">' +
        '<div class="additional-store-information"><b>' + pos.street + ' ' + pos.building_number + '</b></div>' +
        '<div class="additional-store-information"><b>' + pos.postcode + ' ' + pos.town + '</b></div>' +
        '<div class="additional-store-information">' + pos.location_description + '</div>' +
        '<div><a class="button button-primary pointselect" data-select-shipping-method-trigger="1" data-carrier-pointid="' + pos.id + '" data-carrier-pointcode="' + pos.name + '" data-carrier-town="' + pos.town + '" data-carrier-additional="' + pos_additional + '" href="">'+Mall.translate.__("shipping_map_method_select")+'</a></div>' +
        '</div>' +
        '</div>';
}


function buildStoresList(points) {
    var searchByMapList = jQuery(".nearest_stores_container");

    var list = "";
    var pos, posId;
    var nearest_stores_container_list_style = "";
    if (typeof jQuery("[name=shipping_point_code]").val() !== "undefined"
        && jQuery("[name=shipping_point_code]").val().length > 0) {
        nearest_stores_container_list_style = "display:none;";
    }
    if (points.length > 0) {
        list += "<div class='nearest_stores_container_list' style='"+nearest_stores_container_list_style+"'>";
        list += "<h3>"+Mall.translate.__("shipping_map_nearest")+"</h3>";
        for (var i = 0; i < points.length; i++) {
            pos = points[i];
            posId = pos.id;
            list += formatDetailsContent(pos);
        }
        list += "</div>";
        if (nearest_stores_container_list_style === "") {
            list += "<div><a class='nearest_stores_container_link' href=''>"+Mall.translate.__("shipping_map_hide_nearest_link")+"</a></div>";
        } else {
            list += "<div><a class='nearest_stores_container_link' href=''>"+Mall.translate.__("shipping_map_show_nearest_link")+"</a></div>";
        }

    }
    searchByMapList.html(list);
}


function searchOnMap(q, markerToShow) {
    _makeMapRequestInPost(q, markerToShow);
}

function _makeMapRequestInPost(q, markerToShow) {

    jQuery.ajax({
        url: "/modago/inpost/getPopulateMapData",
        type: "POST",
        data: {town: q},
        success: function (response) {
            gmarkers = [];  //to collect only filtered markers (used in showMarkerWindow)
            data = jQuery.parseJSON(response);

            var pointsOnMap = data.map_points;

            constructShippingPointSelectInPost(pointsOnMap);
            refreshMapInPost(pointsOnMap, nearestStores);
            jQuery("#map_delivery").css({"visibility": "visible", "display": "block"});


            if(Mall.getIsBrowserMobile()){
                jQuery(".map_delivery_container_wrapper .map_delivery_container_show_up")
                    .html('<a href="" class="map_delivery_container_show">'+Mall.translate.__("shipping_map_show_map_link")+'</a>');
            }

            if(markerToShow){
                //Show session point
                showMarkerOnMap(markerToShow);
            }

        },
        error: function (response) {
            console.log(response);
        }
    });
}

function constructShippingPointSelectInPost(map_points) {
    var options = [],
        map_point_long_name;

    options.push('<option value="">'+Mall.translate.__("shipping_map_select_locker")+'</option>');
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

    } else {
        jQuery("select[name=shipping_select_point]")
            .html(options.join(""))
            .attr("disabled", false)
            .val("");
    }

    var inpostModal = jQuery(".carrier-points-modal[data-carrier-points='ghinpost']");
    jQuery("select[name=shipping_select_point]")
        .select2({dropdownParent: inpostModal, language: Mall.reg.get("localeCode")});
}

function clearClusters(e) {
    e.preventDefault();
    e.stopPropagation();
    markerClusterer.clearMarkers();
}



jQuery(document).ready(function () {
    Mall.Cart.Shipping.init();

    jQuery("[name=shipping_select_city]").change(function () {
        var enteredSearchValue = jQuery("[name=shipping_select_city] option:selected").val();

        if (enteredSearchValue !== "undefined") {
            jQuery(".shipping_select_point_data").html("");

            searchOnMap(enteredSearchValue);
        }

    });
});