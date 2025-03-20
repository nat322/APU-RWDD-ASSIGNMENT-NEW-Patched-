<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["error" => "Invalid request method"]);
    exit;
}

// Read JSON input
$inputData = json_decode(file_get_contents("php://input"), true);

// Ensure input data is valid
if (empty($inputData) || !isset($inputData['input'])) {
    echo json_encode(["error" => "No valid data provided"]);
    exit;
}

// Define the Node.js server endpoint
$nodeServerUrl = "http://localhost:3000/get-advice";

// Send data to Node.js server using cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $nodeServerUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_FAILONERROR, true);  // ✅ Ensures HTTP errors are caught
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["input" => $inputData["input"]]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

// Execute request and handle errors
$response = curl_exec($ch);
if (curl_errno($ch)) {
    echo json_encode(["error" => "Node.js server connection failed: " . curl_error($ch)]);
    exit;
}
curl_close($ch);

// Return response from Node.js server
echo $response;
