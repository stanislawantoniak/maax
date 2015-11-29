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
var gmarkers = [];

function refreshMap(filtredData) {

    //var imageUrl = 'http://chart.apis.google.com/chart?cht=mm&chs=24x32&chco=FFFFFF,008CFF,000000&ext=.png';
    var imageUrl = 'http://chart.apis.google.com/chart?cht=mm&chs=24x32&chco=ffffff,000000,000000&ext=.png';
    if (typeof filtredData !== "undefined")
        data = filtredData;

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
            infowindow.open(map, this);
        });
        markers.push(marker);
        gmarkers.push(marker);

    }
    //--setMarkers

    markerClusterer = new MarkerClusterer(map, markers, {
        maxZoom: 6,
        gridSize: 7,
        styles: clusterStyles
    });
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
function buildStoresList(data) {

    console.log(data);
    data = jQuery.parseJSON(data);
    console.log(data);
    var searchByMapList = jQuery(".search-by-map-list");

    var list = "";
    var pos, posId;

    if (data.poses.length > 0) {
        list += "<p>Kliknij nazwę, aby dowiedzieć się więcej</p>";
        list += "<ul>";
        for (var i = 0; i < data.poses.length; i++) {
            pos = data.poses[i];
            posId = pos.id;
            list += "<li>" +
                "<a href='' data-markernumber='" + posId + "' onclick='showMarkerWindow(this);return false;'>" +
                "<div><b>" + pos.name + "</b></div>" +
                "<div>Tel: " + pos.phone + "</div>" +
                "</a>" +
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
        center: new google.maps.LatLng(52.4934482, 18.8979594),
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

    if (window.innerWidth < 762) {
        mapOptions.zoomControlOptions.position = google.maps.ControlPosition.RIGHT_CENTER;
        mapOptions.zoomControlOptions.style = google.maps.ZoomControlStyle.SMALL;
        mapOptions.panControl = false;
    }
    ;


    map = new google.maps.Map(document.getElementById('map'), mapOptions);

    infowindow = new google.maps.InfoWindow({
        pixelOffset: new google.maps.Size(0, 5),
        buttons: {close: {show: 0}}
    });


    var clear = document.getElementById('clear');
    google.maps.event.addDomListener(clear, 'click', clearClusters);

    refreshMap();
}


function searchOnMap() {
    var form = jQuery("#search_by_map_form");
    var q = form.find("input[type=text][name=search_by_map]").val();
    jQuery.ajax({
        url: form.attr("action"),
        type: "POST",
        data: {filter: q},
        success: function (data) {
            gmarkers = [];  //to collect only filtred markers (used in showMarkerWindow)
            refreshMap(data);
            buildStoresList(data);
        },
        error: function (response) {
            console.log(response);
        },

    })


}

function clearClusters(e) {
    e.preventDefault();
    e.stopPropagation();
    markerClusterer.clearMarkers();
}

/**
 * Function to filter markers by category
 */
jQuery(document).ready(function () {


})