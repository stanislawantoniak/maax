(function(){
    Mall.Cart.Shipping = {
        form_id: "cart-shipping-methods-form",
        content: "#cart-shipping-methods",

        init: function () {
            Mall.Cart.Map.deliverySet = Mall.reg.get("deliverySet");

            var self = this;

            for (var e in Mall.Cart.Map.deliverySet) {
                if (!sessionStorage.getItem(e)) {
                    jQuery.ajax({
                        url: Mall.Cart.Map.deliverySet[e].urlData,
                        type: "POST",
                        success: (function (response) {
                            sessionStorage.setItem(this.e, response);

                            Mall.Cart.Map.deliverySet[this.e].mapPoints =
                                JSON.parse(sessionStorage.getItem(this.e)).map_points;
                        }).bind({e: e})
                    });
                } else {
                    Mall.Cart.Map.deliverySet[e].mapPoints =
                        JSON.parse(sessionStorage.getItem(e)).map_points;
                }
            }

            self.carrierPoint = Object.keys(Mall.Cart.Map.deliverySet)[0];

            self.updateTotals();

            self.handleShippingMethodSelect();

            self.attachShippingFormValidation();

            var testPointsData = self.analizeMapPoints();

            jQuery("#change-shipping-type").click(function () {
                jQuery(".shipping-method-selector").slideDown();
                jQuery(".shipping-method-selected").slideUp();

                //Clear selected shipping
                jQuery("[name=_shipping_method]").prop("checked", false);
            });

            jQuery(".data_shipping_item").click(function(){
                Mall.Cart.Shipping.carrierPoint = jQuery(this).find("input[name=_shipping_method]").attr("data-carrier-delivery-type");

                var carrierMapPointsData = Mall.Cart.Map.deliverySet[Mall.Cart.Shipping.carrierPoint];

                if (carrierMapPointsData) {
                    var carrierMapPoints = carrierMapPointsData.mapPoints;
                    if (carrierMapPoints){

                        var testPoints = (typeof testPointsData[Mall.Cart.Shipping.carrierPoint] !== "undefined") ? testPointsData[Mall.Cart.Shipping.carrierPoint] : true;

                        if (testPoints) {

                            jQuery(".shipping_select_point_data").html("");
                            jQuery("[name=shipping_point_code]").val("");
                            jQuery("[name=shipping_point_code]").attr("data-id", "");
                            jQuery("[name=shipping_point_code]").attr("data-town", "");

                            var inpostModal = jQuery(".carrier-points-modal[data-carrier-points='" + Mall.Cart.Shipping.carrierPoint + "']");


                            if (!Mall.getIsBrowserMobile()) {
                                inpostModal.find("[name=shipping_select_city]").val("").select2({
                                    placeholder: Mall.translate.__("shipping_map_select_city"),
                                    dropdownParent: inpostModal,
                                    language: Mall.reg.get("localeCode")
                                });

                                inpostModal.find("[name=shipping_select_point]")
                                    .attr("disabled", true)
                                    .val("")
                                    .select2({
                                        dropdownParent: inpostModal,
                                        language: Mall.reg.get("localeCode")
                                    });
                            } else {
                                inpostModal.find("[name=shipping_select_city]").val("");

                                inpostModal.find("[name=shipping_select_point]")
                                    .attr("disabled", true)
                                    .val("");
                            }


                        }

                        if (Object.keys(Mall.Cart.Map.deliverySet).length > 0) {

                            Mall.Cart.Map.initMap();

                            self.implementMapSelections(false);

                            if (typeof jQuery("[name=shipping_point_code]").attr("data-town") !== "undefined")
                                Mall.Cart.Map.refreshMap(
                                    Mall.Cart.Map.deliverySet[Mall.Cart.Shipping.carrierPoint].mapPoints.filter(function (e) {
                                            if (e.town == jQuery("[name=shipping_point_code]").attr("data-town")) return 1;
                                        }
                                    ),
                                    Mall.Cart.Map.nearestStores
                                );
                            else
                                Mall.Cart.Map.refreshMap(
                                    Mall.Cart.Map.deliverySet[Mall.Cart.Shipping.carrierPoint].mapPoints,
                                    Mall.Cart.Map.nearestStores
                                );

                            Mall.Cart.Map.map.setZoom(5);
                            Mall.Cart.Map.map.setCenter({lat: 52.229818, lng: 21.011864});

                        }
                    }
                }

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

            jQuery("[name=shipping_select_city]").change(function () {
                var enteredSearchValue = jQuery(this).find("option:selected").val();

                if (enteredSearchValue !== "undefined") {
                    jQuery(".shipping_select_point_data").html("");

                    Mall.Cart.Map.searchOnMap(enteredSearchValue);
                }
            });

            self.implementMapSelections(true);

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

                if (Object.keys(Mall.Cart.Map.deliverySet).length > 0) Mall.Cart.Map.handleGeoLocation();
            });

            self.attachShowHideMapOnMobile();

            self.attachShowHideNearestPointsList();

            if (Object.keys(Mall.Cart.Map.deliverySet).length > 0) Mall.Cart.Map.initMap();
        },
        analizeMapPoints: function () {
            //Check all the map points and discover is there session deliveryPoint among them
            var testPointsResult = {};
            jQuery.each(Mall.Cart.Map.deliverySet,
                function (code, deliverySet) {
                    var carrierMapPointsData = Mall.Cart.Map.deliverySet[code];

                    if (carrierMapPointsData) {
                        var carrierMapPoints = carrierMapPointsData.mapPoints;
                        if (carrierMapPoints) {
                            var testPoints = true;

                            if (typeof jQuery("[name=shipping_point_code]").val() !== "undefined") {
                                testPoints = !carrierMapPoints.some(
                                    function (e) {
                                        return e.name == jQuery("[name=shipping_point_code]").val();
                                    }
                                );
                            }
                            testPointsResult[code] = testPoints;
                        }
                    }

                });

            return {};
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
        appendSelectedCartShipping: function (selectedMethodData) {

            var shippingMethodSelectedContainer = jQuery(".shipping-method-selected");

            shippingMethodSelectedContainer.find('[data-item="method"]').html(selectedMethodData["method"]);
            shippingMethodSelectedContainer.find('[data-item="description"]').html(selectedMethodData["description"]);
            shippingMethodSelectedContainer.find('[data-item="logo"]').html(selectedMethodData["logo"]);

            shippingMethodSelectedContainer.find('[data-item="additional"]').html(selectedMethodData["additional"]);


            jQuery(".shipping-method-selector").slideUp();
            jQuery(".shipping-method-selected").slideDown();
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
        handleShippingMethodSelect: function () {
            jQuery(document).delegate("input[data-select-shipping-method-trigger=1]",
                "change",
                function (e) {
                    Mall.Cart.Shipping.setShippingMethod(this);
                });
            jQuery(document).delegate("a[data-select-shipping-method-trigger=1]",
                "click",
                Mall.Cart.Shipping.selectMethod);
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

                var shippingCostLabel = jQuery(methodRadio).attr("data-method-cost-label");
                jQuery('#product_summary li[data-target="val_delivery_cost"]').find("span.val_delivery_cost").html(shippingCostLabel);

            } else {
                shippingCost = 0; //not selected yet
            }
            //Grand total
            var quote_products_total = (jQuery.type(Mall.reg.get("quote_products_total")) !== "undefined") ? Mall.reg.get("quote_products_total") :0;
            var quote_discount_total = (jQuery.type(Mall.reg.get("quote_discount_total")) !== "undefined") ? Mall.reg.get("quote_discount_total") :0;

            var totalSum = parseFloat(parseFloat(quote_products_total) + parseFloat(shippingCost) + parseFloat(quote_discount_total));
            jQuery("#sum_price .value_sum_price").html(Mall.currency(totalSum));
        },
        implementMapSelections: function (firstTime) {
            var self = this;
            if (jQuery("input[data-select-shipping-method-trigger=0]").length == 0)
                return;

            //Init map
            google.maps.event.addDomListener(window, 'load', Mall.Cart.Map.initMap);
            google.maps.event.addDomListener(window, "resize", Mall.Cart.Map.resizingMap);

            var inpostModal = jQuery(".carrier-points-modal[data-carrier-points='" + self.carrierPoint + "']");

            if (!Mall.getIsBrowserMobile()) {
                jQuery("[name=shipping_select_point]")
                    .select2({dropdownParent: inpostModal,language: Mall.reg.get("localeCode")})
                    .change(function () {
                        var el = jQuery(this), val = el.val();
                        el.addClass("onchange_shipping_select_point");
                        if (typeof val !== "undefined" && val.length > 0) {
                            Mall.Cart.Map.showMarkerOnMap(val);
                        }

                        jQuery(".nearest_stores_container_list").hide();
                        jQuery(".nearest_stores_container_link").text(Mall.translate.__("shipping_map_show_nearest_link"));

                    });
            } else {
                jQuery("[name=shipping_select_point]")
                    .change(function () {
                        var el = jQuery(this), val = el.val();
                        el.addClass("onchange_shipping_select_point");
                        if (typeof val !== "undefined" && val.length > 0) {
                            Mall.Cart.Map.showMarkerOnMap(val);
                        }

                        jQuery(".nearest_stores_container_list").hide();
                        jQuery(".nearest_stores_container_link").text(Mall.translate.__("shipping_map_show_nearest_link"));

                    });
            }

            if (Mall.getIsBrowserMobile()) {
                inpostModal.find('.select2').on('select2:open', function (e) {
                    jQuery('.select2-search input').prop('focus', false);
                });
            }
            if (!Mall.getIsBrowserMobile()) {
                jQuery("[name=shipping_select_city]").select2({
                    placeholder: Mall.translate.__("shipping_map_select_city"),
                    dropdownParent: inpostModal,
                    language: Mall.reg.get("localeCode")
                });
            }


            if (firstTime) {
                if (!Mall.getIsBrowserMobile()) {
                    jQuery("[name=shipping_select_point]")
                        .attr("disabled", true)
                        .val("")
                        .select2({
                            dropdownParent: inpostModal,
                            language: Mall.reg.get("localeCode")
                        });
                } else {
                    jQuery("[name=shipping_select_point]")
                        .attr("disabled", true)
                        .val("");
                }
            }


            inpostModal.on('show.bs.modal', function () {
                //Must wait until the render of the modal appear,
                // that's why we use the resizeMap and NOT resizingMap!! ;-)
                var sessionPoint = jQuery("[name=shipping_point_code]");

                Mall.Cart.Map.resizeMap(sessionPoint.val());

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
        },
        attachShowOnMapSavedInSessionPoint: function () {
            var sessionPoint = jQuery("[name=shipping_point_code]");

            var inpostModal = jQuery(".carrier-points-modal[data-carrier-points='" + Mall.Cart.Shipping.carrierPoint + "']");
            var sessionPointTown = '';

            if (sessionPointName) {
                sessionPointTown = sessionPoint.attr("data-town");

                jQuery(".shipping_select_point_data").html("");
                if(sessionPointTown.length > 0){
                    if (!Mall.getIsBrowserMobile()) {
                        jQuery("[name=shipping_select_city]")
                            .val(sessionPointTown)
                            .select2({dropdownParent: inpostModal, language: Mall.reg.get("localeCode")});
                    } else {
                        jQuery("[name=shipping_select_city]")
                            .val(sessionPointTown);
                    }

                    Mall.Cart.Map.searchOnMap(sessionPointTown, sessionPointName);
                }

            }

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
            if (Mall.getIsBrowserMobile())
                Mall.Cart.Map.makeMapInvisible();

            jQuery(document).delegate(".map_delivery_container_show",
                "click",
                function (e) {
                    e.preventDefault();
                    Mall.Cart.Map.resizeMapMobile();
                    jQuery(this).text(Mall.translate.__("shipping_map_hide_map_link"));
                    if (jQuery('.map_delivery_container').hasClass('map_delivery_container_visible')) {
                        jQuery(this).text(Mall.translate.__("shipping_map_show_map_link"));
                        Mall.Cart.Map.makeMapInvisible();
                    } else {
                        jQuery(this).text(Mall.translate.__("shipping_map_hide_map_link"));
                        Mall.Cart.Map.makeMapVisible();
                    }
                });
        },
        selectMethod: function (e) {
            e.preventDefault();
            var parentModal = jQuery(this).parents(".modal");
            var deliveryMethod = parentModal.attr("data-carrier-points");

            switch(deliveryMethod){
                case 'zolagopickuppoint':
                    parentModal.modal("hide");
                    var selectedOption = parentModal.find("[name=shipping_select_pos] option:selected");
                    Mall.Cart.Shipping.setShippingMethod(selectedOption);
                    break;
                case 'ghinpost':
                    Mall.Cart.Shipping.setShippingMethod(this);
                    parentModal.modal("hide");
                    Mall.Cart.Map.showMarkerOnMap(jQuery(e.target).attr("data-carrier-pointcode"));
                    break;
                case 'zospwr':
                    Mall.Cart.Shipping.setShippingMethod(this);
                    parentModal.modal("hide");
                    Mall.Cart.Map.showMarkerOnMap(jQuery(e.target).attr("data-carrier-pointcode"));
                    break;
                default:
                    Mall.Cart.Shipping.setShippingMethod(this);
                    parentModal.modal("hide");
                    Mall.Cart.Map.showMarkerOnMap(jQuery(e.target).attr("data-carrier-pointcode"));
                    break;
            }
            return false;
        }
    }

    Mall.Cart.Map = {
        clusterStyles: [
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
        ],
        markerClusterer: null,
        map: null,
        infowindow: null,

        defaultCenterLang: 52.4934482,
        defaultCenterLat: 18.8979594,

        minDist: 30, //km
        minDistFallBack: 100, //km
        nearestStores: [],

        gmarkers: [],
        gmarkersNameRelation: [],

        initMap: function() {
            this.map = null;
            this.gmarkers = [];
            this.gmarkersNameRelation = [];

            var mapOptions = {
                zoom: 8,
                center: new google.maps.LatLng(Mall.Cart.Map.defaultCenterLang, Mall.Cart.Map.defaultCenterLat),
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

            Mall.Cart.Map.map = new google.maps.Map(document.getElementById(Mall.Cart.Map.deliverySet[Mall.Cart.Shipping.carrierPoint].mapDelivery), mapOptions);

            Mall.Cart.Map.infowindow = new google.maps.InfoWindow({
                buttons: {close: {show: 0}}
            });
            Mall.Cart.Map.data = []; //No city no points
        },

        refreshMap: function(filteredData, nearestStores) {
            if (!Mall.Cart.Map.data) Mall.Cart.Map.data = [];

            var imageUrl = "/js/gh/gmap/icons/gmap_marker_black.png";

            if (typeof filteredData !== "undefined")
                Mall.Cart.Map.data = filteredData;

            //Points count (not including nearest)
            var pointsCount = Mall.Cart.Map.data.length;

            var markers = [];
            if (Mall.Cart.Map.markerClusterer) {
                Mall.Cart.Map.markerClusterer.clearMarkers();
            }

            var markerImage = new google.maps.MarkerImage(imageUrl,
                new google.maps.Size(40, 40));

            //setMarkers
            //Join nearest stores (if GEO localization on)
            if (nearestStores.length > 0) {
                for (var k = 0; k < nearestStores.length; k++) {
                    Mall.Cart.Map.data.push(nearestStores[k]);
                }
            }

            for (var i = 0; i < Mall.Cart.Map.data.length; i++) {
                var pos = Mall.Cart.Map.data[i];

                var posLatLng = new google.maps.LatLng(pos.latitude, pos.longitude);
                var marker = new google.maps.Marker({
                    id: pos.id,
                    name: pos.name,
                    town: pos.town,
                    nearest: pos.nearest,
                    position: posLatLng,
                    map: Mall.Cart.Map.map,
                    icon: markerImage,
                    html: Mall.Cart.Map.formatInfoWindowContent(pos),
                    details: Mall.Cart.Map.formatDetailsContent(pos)
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
                    Mall.Cart.Map.infowindow.setContent(this.html);

                    var inpostModal = jQuery(".carrier-points-modal[data-carrier-points='" + Mall.Cart.Shipping.carrierPoint + "']");

                    //Refresh markers and "City", "Address" filters
                    //if nearest store marker clicked, but the city is different from selected
                    if (this.nearest === 1 && jQuery("div[data-carrier-points='" + Mall.Cart.Shipping.carrierPoint + "'] select[name=shipping_select_city]").val() !== this.town) {

                        if (!Mall.getIsBrowserMobile()) {
                            jQuery("div[data-carrier-points='" + Mall.Cart.Shipping.carrierPoint + "'] select[name=shipping_select_city]")
                                .val(this.town)
                                .select2({
                                    dropdownParent: inpostModal,
                                    language: Mall.reg.get("localeCode")
                                });
                        } else {
                            jQuery("div[data-carrier-points='" + Mall.Cart.Shipping.carrierPoint + "'] select[name=shipping_select_city]")
                                .val(this.town);
                        }

                        jQuery(".shipping_select_point_data").html("");

                        Mall.Cart.Map.searchOnMap(this.town, this.name);
                    }

                    jQuery("div[data-carrier-points='" + Mall.Cart.Shipping.carrierPoint + "'] select[name=shipping_select_point]").val(this.name);
                    if (!jQuery("div[data-carrier-points='" + Mall.Cart.Shipping.carrierPoint + "'] select[name=shipping_select_point]").hasClass("onchange_shipping_select_point")) {
                        if (!Mall.getIsBrowserMobile()) {
                            jQuery("div[data-carrier-points='" + Mall.Cart.Shipping.carrierPoint + "'] select[name=shipping_select_point]")
                                .select2({
                                    dropdownParent: inpostModal,
                                    language: Mall.reg.get("localeCode")
                                });
                        }

                    }
                    jQuery("[name=shipping_select_point]").removeClass("onchange_shipping_select_point");


                    if(Mall.Cart.Map.data.length > 10){
                        zoomOnShowPoint = zoomOnShowPointBigCities;
                    }
                    var map = Mall.Cart.Map.map;

                    map.setCenter(this.getPosition());
                    map.setZoom(((map.getZoom() > zoomOnShowPoint) ? map.getZoom() : zoomOnShowPoint));

                    Mall.Cart.Map.infowindow.open(map, this);

                    jQuery(".nearest_stores_container_list").hide();
                    jQuery(".nearest_stores_container_link").text(Mall.translate.__("shipping_map_show_nearest_link"));
                });

                //Selected city case
                if (typeof filteredData !== "undefined") {
                    if (window.innerWidth < Mall.Breakpoint.sm) {
                        Mall.Cart.Map.map.setZoom(zoomOnShowCity);
                    } else {
                        Mall.Cart.Map.map.setZoom(zoomOnShowCityMobile);
                    }
                    Mall.Cart.Map.map.setCenter(new google.maps.LatLng(filteredData[0].latitude,filteredData[0].longitude));
                }

                markers.push(marker);
                Mall.Cart.Map.gmarkers.push(marker);
                Mall.Cart.Map.gmarkersNameRelation[pos.name] = i;

            }
            //--setMarkers
            var markerClusterOptions = {
                maxZoom: 10,
                gridSize: 14,
                styles: Mall.Cart.Map.clusterStyles
            };

            Mall.Cart.Map.markerClusterer = new MarkerClusterer(Mall.Cart.Map.map, markers, markerClusterOptions);

            //Jeśli w mieście jest tylko jeden paczkomat, niech wybiera go automatycznie
            if(pointsCount === 1){
                Mall.Cart.Map.showMarkerOnMap(filteredData[0].name);
            }
        },

        showMarkerOnMap: function(name) {
            var markerId = parseInt(Mall.Cart.Map.gmarkersNameRelation[name]);
            if(typeof Mall.Cart.Map.gmarkers[markerId] !== "undefined"){
                google.maps.event.trigger(Mall.Cart.Map.gmarkers[markerId], "click");
            }
        },

        makeMapInvisible: function(){
            jQuery(".map_delivery_container")
                .removeClass("map_delivery_container_visible");
        },
        makeMapVisible: function(){
            jQuery(".map_delivery_container")
                .addClass("map_delivery_container_visible");
        },

        resizeMapMobile: function(){
            if (map === null)
                return;
            setTimeout(function () {
                Mall.Cart.Map.resizingMapMobile();
            }, 200);
        },

        resizingMapMobile: function() {
            if (map === null)
                return;

            var center = map.getCenter();
            google.maps.event.trigger(map, "resize");
            map.setCenter(center);

        },

        resizeMap: function(point) {
            if (Mall.Cart.Map.map === null)
                return;
            setTimeout(function () {
                Mall.Cart.Map.resizingMap(point);
            }, 400);
        },
        resizingMap: function(point) {
            if (Mall.Cart.Map.map === null)
                return;

            var center = Mall.Cart.Map.map.getCenter();
            google.maps.event.trigger(Mall.Cart.Map.map, "resize");
            Mall.Cart.Map.map.setCenter(center);

            //Show on map session paczkomat
            if(typeof window.geoposition === "undefined"){
                Mall.Cart.Shipping.attachShowOnMapSavedInSessionPoint();
            }


            if(typeof point !== "undefined"){
                //Show on map session paczkomat
                Mall.Cart.Map.showMarkerOnMap(point);
            }

        },
        //GEO
        showPosition: function(position) {
            //Try to find in 30 km
            var closestStores = Mall.Cart.Map.calculateTheNearestStores(position, Mall.Cart.Map.minDist, false);

            //Try to find in 100 km
            if (closestStores.length <= 0) {
                closestStores = Mall.Cart.Map.calculateTheNearestStores(position, Mall.Cart.Map.minDistFallBack, true);
            }
            closestStores.sort(Mall.Cart.Map.sortByDirection);
            closestStores = closestStores.slice(0,3);

            Mall.Cart.Map.buildStoresList(closestStores);
            Mall.Cart.Map.nearestStores = closestStores;

            if (jQuery("[name=shipping_select_city]") || jQuery("[name=shipping_select_city]").val().length === 0) {
                Mall.Cart.Map.refreshMap([], Mall.Cart.Map.nearestStores);
                jQuery("#" + Mall.Cart.Map.deliverySet[Mall.Cart.Shipping.carrierPoint].mapDelivery)
                    .css({"visibility": "visible", "display": "block"});
            }

        },

        //Get the latitude and the longitude;
        successGeolocationFunction: function(position) {
            window.geoposition = position;
            Mall.Cart.Map.showPosition(window.geoposition);
            //Show on map session paczkomat
            if(typeof window.geoposition === "undefined") {
                Mall.Cart.Shipping.attachShowOnMapSavedInSessionPoint();
            }

        },

        handleGeoLocation: function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(Mall.Cart.Map.successGeolocationFunction);
            }
        },

        calculateTheNearestStores: function(position, minDistance, fallback) {
            // find the closest location to the user's location
            var pos;
            var closestStores = [];

            if(typeof Mall.Cart.Map.deliverySet[Mall.Cart.Shipping.carrierPoint].mapPoints !== "undefined"){
                for (var i = 0; i < Mall.Cart.Map.deliverySet[Mall.Cart.Shipping.carrierPoint].mapPoints.length; i++) {
                    pos = Mall.Cart.Map.deliverySet[Mall.Cart.Shipping.carrierPoint].mapPoints[i];
                    // get the distance between user's location and this point
                    var dist = MapsHelper.Haversine(Mall.Cart.Map.deliverySet[Mall.Cart.Shipping.carrierPoint].mapPoints[i].latitude, Mall.Cart.Map.deliverySet[Mall.Cart.Shipping.carrierPoint].mapPoints[i].longitude, position.coords.latitude, position.coords.longitude);

                    // check if this is the shortest distance so far
                    if (dist < minDistance) {
                        Mall.Cart.Map.deliverySet[Mall.Cart.Shipping.carrierPoint].mapPoints[i].distance = dist;
                        Mall.Cart.Map.deliverySet[Mall.Cart.Shipping.carrierPoint].mapPoints[i].nearest = 1;
                        closestStores.push(Mall.Cart.Map.deliverySet[Mall.Cart.Shipping.carrierPoint].mapPoints[i]);
                    }
                }
            }


            return closestStores;
        },
        //sort by distance
        sortByDirection: function (a, b) {
            return ((a.distance < b.distance) ? -1 : ((a.distance > b.distance) ? 1 : 0));
        },

        formatDetailsContent: function (pos) {
            var payment_point_description = "";
            if(typeof (pos.payment_point_description) !== "undefined"
                && pos.payment_point_description.length > 0
            ){
                //payment_point_description = "<div><span><i class='fa fa-credit-card fa-1x'></i> " +pos.payment_point_description+ "</span></div>";
            }
            var pos_additional =
                '<div>' + (pos.street ? pos.street : "") + ' ' + (pos.building_number ? pos.building_number : "") + '</div>' +
                '<div>' + (pos.postcode ? pos.postcode : "") + ' ' + (pos.town ? pos.town : "") + '</div>' +
                (pos.location_description ? '<div>(' + pos.location_description + ')</div>' : "<div>&nbsp;</div>");

            return '<div class="row">' +
                '<div class="col-sm-6">' +
                '<div><b>' + (pos.street ? pos.street : "") + ' ' + (pos.building_number ? pos.building_number : "") + '</b></div>' +
                '<div>' + (pos.postcode ? pos.postcode : "") + ' ' + (pos.town ? pos.town : "") + '</div>' +
                (pos.location_description ? '<div>(' + pos.location_description + ')</div>' : "<div>&nbsp;</div>")+ payment_point_description+
                '</div>' +
                '<div class="col-sm-6 pointselect-container">' +
                '<a class="button button-primary pointselect" data-select-shipping-method-trigger="1" data-carrier-town="' + (pos.town ? pos.town : "") + '" data-carrier-pointid="' +pos.id+ '" data-carrier-pointcode="' +pos.name+ '" data-carrier-additional="' + pos_additional + '" href="">' + Mall.translate.__("shipping_map_method_select") + '</a>' +
                '</div>' +
                '</div>';
        },

        formatInfoWindowContent: function(pos) {
            var payment_point_description = "";
            if(typeof (pos.payment_point_description) !== "undefined"
                && pos.payment_point_description.length > 0
            ){
                //payment_point_description = "<div><span><i class='fa fa-credit-card fa-1x'></i> " +pos.payment_point_description+ "</span></div>";
            }

            var pos_additional =
                '<div>' + pos.street + ' ' + (pos.building_number ? pos.building_number : "") + '</div>' +
                '<div>' + (pos.postcode ? pos.postcode : "") + ' ' + pos.town + '</div>';

            return '<div class="delivery-marker-window">' +
                '<div class="info_window_text">' +
                '<div class="additional-store-information"><b>' + (pos.street ? pos.street : "") + ' ' + (pos.building_number ? pos.building_number : "") + '</b></div>' +
                '<div class="additional-store-information"><b>' + (pos.postcode ? pos.postcode : "") + ' ' + (pos.town ? pos.town : "") + '</b></div>' +
                '<div class="additional-store-information">' + pos.location_description + '</div>' +
                '<div><a class="button button-primary pointselect" data-select-shipping-method-trigger="1" data-carrier-pointid="' + pos.id + '" data-carrier-pointcode="' + pos.name + '" data-carrier-town="' + pos.town + '" data-carrier-additional="' + pos_additional + '" href="">'+Mall.translate.__("shipping_map_method_select")+'</a></div>' +
                '</div>' +
                '</div>';
        },


        buildStoresList: function(points) {
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
                    list += Mall.Cart.Map.formatDetailsContent(pos);
                }
                list += "</div>";
                if (nearest_stores_container_list_style === "") {
                    list += "<div><a class='nearest_stores_container_link' href=''>"+Mall.translate.__("shipping_map_hide_nearest_link")+"</a></div>";
                } else {
                    list += "<div><a class='nearest_stores_container_link' href=''>"+Mall.translate.__("shipping_map_show_nearest_link")+"</a></div>";
                }

            }
            searchByMapList.html(list);
        },


        searchOnMap: function(q, markerToShow) {
            Mall.Cart.Map._makeMapRequest({town: q}, Mall.Cart.Map.deliverySet[Mall.Cart.Shipping.carrierPoint].urlData, function (response) {
                Mall.Cart.Map.gmarkers = [];  //to collect only filtered markers (used in showMarkerWindow)
                var data = jQuery.parseJSON(response);

                var pointsOnMap = data.map_points || [];

                Mall.Cart.Map.constructShippingPointSelect(pointsOnMap);
                Mall.Cart.Map.refreshMap(pointsOnMap, Mall.Cart.Map.nearestStores);
                jQuery("#" + Mall.Cart.Map.deliverySet[Mall.Cart.Shipping.carrierPoint].mapDelivery).css({"visibility": "visible", "display": "block"});



                if(Mall.getIsBrowserMobile()){
                    var isMapVisible = jQuery("#" + Mall.Cart.Map.deliverySet[Mall.Cart.Shipping.carrierPoint].mapDelivery)
                        .parents(".map_delivery_container")
                        .hasClass("map_delivery_container_visible");

                    if(isMapVisible){
                        jQuery(".map_delivery_container_wrapper .map_delivery_container_show_up")
                            .html('<a href="" class="map_delivery_container_show">'+Mall.translate.__("shipping_map_hide_map_link")+'</a>');
                    } else {
                        jQuery(".map_delivery_container_wrapper .map_delivery_container_show_up")
                            .html('<a href="" class="map_delivery_container_show">'+Mall.translate.__("shipping_map_show_map_link")+'</a>');
                    }

                }

                if(markerToShow){
                    //Show session point
                    Mall.Cart.Map.showMarkerOnMap(markerToShow);
                }

            });
        },

        _makeMapRequest: function(q, url, success) {
            jQuery.ajax({
                url: url,
                type: "POST",
                data: q,
                success: success,
                error: function (response) {
                    console.log(response);
                }
            });
        },

        constructShippingPointSelect: function(map_points) {
            var options = [],
                map_point_long_name;

            options.push('<option value="">'+(jQuery(".carrier-points-modal[data-carrier-points='" + Mall.Cart.Shipping.carrierPoint + "'] .title_section").text())+'</option>');
            jQuery(map_points).each(function (i, map_point) {
                map_point_long_name = map_point.street + " " + (map_point.building_number ? map_point.building_number : "") + ", " + map_point.town  + (map_point.postcode ? " (" + map_point.postcode + ")" : "");
                options.push('<option data-carrier-town="' + map_point.town + '" data-carrier-additional="' + map_point.additional + '" data-carrier-pointcode="' + map_point.name + '" data-carrier-pointid="' + map_point.id + '" value="' + map_point.name + '">' + map_point_long_name + '</option>');
            });

            //Jeśli w mieście jest tylko jeden paczkomat,
            // niech wybiera go automatycznie

            if(map_points.length === 1){
                jQuery("div[data-carrier-points='" + Mall.Cart.Shipping.carrierPoint + "'] select[name=shipping_select_point]")
                    .html(options.join(""))
                    .attr("disabled", false)
                    .val(map_points[0].name);

            } else {
                jQuery("div[data-carrier-points='" + Mall.Cart.Shipping.carrierPoint + "'] select[name=shipping_select_point]")
                    .html(options.join(""))
                    .attr("disabled", false)
                    .val("");
            }

            var inpostModal = jQuery(".carrier-points-modal[data-carrier-points='" + Mall.Cart.Shipping.carrierPoint + "']");

            if (!Mall.getIsBrowserMobile()) {
                jQuery("div[data-carrier-points='" + Mall.Cart.Shipping.carrierPoint + "'] select[name=shipping_select_point]")
                    .select2({dropdownParent: inpostModal, language: Mall.reg.get("localeCode")});
            }

        }
    }

    jQuery(document).ready(function(){
        Mall.Cart.Shipping.init();
    });
})();