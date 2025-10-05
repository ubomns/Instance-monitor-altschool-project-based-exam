<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Function to get IMDSv2 token
function getIMDSv2Token() {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://169.254.169.254/latest/api/token");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'X-aws-ec2-metadata-token-ttl-seconds: 21600'
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    $token = curl_exec($ch);
    curl_close($ch);
    return $token;
}

// Function to get EC2 metadata with IMDSv2
function getEC2Metadata($path, $token) {
    $url = "http://169.254.169.254/latest/meta-data/" . $path;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'X-aws-ec2-metadata-token: ' . $token
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

// Get IMDSv2 token
$token = getIMDSv2Token();

if (!$token) {
    echo json_encode([
        'error' => 'Unable to get IMDS token',
        'message' => 'EC2 metadata service unavailable'
    ]);
    exit;
}

// Get instance information
$instanceInfo = [
    'instance_id' => getEC2Metadata('instance-id', $token),
    'public_ipv4' => getEC2Metadata('public-ipv4', $token),
    'private_ipv4' => getEC2Metadata('local-ipv4', $token),
    'availability_zone' => getEC2Metadata('placement/availability-zone', $token),
    'region' => getEC2Metadata('placement/region', $token),
    'instance_type' => getEC2Metadata('instance-type', $token),
    'hostname' => gethostname()
];

// Set location to availability zone
$instanceInfo['location'] = $instanceInfo['availability_zone'];

// Set timezone based on AWS region
$regionTimezones = [
    'us-east-1' => 'America/New_York',
    'us-east-2' => 'America/New_York',
    'us-west-1' => 'America/Los_Angeles',
    'us-west-2' => 'America/Los_Angeles',
    'eu-west-1' => 'Europe/Dublin',
    'eu-west-2' => 'Europe/London',
    'eu-central-1' => 'Europe/Frankfurt',
    'ap-southeast-1' => 'Asia/Singapore',
    'ap-southeast-2' => 'Australia/Sydney',
    'ap-south-1' => 'Asia/Mumbai',
    'ap-northeast-1' => 'Asia/Tokyo',
    'ap-northeast-2' => 'Asia/Seoul',
    'sa-east-1' => 'America/Sao_Paulo',
    'ca-central-1' => 'America/Toronto',
];
$instanceInfo['timezone'] = $regionTimezones[$instanceInfo['region']] ?? 'UTC';

echo json_encode($instanceInfo);
?>