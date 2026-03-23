<?php
// get_radar_image.php

// 1. 設定跨域標頭
header('Access-Control-Allow-Origin: *'); 
header('Content-Type: application/json; charset=utf-8');

// 2. 您的雷達圖片 API (JSON 格式)
$apiUrl = 'https://opendata.cwa.gov.tw/fileapi/v1/opendataapi/O-A0084-001?Authorization=CWA-F36D4CE9-10F1-43BA-82E6-8E7DA63697F7&downloadType=WEB&format=JSON';

// 3. 初始化 cURL
$ch = curl_init();

// 4. 設定 cURL
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // 關鍵：允許轉址
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');

// 5. 執行並回傳
$response = curl_exec($ch);

if(curl_errno($ch)){
    echo json_encode(['error' => curl_error($ch)]);
} else {
    echo $response;
}

curl_close($ch);
?>