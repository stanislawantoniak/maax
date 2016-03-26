(function () {
    "use strict";

    Mall.Cart.Shipping = {
        form_id: "cart-shipping-methods-form",
        content: "#cart-shipping-methods",
        init: function () {
            var self = this;
            self.updateTotals();

            jQuery(document).delegate("input[data-select-shipping-method-trigger=1]",
                "change",
                function (e) {
                    self.handleShippingMethodSelect(e);
                });
            jQuery(document).delegate("a[data-select-shipping-method-trigger=1]",
                "click",
                function (e) {
                    self.handleShippingMethodSelect(e);
                });

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
        handleShippingMethodSelect: function (e) {
            Mall.Cart.Shipping.setShippingMethod(e.target);

            if (jQuery(e.target).is("a") || jQuery(e.target).is("option")) {
                e.preventDefault();
                jQuery("#select_inpost_point").modal("hide");
            }
        },
        implementMapSelections: function () {
            var self = this;
            if (jQuery("input[data-select-shipping-method-trigger=0]").length == 0)
                return;

            handleGeoLocation();

            jQuery("[name=shipping_select_point]").change(function () {
                var selectedPoint = jQuery("[name=shipping_select_point] option:selected");

                if (typeof selectedPoint.val() !== "undefined"
                    && selectedPoint.val().length > 0) {
                    showMarkerOnMap(selectedPoint.attr("data-carrier-pointcode"));
                }
            });
            if (Mall.getIsBrowserMobile()) {
                jQuery('#select_inpost_point .select2').on('select2:open', function (e) {
                    jQuery('.select2-search input').prop('focus', false);
                });
            }
            jQuery("[data-select-shipping-method-trigger=0]").change(function (e) {
                //1. populate popup
                jQuery("#select_inpost_point").modal("show");
            });
            jQuery("[name=shipping_select_city]").select2({
                placeholder: "Wybierz miasto",
                dropdownParent: jQuery("#select_inpost_point")
            });
            jQuery("[name=shipping_select_point]").select2({
                placeholder: "Wybierz paczkomat",
                dropdownParent: jQuery("#select_inpost_point")
            });
            jQuery("[name=shipping_select_city]")
                .val("")
                .select2({
                    dropdownParent: jQuery("#select_inpost_point")
                });
            jQuery("[name=shipping_select_point]")
                .attr("disabled", true)
                .val("")
                .select2({
                    dropdownParent: jQuery("#select_inpost_point")
                });
            //Show on map session paczkomat
            self.attachShowOnMapSavedInSessionPoint();
            //Show on map session paczkomat

            jQuery('#select_inpost_point').on('show.bs.modal', function () {
                //Must wait until the render of the modal appear,
                // that's why we use the resizeMap and NOT resizingMap!! ;-)
                var sessionPoint = jQuery("[name=shipping_point_code]");
                resizeMap(sessionPoint.val());

            });
            jQuery('#select_inpost_point').on('hide.bs.modal', function () {
                //If inPost selected but paczkomat not selected
                if (jQuery("#cart-shipping-methods input[data-select-shipping-method-trigger=0]:checked").length > 0
                    && (typeof(jQuery("#cart-shipping-methods input[name=shipping_point_code]").val()) == "undefined")
                ) {
                    //Clear selected shipping
                    jQuery("[name=_shipping_method]").prop("checked", false);
                }
            });
            self.attachShowHideMapOnMobile();

        },
        attachShowOnMapSavedInSessionPoint: function(){
            var sessionPoint = jQuery("[name=shipping_point_code]");
            var sessionPointTown;
            if(sessionPoint.val()){
                sessionPointTown = sessionPoint.attr("data-town");

                jQuery(".shipping_select_point_data").html("");
                jQuery("[name=shipping_select_city]")
                    .val(sessionPointTown)
                    .select2({dropdownParent: jQuery("#select_inpost_point")});
                searchOnMap(sessionPointTown, sessionPoint.val());
            }
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
            var totalSum = parseInt(parseInt(Mall.reg.get("quote_products_total")) + parseInt(shippingCost) + -parseInt(Mall.reg.get("quote_discount_total")));
            jQuery("#sum_price .value_sum_price").html(Mall.currency(totalSum));
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
                    _shipping_method: {required:Mall.reg.get("validation_please_select_shipping")},

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
                if(!jQuery("#cart-shipping-methods-form").valid()){
                    return false;
                }
                jQuery(this).find('i').addClass('fa fa-spinner fa-spin');
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
                    resizeMap();
                    jQuery(this).text('schowaj mapę');
                    if (jQuery('.map_delivery_container').is(':visible')) {
                        jQuery(this).text('pokaż mapę');
                    } else {
                        jQuery(this).text('schowaj mapę');
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
            //console.log(formData);

            jQuery.ajax({
                url: "/checkout/singlepage/saveBasketShipping/",
                type: "POST",
                data: formData
            }).done(function (response) {
                //console.log(response);
            });

            Mall.Cart.Shipping.updateTotals();
        }
    }
})();



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

    if(typeof point !== "undefined"){
        showMarkerOnMap(point);
    }


}
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

google.maps.event.addDomListener(window, 'load', initialize);
google.maps.event.addDomListener(window, "resize", resizingMap());

var defaultCenterLang = 52.4934482;
var defaultCenterLat = 18.8979594;
var defaultCenterLangMobile = 51.7934482;
var defaultCenterLatMobile = 18.8979594;

var minDist = 30; //km
var minDistFallBack = 100; //km
var closestStores = [];

var gmarkers = [];
var gmarkersNameRelation = [];





//Get the latitude and the longitude;
function successFunction(position) {
    window.geoposition = position;
}

function handleGeoLocation(){
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(successFunction);
    }

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function (position) {
                //If you allow to see your location, you will see all the nearest stores
                showPosition(position);
            },
            function (error) {
                //If you deny to see your location, you will see all the stores
                if (error.code == error.PERMISSION_DENIED) {
                }

            });
    } else {
        //Your browser doesn't support GEO location, you will see all the stores
    }
}


