<?php
ini_set('memory_limit', '512M');
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$token = '401c9ae1-1f9a-419e-a284-a3ac367cb21c';
$targetFile = 'radar_data.json';

// 您定義的三個關鍵點
$targetPoints = [
    ["name" => "新店", "lat" => 24.9828108, "lng" => 121.541716],
    ["name" => "中和", "lat" => 25.002385, "lng" => 121.496251],
    ["name" => "板橋", "lat" => 25.0153035, "lng" => 121.464625]
];

$url = 'https://dataapi2.ncdr.nat.gov.tw/NCDR/MaxDBZ?DataFormat=JSON';
$options = ["http" => ["header" => "token: " . $token . "\r\n"]];
$rawJson = @file_get_contents($url, false, stream_context_create($options));

if ($rawJson === FALSE) {
    echo file_exists($targetFile) ? file_get_contents($targetFile) : json_encode(["error" => "API連線失敗"]);
    exit;
}

$allData = json_decode($rawJson, true);
unset($rawJson);

$finalResults = [];

// 遍歷我們定義的站點
foreach ($targetPoints as $station) {
    $minDist = 999999;
    $targetGrid = null;

    // 從 20 萬筆資料中找出離該站點最近的格點
    foreach ($allData['Data'] as $point) {
        $dist = pow($point['Lon'] - $station['lng'], 2) + pow($point['Lat'] - $station['lat'], 2);
        if ($dist < $minDist) {
            $minDist = $dist;
            $targetGrid = $point;
        }
    }

    if ($targetGrid) {
        // 強制寫入站點名稱，即使格點座標重複，名稱也會不同
        $targetGrid['StationName'] = $station['name'];
        // 為了確保前端顯示，將 -0.001 統一轉為 0
        foreach (['T','T+1','T+2','T+3','T+4','T+5','T+6'] as $tKey) {
            if ($targetGrid[$tKey] < 0) $targetGrid[$tKey] = 0;
        }
        $finalResults[] = $targetGrid;
    }
}

$output = [
    "RecDateTime" => $allData['RecDateTime'],
    "UpdatedTime" => date("Y-m-d H:i:s"),
    "Data" => $finalResults // 這裡現在保證會有與 $targetPoints 數量相同的資料
];

$finalJson = json_encode($output);
file_put_contents($targetFile, $finalJson);
echo $finalJson;