//GEO helpers
MapsHelper = {
    /**
     * Get Distance between two lat/lng points using the Haversine function
     * First published by Roger Sinnott in Sky & Telescope magazine in 1984 
     * (“Virtues of the Haversine”)
     * @param {type} lat1
     * @param {type} lon1
     * @param {type} lat2
     * @param {type} lon2
     * @returns {Number}
     */

    Haversine: function (lat1, lon1, lat2, lon2) {
        var R = 6372.8; // Earth Radius in Kilometers

        var dLat = MapsHelper.Deg2Rad(lat2 - lat1);
        var dLon = MapsHelper.Deg2Rad(lon2 - lon1);

        var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(MapsHelper.Deg2Rad(lat1)) * Math.cos(MapsHelper.Deg2Rad(lat2)) *
                Math.sin(dLon / 2) * Math.sin(dLon / 2);
        var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        var d = R * c;

        // Return Distance in Kilometers
        return d;
    },
    // Convert Degress to Radians
    Deg2Rad: function (deg) {
        return deg * Math.PI / 180;
    }
}


