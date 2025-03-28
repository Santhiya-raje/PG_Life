<?php

// Database connection parameters
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'pglife';

// Connect to the database
$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare and execute the stored procedure
$cityName = 'New York'; // Replace with the desired city name
$amenityName = 'Wi-Fi'; // Replace with the desired amenity name
$stmt = $conn->prepare("CALL GetAvailableProperties(?, ?)");
$stmt->bind_param("ss", $cityName, $amenityName);
$stmt->execute();

// Fetch and display the results
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "Property ID: " . $row['id'] . "<br>";
        echo "Property Name: " . $row['property_name'] . "<br>";
        echo "Address: " . $row['address'] . "<br>";
        echo "Rent: " . $row['rent'] . "<br>";
        echo "Cleanliness Rating: " . $row['rating_clean'] . "<br>";
        echo "Food Rating: " . $row['rating_food'] . "<br>";
        echo "Safety Rating: " . $row['rating_safety'] . "<br>";
        echo "Amenities: " . $row['amenities'] . "<br>";
        echo "Testimonial User: " . $row['testimonial_user'] . "<br>";
        echo "Testimonial Content: " . $row['testimonial_content'] . "<br>";
        echo "<br>";
    }
} else {
    echo "No properties available.";
}

// Close the statement and database connection
$stmt->close();
$conn->close();

?>