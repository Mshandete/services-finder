<?php
include "db.php";

$data = json_decode(file_get_contents("php://input"), true);

$service_name = $data['service_name'];
$category     = $data['category'];
$phone        = $data['phone'];
$location     = $data['location_name'];
$lat          = $data['latitude'];
$lng          = $data['longitude'];

$sql = "INSERT INTO services (service_name, category, phone, location_name, latitude, longitude)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssdd", $service_name, $category, $phone, $location, $lat, $lng);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Service added successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to add service"]);
}
?>