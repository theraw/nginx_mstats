<?php
require 'vendor/autoload.php'; // Include the Composer autoloader for Predis

$apiEndpoint = '   '; // Replace with your NGINX Plus API endpoint

// Set up the cURL request
$curl = curl_init();
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // Remove this line if using a trusted SSL certificate

// Fetch upstream information
$url = "{$apiEndpoint}/8/http/upstreams";
curl_setopt($curl, CURLOPT_URL, $url);
$response = curl_exec($curl);

// Check for errors
if (curl_errno($curl)) {
    echo 'Error: ' . curl_error($curl);
    exit;
}

// Parse the JSON response
$data = json_decode($response, true);

// Connect to Redis
$redis = new Predis\Client([
    'host' => '172.69.0.78',
    'port' => '6379',
]);

// Set the expiry time to 30 days (in seconds)
$expiryTime = 30 * 24 * 60 * 60;

// Loop through each upstream
foreach ($data as $upstreamName => $upstream) {
    // Create a key for the upstream peers
    $upstreamKey = "nginx_stats:upstreams:{$upstreamName}:peers:";

    // Loop through each peer within the upstream
    foreach ($upstream['peers'] as $peer) {
        // Store the peer data in Redis
        $redis->hset($upstreamKey, $peer['id'], json_encode($peer));

        // Set the expiry time for the hash
        $redis->expire($upstreamKey, $expiryTime);
    }
}

// Close the cURL request
curl_close($curl);
?>
