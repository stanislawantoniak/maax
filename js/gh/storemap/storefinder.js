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

    if (window.innerWidth < 768) {
        //mapOptions.zoom = 5;
        //mapOptions.center = new google.maps.LatLng(defaultCenterLangMobile, defaultCenterLatMobile);
        //mapOptions.zoomControlOptions.position = google.maps.ControlPosition.RIGHT_CENTER;
        //mapOptions.zoomControlOptions.style = google.maps.ZoomControlStyle.SMALL;
        //mapOptions.panControl = false;
    }

    map = new google.maps.Map(document.getElementById('map'), mapOptions);

    infowindow = new google.maps.InfoWindow({
        //pixelOffset: new google.maps.Size(0, 5),
        buttons: {close: {show: 0}}
    });
    data = jQuery.parseJSON(data);



    if(navigator.geolocation){
        navigator.geolocation.getCurrentPosition(
            function (position) {
                console.log("I'm tracking you!");

                showPosition(position);
            },
            function (error) {
                if (error.code == error.PERMISSION_DENIED)
                    console.log("You denied me :-(");
                refreshMap();
                buildStoresList();
            });
    } else {
        console.log(" Your browser don't support GEO location!");
        refreshMap();
        buildStoresList();
    }



}

//GEO
function showPosition(position) {

    //Try to find in 30 km
    var closestStores = calculateTheNearestStores(position, minDist, false);
    //Try to find in 100 km
    if (closestStores.length <= 0) {
        closestStores = calculateTheNearestStores(position, minDistFallBack, true);
        refreshMap(closestStores);
        buildStoresList(closestStores);
        return;
    }
    if (closestStores.length <= 0) {

        closestStores = data;
        refreshMap(closestStores);
        buildStoresList(closestStores);
        return;
    }


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
                //minDistance = dist;
                return closestStores;
            }

        }
    }
    return closestStores;
}
//--GEO


function refreshMap(filteredData) {

    //var imageUrl = 'http://chart.apis.google.com/chart?cht=mm&chs=24x32&chco=FFFFFF,008CFF,000000&ext=.png';
    var imageUrl = 'http://chart.apis.google.com/chart?cht=mm&chs=24x32&chco=ffffff,000000,000000&ext=.png';
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
            //$screen-sm:                  768px
            if (window.innerWidth >= 768) {
                map.setCenter(this.getPosition()); // set map center to marker position
                smoothZoom(map, 10, map.getZoom()); //call smoothZoom, parameters map, final zoomLevel, and starting zoom level
            } else {
                //map.setCenter(this.getPosition());
                smoothZoom(map, 6, map.getZoom());
            }

            infowindow.open(map, this);

        });

        //Show all stores case
        if (typeof filteredData !== "undefined") {
            if (window.innerWidth < 768) {
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
    if (window.innerWidth < 768) {
        //markerClusterOptions.maxZoom = 8;
        //markerClusterOptions.gridSize = 20;
    }
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

function generateDirectionLink(pos) {
    return "https://maps.google.com/?daddr=" + pos.latitude + "," + pos.longitude;
}
function buildStoresList(filteredData) {

    if (typeof filteredData !== "undefined")
        data = filteredData;

    var searchByMapList = jQuery(".search-by-map-list");

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
                "<p>" + pos.postcode + " " + pos.city + "</p>" +
                "<p>Tel: " + pos.phone + "</p>" +
                "<div>" + pos.time_opened + "</div>" +
                "</div>" +

                "<div class='col-md-5 col-sm-4 col-xs-5 right-column'>" +
                "<div class='buttons'>" +
                "<div class='row'><a class='button button-third large pull-right' href='' data-markernumber='" + posId + "' onclick='showMarkerWindow(this);return false;'><i class='fa fa-map-marker'></i> " + showOnMapLink + "</a></div>" +
                "<div class='row'><a class='button button-third large pull-right' href='" + generateDirectionLink(pos) + "' target='_blank'><i class='fa fa-compass'></i> " + defineTheRoute + "</a></div>" +
                "<div class='row'><a class='button button-third large pull-right' href='tel:" + pos.phone + "'><i class='fa fa-phone'></i> " + selectNumber + "</a></div>" +
                "</div>" +
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

function filterStoresList(enteredText) {

    var posCity;
    var posPostcode;

    jQuery(data).each(function (i, pos) {
        posCity = pos.city;
        posPostcode = pos.postcode;

        if (
            (posCity.search(new RegExp(enteredText, "i")) > -1) ||
            posPostcode.search(new RegExp(enteredText, "i")) > -1
        ) {
            jQuery(".search-by-map-list-html li[data-id=" + pos.id + "]").show();
        } else {
            jQuery(".search-by-map-list-html li[data-id=" + pos.id + "]").hide();
        }
    });
}

jQuery(document).ready(function () {
    jQuery(document).on("keyup", "input[name=search_by_map]", function (e) {
        e.preventDefault;
        searchOnMap(jQuery(this).val());
        //filterStoresList(jQuery(this).val());
    });

    jQuery("#search_by_map_form").submit(function(){
        searchOnMap();
        return false;
    })
});