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
    <title>Stores</title>
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
    <h1>Stores</h1>
    <div id="map"></div>
    
    <script src="https://api.tomtom.com/maps-sdk-for-web/cdn/6.x/6.15.0/maps/maps-web.min.js"></script>
    <script src="https://api.tomtom.com/maps-sdk-for-web/cdn/6.x/6.25.0/services/services-web.min.js"></script>
    <script>
        const apiKey = 'Lo1AJ40JYhvoOVjYevPKTJ3GlMVhiTKK';
        var map;

        // Initialize map
        map = tt.map({
            key: apiKey,
            container: 'map',
            center: [0, 0], // Initial center, will be updated later
            zoom: 15 // Adjust as needed
        });

        // Array to hold waypoints
        var waypoints = [];

        // Function to add waypoint marker
        function addWaypointMarker(position, query) {
            let marker = new tt.Marker().setLngLat(position).addTo(map);
            let popup = new tt.Popup().setLngLat(position).setText(query).addTo(map);
            marker.setPopup(popup);
        }

        // Function to display waypoints
        function displayWaypoints() {
            // Implement this function
        }

        // Function to show map
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

    <!-- Popup form to save search query -->
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

    <input type="text" id="query" value="" placeholder="Enter search query"/>
    <button onclick="search()">Search</button>
</body>
</html>
