<?php
require 'vendor/autoload.php'; // Include the Composer autoloader for Predis

// Connect to Redis
$redis = new Predis\Client([
    'host' => '172.69.0.78',
    'port' => '6379',
]);

// Get all upstream keys
$upstreamKeys = $redis->keys('nginx_stats:upstreams:*');

// Loop through each upstream key
foreach ($upstreamKeys as $upstreamKey) {
    echo "Upstream: " . substr($upstreamKey, 21) . "<br>" . PHP_EOL;

    // Get all peers within the upstream
    $peers = $redis->hgetall($upstreamKey);

    // Loop through each peer
    foreach ($peers as $peerId => $peerData) {
        $peer = json_decode($peerData, true);

        echo "Peer ID: " . $peerId . "<br>" . PHP_EOL;
        echo "Server: " . $peer['server'] . "<br>" . PHP_EOL;
        echo "State: " . $peer['state'] . "<br>" . PHP_EOL;
        echo "Requests: " . $peer['requests'] . "<br>" . PHP_EOL;
        // Display additional fields as needed

        echo "<br>" . PHP_EOL;
    }

    echo "<br>" . PHP_EOL;
}
?>