function initialize() {

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
        //mapOptions.zoom = 5;
        //mapOptions.center = new google.maps.LatLng(defaultCenterLangMobile, defaultCenterLatMobile);
        mapOptions.zoomControlOptions.position = google.maps.ControlPosition.RIGHT_CENTER;
        mapOptions.zoomControlOptions.style = google.maps.ZoomControlStyle.SMALL;
        mapOptions.panControl = false;
    }

    map = new google.maps.Map(document.getElementById('map_delivery'), mapOptions);

    infowindow = new google.maps.InfoWindow({
        //pixelOffset: new google.maps.Size(0, 5),
        buttons: {close: {show: 0}}
    });
    data = []; //No city no points
}

//GEO
function showPosition(position) {

    //Try to find in 30 km
    var closestStores = calculateTheNearestStores(position, minDist, false);
    console.log(closestStores.length);
    //Try to find in 100 km
    if (closestStores.length <= 0) {
        closestStores = calculateTheNearestStores(position, minDistFallBack, true);
    } else {
        showLabel(".the-nearest-stores");
        showLabel("a.stores-map-show-all");
    }

    if (closestStores.length <= 0) {
        closestStores = inPostPoints;
    } else {
        showLabel(".the-nearest-stores");
        showLabel("a.stores-map-show-all");
    }
    closestStores.sort(sortByDirection);
    closestStores = closestStores.slice(0,3);
    console.log(closestStores.slice(0,3));
    buildStoresList(closestStores, position);
}
function calculateTheNearestStores(position, minDistance, fallback) {
    // find the closest location to the user's location
    var pos;

    //console.log(minDistance);
    for (var i = 0; i < inPostPoints.length; i++) {
        pos = inPostPoints[i];
        // get the distance between user's location and this point
        var dist = Haversine(inPostPoints[i].latitude, inPostPoints[i].longitude, position.coords.latitude, position.coords.longitude);

        // check if this is the shortest distance so far
        if (dist < minDistance) {
            inPostPoints[i].distance = dist;
            closestStores.push(inPostPoints[i]);

            if (fallback && closestStores.length >= 3) {
                break;
            }

        }
    }




    return closestStores;
}
//sort by distance
function sortByDirection(a, b) {
    return ((a.distance < b.distance) ? -1 : ((a.distance > b.distance) ? 1 : 0));
}
//--GEO


