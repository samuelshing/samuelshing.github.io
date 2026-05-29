<?php
// get_track_temp.php

// 1. 設定標頭：允許跨來源請求 (CORS) 與指定回傳格式為 JSON
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

// 2. 定義軌道溫度 API 網址 (內部 Private IP)
$apiUrl = 'http://172.16.1.13:8388/tmpMonitorCir/tmpFunc.php';

// 3. 初始化 cURL
$ch = curl_init();

// 4. 設定 cURL 選項
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_POST, true); // 使用 POST 方法
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['action' => 'getLastTemp'])); // 【關鍵】傳送 API 所需的 action 參數
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 將結果回傳成字串
curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 設定超時時間為 5 秒，避免卡死
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 忽略 SSL 檢查

// 5. 執行請求並取得資料
$response = curl_exec($ch);

// 6. 檢查是否有錯誤或超時 (例如在本機環境連不到 172.16.1.13)
if (curl_errno($ch) || empty($response)) {
    // 連線失敗時，不提供模擬數據，回傳錯誤狀態與錯誤訊息
    http_response_code(502); // 設置 502 Bad Gateway 狀態碼
    echo json_encode([
        'result' => false,
        'error' => curl_error($ch) ?: '無法連線至軌道溫度監控伺服器，或回應內容為空。'
    ]);
} else {
    // 7. 如果成功，直接輸出 API 的 JSON 回應
    echo $response;
}

// 8. 關閉連線
curl_close($ch);
?>
