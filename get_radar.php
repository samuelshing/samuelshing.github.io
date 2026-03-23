<?php
// get_radar.php

// 1. 設定標頭：允許跨來源請求 (CORS) 與指定回傳格式為 JSON
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

// 2. 定義氣象署的雷達 API 網址 (包含您的 Authorization Key)
// 注意：這裡直接寫死在 PHP 裡，前端就不用暴露 Key
$apiUrl = 'https://opendata.cwa.gov.tw/fileapi/v1/opendataapi/O-A0059-001?Authorization=CWA-F36D4CE9-10F1-43BA-82E6-8E7DA63697F7&downloadType=WEB&format=JSON';

// 3. 初始化 cURL
$ch = curl_init();

// 4. 設定 cURL 選項
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 將結果回傳成字串，而不是直接輸出
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // 【關鍵】這行允許 PHP 跟隨氣象署的 302 Redirect 跳轉到 Amazon S3
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 忽略 SSL 憑證檢查 (避免某些本機開發環境報錯)
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0'); // 偽裝成瀏覽器，避免被擋

// 5. 執行請求並取得資料
$response = curl_exec($ch);

// 6. 檢查是否有錯誤
if(curl_errno($ch)){
    // 如果失敗，回傳一個錯誤 JSON
    echo json_encode(['error' => curl_error($ch)]);
} else {
    // 7. 如果成功，直接將氣象署原本的 JSON 輸出給您的前端
    echo $response;
}

// 8. 關閉連線
curl_close($ch);
?>