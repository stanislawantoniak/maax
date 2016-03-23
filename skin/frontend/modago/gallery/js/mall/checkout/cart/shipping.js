(function () {
    "use strict";

    Mall.Cart.Shipping = {
        form_id: "cart-shipping-methods-form",
        content: "#cart-shipping-methods",
        markerClusterer: null,
        map: null,
        infowindow: null,
        markers: [],
        gmarkers: [],
        defaultCenterLang: 52.42997,
        defaultCenterLat: 19.46633,
        init: function () {
            var self = this;

            Mall.Cart.Shipping.updateTotals();

            jQuery(document).delegate("[data-select-shipping-method-trigger=1]", "click", function (e) {
                self.handleShippingMethodSelect(e);
            });


            jQuery("[data-select-shipping-method-trigger=0]").click(function (e) {
                //1. populate popup
                jQuery("#select_inpost_point").modal("show");
            });

            jQuery(".shipping_method_link").click(function (e) {
                e.preventDefault();
                jQuery(this).find("input[name=_shipping_method]").prop("checked", true);
            });

            if (jQuery("#cart-shipping-methods [name=_shipping_method]").length == 1) {
                jQuery("#cart-shipping-methods [name=_shipping_method]").click();
            }


            jQuery("#change-shipping-type").click(function () {
                jQuery(".shipping-method-selector").slideDown();
                jQuery(".shipping-method-selected").slideUp();
            });

            jQuery('#select_inpost_point').on('show.bs.modal', function () {
                //Must wait until the render of the modal appear, thats why we use the resizeMap and NOT resizingMap!! ;-)
                Mall.Cart.Shipping.initializeMap();
                Mall.Cart.Shipping.resizeMap();
                var shippingPointCode = jQuery("[name=shipping_point_code]").val();
                if (jQuery.type(shippingPointCode) !== "undefined") {
                    Mall.Cart.Shipping.showMarkerWindow(shippingPointCode);
                }
            });
            jQuery('#select_inpost_point').on('hide.bs.modal', function () {
                //Clear markers
                Mall.Cart.Shipping.markers = [];
                Mall.Cart.Shipping.gmarkers = [];
                Mall.Cart.Shipping.markerClusterer = null;
            });

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

            if (jQuery(e.target).is("a")) {
                e.preventDefault();
                jQuery("#select_inpost_point").modal("hide");
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

            if (jQuery.type(shipping) !== "undefined") {
                var inputs = '';
                jQuery.each(vendors, function (i, vendor) {
                    inputs += '<input type="hidden" name="shipping_method[' + vendor + ']" value="' + shipping + '" required="required" />';
                });
                inputs += '<input type="hidden" data-id="' + pointId + '" name="shipping_point_code" value="' + pointCode + '"  />';

                content.find("form .shipping-collect").html(inputs);
            }

            Mall.Cart.Shipping.appendSelectedCartShipping(selectedMethodData);


            var formData = jQuery("#cart-shipping-methods-form").serializeArray();
            //console.log(formData);

            jQuery.ajax({
                url: jQuery("#cart-shipping-methods-form").attr("action"),
                type: "POST",
                data: formData
            }).done(function (response) {
                //console.log(response);
            });

            Mall.Cart.Shipping.updateTotals();
        },


        initializeMap: function () {
            //1. Get block
            var deliveyType = Mall.Cart.Shipping.getSelectedShipping().attr("data-carrier-delivery-type");

            var inpostPoints = Mall.reg.get("inpost_points");

            if(inpostPoints.length == 0){
                return;
            }

            var mapData = jQuery.parseJSON(Mall.reg.get("inpost_points"));

            google.maps.event.addDomListener(window, 'load', mapData);
            google.maps.event.addDomListener(window, "resize", Mall.Cart.Shipping.resizingMap());


            var mapOptions = {
                zoom: 6,
                center: new google.maps.LatLng(Mall.Cart.Shipping.defaultCenterLang, Mall.Cart.Shipping.defaultCenterLat),
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


            Mall.Cart.Shipping.map = new google.maps.Map(document.getElementById('map_delivery_cluster'), mapOptions);

            Mall.Cart.Shipping.infowindow = new google.maps.InfoWindow({
                //pixelOffset: new google.maps.Size(0, 5),
                buttons: {close: {show: 0}}
            });


            //I will show all the points on the map first
            Mall.Cart.Shipping.refreshMap(mapData);


        },
        refreshMap: function (data) {

            //var imageUrl = 'http://chart.apis.google.com/chart?cht=mm&chs=24x32&chco=FFFFFF,008CFF,000000&ext=.png';
            var imageUrl = 'http://chart.apis.google.com/chart?cht=mm&chs=24x32&chco=ffffff,000000,000000&ext=.png';

//            Mall.Cart.Shipping.markers = [];
            if (Mall.Cart.Shipping.markerClusterer) {
                Mall.Cart.Shipping.markerClusterer.clearMarkers();
            }

            var markerImage = new google.maps.MarkerImage(imageUrl, new google.maps.Size(40, 40));

            //setMarkers
            for (var i = 0; i < data.length; i++) {
                var pos = data[i];

                var posLatLng = new google.maps.LatLng(pos.latitude, pos.longitude);
                var marker = new google.maps.Marker({
                    id: pos.id,
                    name: pos.name,
                    position: posLatLng,
                    map: Mall.Cart.Shipping.map,
                    icon: markerImage,
                    html: Mall.Cart.Shipping.formatInfoWindowContent(pos),
                    latitude: pos.latitude,
                    longitude: pos.longitude
                });

                var contentString = " ";

                google.maps.event.addListener(marker, "click", function () {
                    Mall.Cart.Shipping.infowindow.setContent(this.html);
                    Mall.Cart.Shipping.infowindow.open(Mall.Cart.Shipping.map, this);

                    // set map center to marker position
                    Mall.Cart.Shipping.map.setCenter(this.getPosition());
                    //call smoothZoom, parameters map, final zoomLevel, and starting zoom level
                    Mall.Cart.Shipping.smoothZoom(Mall.Cart.Shipping.map, 15, Mall.Cart.Shipping.map.getZoom());
                });

                Mall.Cart.Shipping.markers.push(marker);
                Mall.Cart.Shipping.gmarkers.push(marker);

            }

            //--setMarkers
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
            var markerClusterOptions = {
                maxZoom: 13,
                gridSize: 35,
                styles: clusterStyles
            };

            Mall.Cart.Shipping.markerClusterer = new MarkerClusterer(Mall.Cart.Shipping.map, Mall.Cart.Shipping.markers, markerClusterOptions);
        },
        formatInfoWindowContent: function (pos) {
            return '<div class="delivery-marker-window">' +
                    '<div class="info_window_text"><p></p>' +
                        '<div class="additional-store-information"><b>' + pos.street + ' ' + pos.building_number + '</b></div>' +
                        '<div class="additional-store-information"><b>' + pos.postcode + ' ' + pos.town + '</b></div>' +
                        '<div class="additional-store-information"><p>' + pos.location_description + '</p></div>' +
                        '<div><a class="button button-third small" data-select-shipping-method-trigger="1" data-carrier-pointid="' + pos.id + '" data-carrier-pointcode="' + pos.name + '" data-carrier-additional="' + pos.additional + '" href="">wybierz ten adres</a></div>'
                    '</div>' +
                '</div>';
        },
        showMarkerWindow: function (name) {
            jQuery(Mall.Cart.Shipping.gmarkers).each(function (i, item) {
                if (name == item.name) {
                    google.maps.event.trigger(Mall.Cart.Shipping.gmarkers[i], "click");
                    return false;
                }
            });

        },
        resizeMap: function () {
            if (Mall.Cart.Shipping.map == null) return;
            setTimeout(function () {
                Mall.Cart.Shipping.resizingMap();
            }, 400);
        },
        resizingMap: function () {
            if (Mall.Cart.Shipping.map == null) return;
            var center = Mall.Cart.Shipping.map.getCenter();
            google.maps.event.trigger(Mall.Cart.Shipping.map, "resize");
            Mall.Cart.Shipping.map.setCenter(center);
        },
        smoothZoom: function (map, max, cnt) {
            if (cnt >= max) {
                return;
            }
            else {
                var y = google.maps.event.addListener(map, 'zoom_changed', function (event) {
                    google.maps.event.removeListener(y);
                    Mall.Cart.Shipping.smoothZoom(map, max, cnt + 1);
                });
                setTimeout(function () {
                    Mall.Cart.Shipping.map.setZoom(cnt)
                }, 80);
            }
        }
    }
})();


jQuery(document).ready(function () {
    Mall.Cart.Shipping.init();

    jQuery("#cart-buy").on('click', function (e) {
        jQuery(this).find('i').addClass('fa fa-spinner fa-spin');
    });

});








