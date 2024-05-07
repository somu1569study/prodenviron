<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "users";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
session_start();
$id = $_SESSION['id'];





// Fetch data from database
$sql = "SELECT label, lat, lon FROM userstore WHERE Uid=$id";
$result = $conn->query($sql);

$stores = array();
if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        $stores[] = $row;
    }
} else {
    echo "0 results";
}

$conn->close();
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Route Planning & Nearby Petrol Stations</title>
    <link rel='stylesheet' type='text/css' href='https://api.tomtom.com/maps-sdk-for-web/cdn/6.x/6.15.0/maps/maps.css'>
    <style>
        #map {
            height: 600px;
            width: 100%;
        }

        #waypoints {
            width: 30%;
            float: right;
            padding: 20px;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
    <h1>Route Planning & Nearby Petrol Stations</h1>
    <button onclick="getCurrentLocation()">Get Current Location</button>
    <button onclick="showNearbyStations()">Show Nearby Petrol Stations</button>
    <button onclick="findNextWaypoint()">Find Best Nearest Petrol Station Waypoint</button>
    
    <button onclick="calculateRoute()">Calculate Route</button>
    <button onclick="optimizeRoute()">Optimize Route</button>
    <button onclick="ride()">Start Ride</button>



    <input type="text" id="query" value="" placeholder="Enter search query"/>
    <button onclick="search()">Search</button>

    <div id="map"></div>
    <div id="waypoints"></div>
    <div id="summary-route"></div>
    <div id="summary-optimize"></div>
    <div id="optimizedDiv"></div>

    <script src="https://api.tomtom.com/maps-sdk-for-web/cdn/6.x/6.15.0/maps/maps-web.min.js"></script>
    <script src="https://api.tomtom.com/maps-sdk-for-web/cdn/6.x/6.25.0/services/services-web.min.js"></script>
    <script>
        const apiKey = 'Lo1AJ40JYhvoOVjYevPKTJ3GlMVhiTKK';
        var map;
        var petrolStationsData = []; // Global variable to store petrol stations data
        var waypoints = [];
        var optimizedWaypoints = []; // Variable to store optimized waypoints

        // Initialize map
        map = tt.map({
            key: apiKey,
            container: 'map',
            center: [0, 0], // Initial center, will be updated later
            zoom: 15 // Adjust as needed
        });

        // Function to search for nearby petrol stations using TomTom Search API
        async function searchPetrolStations(location) {
            try {
                const response = await fetch(`https://api.tomtom.com/search/2/poiSearch/petrol+station.json?limit=10&lat=${location.lat}&lon=${location.lon}&key=${apiKey}`);
                const data = await response.json();
                return data.results;
            } catch (error) {
                console.error('Error searching for petrol stations:', error);
                return [];
            }
        }

        // Function to add markers on the map
        function addMarkers(petrolStations) {
            petrolStations.forEach(station => {
                var marker = new tt.Marker().setLngLat([station.position.lon, station.position.lat])
                                               .setPopup(new tt.Popup().setHTML(`Latitude: ${station.position.lat}, Longitude: ${station.position.lon}`))
                                               .addTo(map);
            });
        }

        // Function to get current location
        function getCurrentLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition);
            } else {
                console.error('Geolocation is not supported by this browser.');
            }
        }

        // Callback function to handle current position
        function showPosition(position) {
            var currentLocation = {
                lat: position.coords.latitude,
                lon: position.coords.longitude
            };

            // Update map center to current location
            map.setCenter([currentLocation.lon, currentLocation.lat]);

            // Add marker for current location
            new tt.Marker().setLngLat([currentLocation.lon, currentLocation.lat])
                           .setPopup(new tt.Popup().setHTML('Your current location: ' + currentLocation.lat + ', ' + currentLocation.lon))
                           .addTo(map);
        }

        function showMap(center) {
            if (map) {
                map.flyTo({
                    center: center,
                    zoom: 13,
                    pitch: 25
                });
            } else {
                map = tt.map({
                    key: apiKey,
                    container: 'map',
                    center: center,
                    zoom: 13,
                    pitch: 25
                });
            }
        }

        // Function to show nearby petrol stations
        function showNearbyStations() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(position => {
                    var currentLocation = {
                        lat: position.coords.latitude,
                        lon: position.coords.longitude
                    };

                    // Update map center to current location
                    map.setCenter([currentLocation.lon, currentLocation.lat]);

                    // Search for nearby petrol stations
                    searchPetrolStations(currentLocation)
                        .then(petrolStations => {
                            console.log("Nearby Petrol Stations:");
                            console.log(petrolStations);
                            petrolStationsData = petrolStations; // Store petrol stations data globally
                            addMarkers(petrolStations);
                        })
                        .catch(error => {
                            console.error('Error searching for petrol stations:', error);
                        });
                });
            } else {
                console.error('Geolocation is not supported by this browser.');
            }
        }

        // Function to find the next waypoint
        function findNextWaypoint() {
            if (petrolStationsData.length === 0) {
                console.error('No petrol stations data available.');
                return;
            }

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(position => {
                    var currentLocation = {
                        lat: position.coords.latitude,
                        lon: position.coords.longitude
                    };

                    // Calculate distances between current location and petrol stations
                    const distances = petrolStationsData.map(station => ({
                        lat: station.position.lat,
                        lon: station.position.lon,
                        dist: getDistance(currentLocation.lat, currentLocation.lon, station.position.lat, station.position.lon)
                    }));

                    // Sort distances by distance
                    distances.sort((a, b) => a.dist - b.dist);

                    // Print the closest station
                    const closestStation = distances[0];
                    console.log("Closest Petrol Station:");
                    console.log(`Position: ${closestStation.lat}, ${closestStation.lon}, Distance: ${closestStation.dist}`);

                    // Fly to the closest petrol station
                    showMap([closestStation.lon, closestStation.lat]);
                });
            } else {
                console.error('Geolocation is not supported by this browser.');
            }
        }

        // Function to calculate distance between two points using Haversine formula
        function getDistance(lat1, lon1, lat2, lon2) {
            const R = 6371; // Radius of the Earth in kilometers
            const dLat = deg2rad(lat2 - lat1);
            const dLon = deg2rad(lon2 - lon1);
            const a =
                Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
                Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            const d = R * c; // Distance in kilometers
            return d;
        }

        function deg2rad(deg) {
            return deg * (Math.PI / 180);
        }

        // Route planning functions

        function search() {
            const query = document.getElementById("query").value;
            tt.services.fuzzySearch({
                key: apiKey,
                query: query,
            })
                .then((response) => {
                    const position = response.results[0].position;
                    showMap(position);
                    waypoints.push(position);
                    addWaypointMarker(position, query);
                    displayWaypoints();
                });
        }
         // Function to perform search
         function search(temp) {
            var query = temp||document.getElementById("query").value;
            tt.services.fuzzySearch({
                key: apiKey,
                query: query,
            })
            .then((response) => {
                const position = response.results[0].position;
                showMap(position);
                waypoints.push(position);
                addWaypointMarker(position, query);
                displayWaypoints();
            });
        }

        function calculateRoute(customWaypoints ) {
            const locations1 = customWaypoints || waypoints;
            tt.services.calculateRoute({
                key: apiKey,
                routeType: 'shortest',

                locations: locations1
                //computeBestOrder: true
            })
                .then((result) => {
                    console.log(result);
                    const summary = document.getElementById('summary-route');
                    summary.innerHTML = 'Distance routed: ' + result.routes[0].summary.lengthInMeters + ' meters';

                    const geojson = result.toGeoJson();
                    if (map.getLayer('route')) {
                        map.removeLayer('route');
                        map.removeSource('route');
                    }

                    map.addLayer({
                        'id': 'route',
                        'type': 'line',
                        'source': {
                            'type': 'geojson',
                            'data': geojson
                        },
                        'paint': {
                            'line-color': 'orange',
                            'line-width': 8
                        }
                    });
                });
        }

        // Optionally, you can call another function or perform additional actions here
    
    function showoptroute(customWaypoints ) {
            const locations1 = customWaypoints || waypoints;
            tt.services.calculateRoute({
                key: apiKey,
                routeType: 'shortest',
                locations: locations1,
                computeBestOrder: true
            })
                .then((result) => {
                    console.log(result);
                    const summary = document.getElementById('summary-route');
                    summary.innerHTML = 'Distance routed: ' + result.routes[0].summary.lengthInMeters + ' meters';


    // Clear existing optimized layer
    if (map.getLayer('optimized')) {
        map.removeLayer('optimized');
        map.removeSource('optimized');
    }

    // Add optimized route layer
    const optimizedGeojson = result.toGeoJson();

    map.addLayer({
        'id': 'optimized',
        'type': 'line',
        'source': {
            'type': 'geojson',
            'data': optimizedGeojson
        },
        'paint': {
            'line-color': 'green',
            'line-width': 8
        }
    });

});
    
    }
        function optimizeRoute() {
    // Get the current location as the starting point
    const currentLocation = waypoints[0];
    // Calculate distances between current location and all other waypoints
    const distances = waypoints.slice(1).map(waypoint => calculateDistance(currentLocation, waypoint));
    // Create an array of indices representing the order of waypoints based on distances
    const sortedIndices = distances.map((distance, index) => index + 1).sort((a, b) => distances[a - 1] - distances[b - 1]);
    // Construct the optimized waypoints using FIFO approach
    const optimizedWaypoints = [currentLocation, ...sortedIndices.map(index => waypoints[index])];

    // Display the optimized waypoints in the console
    console.log("Optimized Waypoints:");
    optimizedWaypoints.forEach((waypoint, index) => {
        // Add markers to the map for the optimized waypoints
        console.log(`Waypoint ${index}: ${waypoint.lat}, ${waypoint.lng}`);
        //clearMarkers();
        addWaypointMarker(waypoint, `Optimized Waypoint ${index}`);
        showoptroute(waypoint);
    });

   
    showoptroute(optimizedWaypoints);
}
 


       
           
        function ride() {
    
         
        
        }




        function addWaypointMarker(position, query) {
            let marker = new tt.Marker().setLngLat(position).addTo(map);
            let popup = new tt.Popup().setLngLat(position).setText(query).addTo(map);
            marker.setPopup(popup);
        }

        function displayWaypoints(order) {
            let waypointsDiv = document.getElementById('waypoints');
            waypointsDiv.innerHTML = "<h3>Waypoints:</h3>";
            if (order) {
                order.forEach((index, i) => {
                    waypointsDiv.innerHTML += `<p>Waypoint ${i}: ${waypoints[index].lat}, ${waypoints[index].lon}</p>`;
                });
            } else {
                waypoints.forEach((waypoint, index) => {
                    waypointsDiv.innerHTML += `<p>Waypoint ${index + 1}: ${waypoint.lat}, ${waypoint.lon}</p>`;
                });
            }
        }

        // Function to calculate distance between two points (using haversine formula)
        function calculateDistance(point1, point2) {
            const earthRadius = 6371; // Radius of the Earth in kilometers
            const lat1 = toRadians(point1.lat);
            const lon1 = toRadians(point1.lng);
            const lat2 = toRadians(point2.lat);
            const lon2 = toRadians(point2.lng);

            const dLat = lat2 - lat1;
            const dLon = lon2 - lon1;

            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                      Math.cos(lat1) * Math.cos(lat2) *
                      Math.sin(dLon / 2) * Math.sin(dLon / 2);

            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

            const distance = earthRadius * c; // Distance in kilometers
            return distance;
        }

        // Function to convert degrees to radians
        function toRadians(degrees) {
            return degrees * Math.PI / 180;
        }


        
        // Function to save form data
        function save() {
            var label = document.getElementById('label').value;
            var latitude = document.getElementById('latitude').value;
            var longitude = document.getElementById('longitude').value;
            // Implement code to save this data to the database
        }

        // Function to show form
        function showForm(label, latitude, longitude) {
            document.getElementById('label').value = label;
            document.getElementById('latitude').value = latitude;
            document.getElementById('longitude').value = longitude;
            var form = document.getElementById('saveForm');
            form.style.display = 'block';
            var temp=document.getElementById('latitude').value+","+document.getElementById('longitude').value;
            search(temp);
        }

        function  createform()
        {
            var form = document.getElementById('saveForm');
            form.style.display = 'block';
        }

        // Function to close form
        function closeForm() {
            var form = document.getElementById('saveForm');
            form.style.display = 'none';
        }

        // Function to handle search button click
        function bindsearch(lat, lon) {
            var temp=lat.lon;
            search(temp);
        }
    </script>

<div id="saveForm" style="display: none;">
    <form method="post" action="savequery.php">
        <h2>Save Search Query</h2>
        <label for="label">Label:</label>
        <input type="text" id="label" name="label" ><br><br>
        <label for="latitude">Latitude:</label>
        <input type="text" id="latitude" name="latitude"><br><br>
        <label for="longitude">Longitude:</label>
        <input type="text" id="longitude" name="longitude"><br><br>
        <input type="button" value="Save" onclick="save()">
        <button type="button" onclick="closeForm()" >Cancel</button>
    </form>
</div>

<div id="storeButtons">
    <?php foreach ($stores as $store): ?>
        <button onclick='showForm("<?php echo $store["label"]; ?>", "<?php echo $store["lat"]; ?>", "<?php echo $store["lon"]; ?>"); bindsearch("<?php echo $store["lat"]; ?>", "<?php echo $store["lon"]; ?>");'><?php echo $store["label"]; ?></button>
    <?php endforeach; ?>
</div>
<br>
<div id="store_label">
    <button onclick="createform()">Add customized label</button>
</div>



</body>
</html>