function refreshMap(filteredData) {

    //var imageUrl = 'http://chart.apis.google.com/chart?cht=mm&chs=24x32&chco=FFFFFF,008CFF,000000&ext=.png';
    //var imageUrl = 'http://chart.apis.google.com/chart?cht=mm&chs=24x32&chco=ffffff,000000,000000&ext=.png';
    var imageUrl = "/js/gh/storemap/cluster_icons/circle4.png";
    //var imageSelectedUrl = 'http://chart.apis.google.com/chart?cht=mm&chs=24x32&chco=ffffff,d20005,d20005&ext=.png';
    var imageSelectedUrl = "http://chart.apis.google.com/chart?cht=mm&chs=24x32&chco=ffffff,000000,000000&ext=.png";

    if (typeof filteredData !== "undefined")
        data = filteredData;

    var markers = [];
    if (markerClusterer) {
        markerClusterer.clearMarkers();
    }

    var markerImage = new google.maps.MarkerImage(imageUrl,
        new google.maps.Size(40, 40));

    console.log(data.length);



    //setMarkers
    for (var i = 0; i < data.length; i++) {
        var pos = data[i];

        var posLatLng = new google.maps.LatLng(pos.latitude, pos.longitude);
        var marker = new google.maps.Marker({
            id: pos.id,
            name: pos.name,
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

            jQuery("select[name=shipping_select_point]")
                .val(this.name)
                .select2("destroy")
                .select2({dropdownParent: jQuery("#select_inpost_point")});

            if(data.length > 10){
                zoomOnShowPoint = zoomOnShowPointBigCities;
            }
            map.setCenter(this.getPosition());
            map.setZoom(((map.getZoom() > zoomOnShowPoint) ? map.getZoom() : zoomOnShowPoint));
            for (var i=0; i<gmarkers.length; i++) {
                gmarkers[i].setIcon(imageUrl);
            }
            this.setIcon(imageSelectedUrl);
            //infowindow.open(map, this);

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
}
// the smooth zoom function
function smoothZoom(map, max, cnt) {

    if (cnt >= max) {
        return;
    }
    else {
        y = google.maps.event.addListener(map, 'zoom_changed', function (event) {
            google.maps.event.removeListener(y);
            smoothZoom(map, max, cnt + 1);
        });
        setTimeout(function () {
            map.setZoom(cnt)
        }, 80);
    }
}

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
        '<div class="col-sm-6">' +
        '<a class="btn button-third reverted" data-select-shipping-method-trigger="1" data-carrier-town="' + pos.town + '" data-carrier-pointid="' +pos.id+ '" data-carrier-pointcode="' +pos.name+ '" data-carrier-additional="' + pos_additional + '" href="">wybierz</a>' +
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
        '<div>' + pos.postcode + ' ' + pos.town + '</div>' +
        '<div>(' + pos.location_description + ')</div>'+ payment_point_description+ '</div>';

    return '<div class="delivery-marker-window">' +
        '<div class="info_window_text">' +
        '<div class="additional-store-information"><b>' + pos.street + ' ' + pos.building_number + '</b></div>' +
        '<div class="additional-store-information"><b>' + pos.postcode + ' ' + pos.town + '</b></div>' +
        '<div class="additional-store-information">' + pos.location_description + '</div>' +
        '<div><a class="btn button-third reverted" data-select-shipping-method-trigger="1" data-carrier-pointid="' + pos.id + '" data-carrier-pointcode="' + pos.name + '" data-carrier-additional="' + pos_additional + '" href="">wybierz</a></div>' +
        '</div>' +
        '</div>';
}


function buildStoresList(points, position) {
    var searchByMapList = jQuery(".nearest_stores_container");

    var list = "";
    var pos, posId;

    if (points.length > 0) {
        list += "<h3>Najbliższe</h3>";
        for (var i = 0; i < points.length; i++) {
            pos = points[i];
            posId = pos.id;
            list += formatDetailsContent(pos);
        }
    }
    searchByMapList.html(list);
}
function showMarkerOnMap(name) {
    markerId = parseInt(gmarkersNameRelation[name]);

    if(typeof gmarkers[markerId] !== "undefined"){
        google.maps.event.trigger(gmarkers[markerId], "click");
    }
}


function searchOnMap(q, markerToShow) {
    _makeMapRequest(q, markerToShow);
}
function clearSearchOnMap() {
    _makeMapRequest(0)
}

function _makeMapRequest(q, markerToShow) {

    jQuery.ajax({
        url: "/modago/inpost/getPopulateMapData",
        type: "POST",
        data: {town: q},
        success: function (data) {
            //console.log(data);
            gmarkers = [];  //to collect only filtered markers (used in showMarkerWindow)
            data = jQuery.parseJSON(data);

            refreshMap(data.map_points);
            jQuery("#map_delivery").css({"visibility": "visible", "display": "block"});

            constructShippingPointSelect(data.map_points);
            //buildStoresList(data);

            if(Mall.getIsBrowserMobile()){
                jQuery(".map_delivery_container_wrapper .map_delivery_container_show_up")
                    .html('<a href="" class="map_delivery_container_show">pokaż mapę</a>');
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

function constructShippingPointSelect(map_points) {
    var options = [],
        map_point_long_name;

    options.push('<option value="">wybierz paczkomat</option>');
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

        showMarkerOnMap(map_points[0].name);
    } else {
        jQuery("select[name=shipping_select_point]")
            .html(options.join(""))
            .attr("disabled", false)
            .val("");
    }






    jQuery("select[name=shipping_select_point]")
        .select2({dropdownParent: jQuery("#select_inpost_point")});

}

function clearClusters(e) {
    e.preventDefault();
    e.stopPropagation();
    markerClusterer.clearMarkers();
}

//GEO helpers
// Convert Degress to Radians
function Deg2Rad(deg) {
    return deg * Math.PI / 180;
}

// Get Distance between two lat/lng points using the Haversine function
// First published by Roger Sinnott in Sky & Telescope magazine in 1984 (“Virtues of the Haversine”)
//
function Haversine(lat1, lon1, lat2, lon2) {
    var R = 6372.8; // Earth Radius in Kilometers

    var dLat = Deg2Rad(lat2 - lat1);
    var dLon = Deg2Rad(lon2 - lon1);

    var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(Deg2Rad(lat1)) * Math.cos(Deg2Rad(lat2)) *
        Math.sin(dLon / 2) * Math.sin(dLon / 2);
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    var d = R * c;

    // Return Distance in Kilometers
    return d;
}
//--GEO helpers

function showLabel(label) {
    jQuery(label).removeClass("hidden");
}
function hideLabel(label) {
    jQuery(label).addClass("hidden");
}

jQuery(document).ready(function () {
    Mall.Cart.Shipping.init();

    jQuery("[name=shipping_select_city]").change(function () {
        var enteredSearchValue = jQuery("[name=shipping_select_city] option:selected").val();
        jQuery(".shipping_select_point_data").html("");
        searchOnMap(enteredSearchValue);

    });
});