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

var defaultCenterLang = 52.4934482;
var defaultCenterLat = 18.8979594;
var defaultCenterLangMobile = 51.7934482;
var defaultCenterLatMobile = 18.8979594;

var minDist = 30; //km
var minDistFallBack = 100; //km
var closestStores = [];

var gmarkers = [];


var smallScreen = 768;
var middleScreen = 992;

if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(successFunction);
}
// Get the latitude and the longitude;
function successFunction(position) {
    window.geoposition = position;
}


function initialize() {

    var mapOptions = {
        zoom: 6,
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

    if (window.innerWidth < smallScreen) {
        //mapOptions.zoom = 5;
        //mapOptions.center = new google.maps.LatLng(defaultCenterLangMobile, defaultCenterLatMobile);
        mapOptions.zoomControlOptions.position = google.maps.ControlPosition.RIGHT_CENTER;
        mapOptions.zoomControlOptions.style = google.maps.ZoomControlStyle.SMALL;
        mapOptions.panControl = false;
    }

    map = new google.maps.Map(document.getElementById('map'), mapOptions);

    infowindow = new google.maps.InfoWindow({
        //pixelOffset: new google.maps.Size(0, 5),
        buttons: {close: {show: 0}}
    });
    data = jQuery.parseJSON(data);


    //I will show all the stores on the map first
    refreshMap();
    buildStoresList();
    if(navigator.geolocation){
        navigator.geolocation.getCurrentPosition(
            function (position) {
                //If you allow to see your location, you will see all the nearest stores
                gmarkers = [];
                showPosition(position);
            },
            function (error) {
                //If you deny to see your location, you will see all the stores
                if (error.code == error.PERMISSION_DENIED){}

            });
    } else {
        //Your browser doesn't support GEO location, you will see all the stores
    }



}

//GEO
function showPosition(position) {

    //Try to find in 30 km
    var closestStores = calculateTheNearestStores(position, minDist, false);
    //Try to find in 100 km
    if (closestStores.length <= 0) {
        closestStores = calculateTheNearestStores(position, minDistFallBack, true);
    } else {
        showLabel(".the-nearest-stores");
        showLabel("a.stores-map-show-all");
    }
    if (closestStores.length <= 0) {
        closestStores = data;
    } else {
        showLabel(".the-nearest-stores");
        showLabel("a.stores-map-show-all");
    }

    refreshMap(closestStores);
    buildStoresList(closestStores,position);
}

function calculateTheNearestStores(position,minDistance, fallback) {
    // find the closest location to the user's location
    var pos;
    //console.log(minDistance);
    for (var i = 0; i < data.length; i++) {
        pos = data[i];
        // get the distance between user's location and this point
        var dist = Haversine(data[i].latitude, data[i].longitude, position.coords.latitude, position.coords.longitude);
        //console.log(dist);
        // check if this is the shortest distance so far
        if (dist < minDistance) {
            data[i].distance = dist;
            closestStores.push(data[i]);
            if(fallback && closestStores.length >= 3){
                break;
            }

        }
    }
    //sort by distance
    function sortByDirection(a, b) {
        return ((a.distance < b.distance) ? -1 : ((a.distance > b.distance) ? 1 : 0));
    }
    closestStores.sort(sortByDirection);

    return closestStores;
}

//--GEO


function refreshMap(filteredData) {
    //var imageUrl = '/skin/frontend/modago/wf/js/storemap/marker/chart.png';
    var imageUrl = '/skin/frontend/modago/wf/js/storemap/marker/wf.png';
    if (typeof filteredData !== "undefined")
        data = filteredData;

    var markers = [];
    if (markerClusterer) {
        markerClusterer.clearMarkers();
    }

    var markerImage = new google.maps.MarkerImage(imageUrl,
        new google.maps.Size(40, 40));

    //setMarkers
    for (var i = 0; i < data.length; i++) {
        var pos = data[i];

        var posLatLng = new google.maps.LatLng(pos.latitude, pos.longitude);
        var marker = new google.maps.Marker({
            id: pos.id,
            position: posLatLng,
            map: map,
            icon: markerImage,
            html: formatInfoWindowContent(pos)
        });

        var contentString = " ";

        google.maps.event.addListener(marker, "click", function () {
            infowindow.setContent(this.html);
            //$screen-sm: 768px
            if (window.innerWidth >= smallScreen) {
                map.setCenter(this.getPosition()); // set map center to marker position
                smoothZoom(map, 10, map.getZoom()); //call smoothZoom, parameters map, final zoomLevel, and starting zoom level
            } else {
                map.setCenter(this.getPosition());
                map.setZoom(((map.getZoom() > 10) ? map.getZoom() : 10));
            }
            //$screen-md: 992px
            if (window.innerWidth <= middleScreen) {
                jQuery('html, body').animate({
                    scrollTop: (jQuery("#map-container").offset().top - 120)
                }, 1000);
            }

            infowindow.open(map, this);

        });

        //Show all stores case
        if (typeof filteredData !== "undefined") {
            if (window.innerWidth < smallScreen) {
                map.setZoom(5);
                map.setCenter(new google.maps.LatLng(defaultCenterLangMobile, defaultCenterLatMobile));
            } else {
                map.setZoom(6);
                map.setCenter(new google.maps.LatLng(defaultCenterLang, defaultCenterLat));
            }
        }

        markers.push(marker);
        gmarkers.push(marker);

    }
    //--setMarkers

    var markerClusterOptions = {
        maxZoom: 6,
        gridSize: 7,
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
function formatInfoWindowContent(info) {
    var contentString =
        '<div class="marker-window">' +
        '<div class="info_window_text">' +
        '<p class="iw_header"><i class="shop_name bold">' + info.name + '</i></p>' +
        '<div class="marker-info-tel"><b>Tel: </b>' + info.phone + '</div>' +
        '<div class="additional-store-information">' + info.time_opened + '</div>' +
        '</div>' +
        '</div>';
    return contentString;
}

function generateDirectionLink(pos, position) {
    var directionLink = "https://maps.google.com/?daddr=" + pos.latitude + "," + pos.longitude;
    if(typeof position == "undefined")
        position =  window.geoposition;


    if (typeof position !== "undefined")
        directionLink += "&saddr=" + position.coords.latitude + "," + position.coords.longitude;

    return directionLink;
}


function buildStoresList(filteredData,position) {
    if (typeof filteredData !== "undefined")
        data = filteredData;

    var searchByMapList = jQuery(".search-by-map-list-container");

    var list = "";
    var pos, posId;

    if (data.length > 0) {
        list += "<ul class='search-by-map-list-html'>";
        for (var i = 0; i < data.length; i++) {
            pos = data[i];
            posId = pos.id;
            list += "<li data-id='" + posId + "'>" +
                "<div class='col-md-12 col-sm-12 col-xs-12 store-info-item'>" +

                "<div class='col-md-7 col-sm-8 col-xs-7 left-column'>" +
                "<p><b>" + pos.name + "</b></p>" +
                "<p>" + pos.street + "</p>" +
                "<p>" + pos.postcode + " " + pos.city + "</p>";
            if(pos.phone.length > 0){
                list +="<p>Tel: " + pos.phone + "</p>";
            }

            list +="<div>" + pos.time_opened + "</div>" +
                "</div>" +

                "<div class='col-md-5 col-sm-4 col-xs-5 right-column'>" +
                "<div class='buttons'>" +
                "<div class='row'><a class='button button-primary medium pull-right' href='' data-markernumber='" + posId + "' onclick='showMarkerWindow(this);return false;'><i class='fa fa-map-marker'></i> " + Mall.translate.__("Show on map") + "</a></div>" +
                "<div class='row'><a class='button button-primary medium pull-right' href='" + generateDirectionLink(pos,position) + "' target='_blank'><i class='fa fa-compass'></i> " + Mall.translate.__("Define the route") + "</a></div>";

            if(Mall.getIsBrowserMobile() && pos.phone.length > 0){
                list += "<div class='row'>" +
                    "<a class='button button-primary medium pull-right' href='tel:" + pos.phone + "'><i class='fa fa-phone'></i> " + Mall.translate.__("Select phone") + "</a>" +
                    "</div>";
            }

            list +="</div>" +
                "</div>" +

                "</div>" +
                "</li>";
        }
        list += "</ul>";
    }
    searchByMapList.html(list);
}

function showMarkerWindow(link) {
    var markernumber = jQuery(link).data("markernumber");
    jQuery(gmarkers).each(function (i, item) {
        if (markernumber == item.id) {
            google.maps.event.trigger(gmarkers[i], "click");
            return false;
        }
    });

}


function searchOnMap(q) {
    var form = jQuery("#search_by_map_form");
    var q = form.find("[name=search_by_map]").val();
    _makeMapRequest(q);
}
function clearSearchOnMap() {
    var form = jQuery("#search_by_map_form");
    form.find("[name=search_by_map]").val("");
    hideLabel("a.stores-map-show-all");
    hideLabel(".the-nearest-stores");
    _makeMapRequest(0)
}

function _makeMapRequest(q) {
    var form = jQuery("#search_by_map_form");
    jQuery.ajax({
        url: form.attr("action"),
        type: "POST",
        data: {filter: q},
        success: function (data) {
            gmarkers = [];  //to collect only filtered markers (used in showMarkerWindow)
            data = jQuery.parseJSON(data);
            refreshMap(data);
            buildStoresList(data);
        },
        error: function (response) {
            console.log(response);
        }
    });
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

function showLabel(label){
    jQuery(label).removeClass("hidden");
}
function hideLabel(label){
    jQuery(label).addClass("hidden");
}

jQuery(document).ready(function () {
    var enteredSearchValue;
    jQuery(document).on("keyup", "input[name=search_by_map]", function (e) {
        e.preventDefault();

        enteredSearchValue = jQuery.trim(jQuery(this).val());

        if(enteredSearchValue.length > 0){
            hideLabel(".the-nearest-stores");
            showLabel("a.stores-map-show-all");
        }
        else {
            hideLabel("a.stores-map-show-all");
        }
        if (enteredSearchValue.length >= 0 && e.which !== 13) {
            searchOnMap(enteredSearchValue);
        }
    });

    jQuery("#search_by_map_form").submit(function (e) {
        e.preventDefault();
        console.log(jQuery("input[name=search_by_map]").val());
        if (jQuery.trim(jQuery("input[name=search_by_map]").val()).length > 0) {

            hideLabel(".the-nearest-stores");
            searchOnMap();
        }
    })
});