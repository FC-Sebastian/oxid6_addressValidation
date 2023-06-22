

function fcInitMap() {

    let map = new google.maps.Map($('#fcMap')[0], {center: {lat: 51.1642292, lng: 10.4541194}, zoom: 6});
    let service = new google.maps.places.PlacesService(map);

    let request = {query: fcMapAddress, fields:['name', 'geometry']};

    service.findPlaceFromQuery(request, function (results, status) {
        if (status === google.maps.places.PlacesServiceStatus.OK) {
            for (var i = 0; i < results.length; i++) {
                createMarker(results[i]);
            }
            map.setCenter(results[0].geometry.location);
        }
    });
}