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

var gmarkers = [];

function refreshMap(filteredData) {

    //var imageUrl = 'http://chart.apis.google.com/chart?cht=mm&chs=24x32&chco=FFFFFF,008CFF,000000&ext=.png';
    var imageUrl = 'http://chart.apis.google.com/chart?cht=mm&chs=24x32&chco=ffffff,000000,000000&ext=.png';
    if (typeof filteredData !== "undefined")
        data = filteredData;

    var markers = [];
    if (markerClusterer) {
        markerClusterer.clearMarkers();
    }

    data = jQuery.parseJSON(data);


    var markerImage = new google.maps.MarkerImage(imageUrl,
        new google.maps.Size(40, 40));

    //setMarkers
    for (var i = 0; i < data.poses.length; i++) {
        var pos = data.poses[i];

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
            map.setCenter(this.getPosition()); // set map center to marker position

            // call smoothZoom, parameters map, final zoomLevel, and starting zoom level
            if (window.innerWidth < 768) {
                smoothZoom(map, 5, map.getZoom());
            } else {
                smoothZoom(map, 11, map.getZoom());
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
        markerClusterOptions.maxZoom = 8;
        markerClusterOptions.gridSize = 20;
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
            self.smoothZoom(map, max, cnt + 1);
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
        data = jQuery.parseJSON(filteredData);

    var searchByMapList = jQuery(".search-by-map-list");

    var list = "";
    var pos, posId;

    if (data.poses.length > 0) {
        list += "<ul class='search-by-map-list-html'>";
        for (var i = 0; i < data.poses.length; i++) {
            pos = data.poses[i];
            posId = pos.id;
            list += "<li data-id='"+posId+"'>" +
                "<div class='col-md-12 col-sm-12 col-xs-12 store-info-item'>" +

                "<div class='col-md-7 col-sm-7 col-xs-7 left-column'>" +
                "<p><b>" + pos.name + "</b></p>" +
                "<p>" + pos.street + "</p>" +
                "<p>" + pos.postcode + " "+pos.city+"</p>" +
                "<p>Tel: " + pos.phone + "</p>" +
                "<div>" + pos.time_opened + "</div>" +
                "</div>" +

                "<div class='col-md-5 col-sm-5 col-xs-5 right-column'>" +
                "<div class='buttons'>" +
                "<div class='row'><a class='button button-third large' href='' data-markernumber='" + posId + "' onclick='showMarkerWindow(this);return false;'><i class='fa fa-map-marker'></i> "+showOnMapLink+"</a></div>" +
                "<div class='row'><a class='button button-third large' href='"+generateDirectionLink(pos)+"' target='_blank'><i class='fa fa-compass'></i> "+defineTheRoute+"</a></div>" +
                "<div class='row'><a class='button button-third large' href='tel:"+pos.phone+"'><i class='fa fa-phone'></i> "+selectNumber+"</a></div>" +
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
        mapOptions.zoom = 5;
        mapOptions.center = new google.maps.LatLng(defaultCenterLangMobile, defaultCenterLatMobile);
        mapOptions.zoomControlOptions.position = google.maps.ControlPosition.RIGHT_CENTER;
        mapOptions.zoomControlOptions.style = google.maps.ZoomControlStyle.SMALL;
        mapOptions.panControl = false;
    }


    map = new google.maps.Map(document.getElementById('map'), mapOptions);

    infowindow = new google.maps.InfoWindow({
        //pixelOffset: new google.maps.Size(0, 5),
        buttons: {close: {show: 0}}
    });

    refreshMap();
    buildStoresList();
}


function searchOnMap(q) {
    _makeMapRequest(q);
}
function clearSearchOnMap() {
    var form = jQuery("#search_by_map_form");
    form.find("select[name=search_by_map]").val("");
    _makeMapRequest(0)
}

function _makeMapRequest(q) {
    var form = jQuery("#search_by_map_form");
    jQuery.ajax({
        url: searchOnMapUrl,
        type: "POST",
        data: {filter: q},
        success: function (data) {
            gmarkers = [];  //to collect only filtered markers (used in showMarkerWindow)
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

function filterStoresList(enteredText) {
    var posCity;
    var posPostcode;

    jQuery(data.poses).each(function (i, pos) {
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

    jQuery("input[name=search_by_map]").keyup(function(e){
        var enteredText = jQuery(this).val();
        filterStoresList(enteredText)
    });
});