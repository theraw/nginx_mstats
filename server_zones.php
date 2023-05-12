<?php
require 'vendor/autoload.php'; // Include the Composer autoloader for Predis

$apiEndpoint = '   '; // Replace with your NGINX Plus API endpoint

// Set up the cURL request
$curl = curl_init();
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // Remove this line if using a trusted SSL certificate

// Fetch server zone information
$url = "{$apiEndpoint}/8/http/server_zones";
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

// Loop through each server zone
foreach ($data as $domain => $serverZone) {
    // Check if the zone exists in Redis
    if ($redis->hexists('nginx_stats', $domain)) {
        // Get the existing data from Redis
        $existingData = json_decode($redis->hget('nginx_stats', $domain), true);
        
        // Merge the new data with existing data
        $updatedData = array_merge($existingData, $serverZone);
        
        // Update the data in Redis
        $redis->hset('nginx_stats', $domain, json_encode($updatedData));
    } else {
        // If the zone does not exist, store the new data in Redis
        $redis->hset('nginx_stats', $domain, json_encode($serverZone));
    }
    
    // Set the expiry time for the hash
    $redis->expire('nginx_stats', $expiryTime);
}

// Close the cURL request
curl_close($curl);
?>
