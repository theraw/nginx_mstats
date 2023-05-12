<?php
require 'vendor/autoload.php'; // Include the Composer autoloader for Predis

// Connect to Redis
$redis = new Predis\Client([
    'host' => '172.69.0.78',
    'port' => '6379',
]);

// Get all the server zone domains
$zoneKeys = $redis->keys('nginx_status:server_zones:*');
// Loop through each domain and retrieve the data
foreach ($zoneKeys as $zoneKey) {
    // Get the domain from the zone key
    $domain = rtrim(str_replace('nginx_status:server_zones:', '', $zoneKey), ':');

    // Get the data for the domain from Redis
    $data = $redis->hget($zoneKey, 'data');

    // Check if data exists for the domain
    if ($data) {
        // Decode the JSON-encoded data
        $serverZoneData = json_decode($data, true);

        // Display the domain and its data
        echo "Domain: $domain" . PHP_EOL;
        echo "Processing: " . $serverZoneData['processing'] . PHP_EOL;
        echo "Requests: " . $serverZoneData['requests'] . PHP_EOL;
        echo "Responses:" . PHP_EOL;
        echo "   1xx: " . $serverZoneData['responses']['1xx'] . PHP_EOL;
        echo "   2xx: " . $serverZoneData['responses']['2xx'] . PHP_EOL;
        echo "   3xx: " . $serverZoneData['responses']['3xx'] . PHP_EOL;
        echo "   4xx: " . $serverZoneData['responses']['4xx'] . PHP_EOL;
        echo "   5xx: " . $serverZoneData['responses']['5xx'] . PHP_EOL;
        // Display other data fields as needed

        echo PHP_EOL; // Add a line break between each domain's data
    }
}
?>
