var clusterStyles = [[
    {
        url: '/media/cluster_icons/people35.png',
        width: 35,
        height: 35,
        textColor: '#FFFFFF',
        textSize: 10
    },
    {
        url: '/media/cluster_icons/people45.png',
        width: 45,
        height: 45,
        textColor: '#ff0000',
        textSize: 11
    }, {
        url: '/media/cluster_icons/people55.png',
        width: 55,
        height: 55,
        textColor: '#ffffff',
        textSize: 12
    }], [{
    url: '/media/cluster_icons/conv30.png',
    width: 30,
    height: 27,
    anchorText: [-3, 0],
    anchorIcon: [27, 28],
    textColor: '#ff00ff',
    textSize: 10
}, {
    url: '/media/cluster_icons/conv40.png',
    width: 40,
    height: 36,
    anchorText: [-4, 0],
    anchorIcon: [36, 37],
    textColor: '#ff0000',
    textSize: 11
}, {
    url: '/media/cluster_icons/conv50.png',
    width: 50,
    height: 45,
    anchorText: [-5, 0],
    anchorIcon: [45, 46],
    textColor: '#0000ff',
    textSize: 12
}], [{
    url: '/media/cluster_icons/heart30.png',
    width: 30,
    height: 26,
    anchorIcon: [26, 15],
    textColor: '#ff00ff',
    textSize: 10
}, {
    url: '/media/cluster_icons/heart40.png',
    width: 40,
    height: 35,
    anchorIcon: [35, 20],
    textColor: '#ff0000',
    textSize: 11
}, {
    url: '/media/cluster_icons/heart50.png',
    width: 50,
    height: 44,
    anchorIcon: [44, 25],
    textSize: 12
}
]];

var markerClusterer = null;
var map = null;
var infowindow = null;

google.maps.event.addDomListener(window, 'load', initialize);


function refreshMap(filtredData) {

    var imageUrl = 'http://chart.apis.google.com/chart?cht=mm&chs=24x32&chco=FFFFFF,008CFF,000000&ext=.png';

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
    }
    //--setMarkers

    markerClusterer = new MarkerClusterer(map, markers, {
        maxZoom: 6,
        gridSize: 7,
        //styles: clusterStyles[0]
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
    var refresh = document.getElementById('refresh');
    google.maps.event.addDomListener(refresh, 'click', refreshMap);

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
            refreshMap(data);
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