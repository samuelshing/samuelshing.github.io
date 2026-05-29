<?php
// get_radar_image.php
ini_set('display_errors', 0); 
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$token = '401c9ae1-1f9a-419e-a284-a3ac367cb21c';
$tempZip = 'radar_temp.zip';
$outputDir = 'radar_images/'; 

// 1. 確保儲存目錄存在
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}

// --- 2. 清理舊檔案機制 ---
// 在抓取新資料前，先刪除該目錄下的所有舊 PNG 檔
$outputDir = 'radar_images/'; 
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}

// 取得絕對路徑，確保 glob 運作正常
$absolutePath = realpath($outputDir) . DIRECTORY_SEPARATOR;
$oldFiles = glob($absolutePath . "*.png");

$debugInfo = [];
foreach ($oldFiles as $file) {
    if (is_file($file)) {
        // 嘗試強制解除佔用並刪除
        if (@unlink($file)) {
            $debugInfo[] = "Deleted: " . basename($file);
        } else {
            $error = error_get_last();
            $debugInfo[] = "Failed: " . basename($file) . " (Reason: " . $error['message'] . ")";
        }
    }
}

// 3. 串接 NCDR API 取得 ZIP
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://dataapi2.ncdr.nat.gov.tw/NCDR/MaxDBZPic');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['token: ' . $token]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$zipData = curl_exec($ch);
curl_close($ch);

if (empty($zipData)) {
    http_response_code(500);
    echo json_encode(["error" => "無法從 NCDR 取得資料"]);
    exit;
}

// 4. 儲存並解壓縮最新圖檔
file_put_contents($tempZip, $zipData);
$zip = new ZipArchive;
$savedFiles = [];

if ($zip->open($tempZip) === TRUE) {
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $fileName = $zip->getNameIndex($i);
        
        if (stripos($fileName, '.png') !== false) {
            $imageData = $zip->getFromIndex($i);
            // 儲存新圖檔
            file_put_contents($outputDir . $fileName, $imageData);
            $savedFiles[] = [
                "fileName" => $fileName,
                "url" => $outputDir . $fileName
            ];
        }
    }
    $zip->close();
    unlink($tempZip);

    // 依照檔名排序 (確保 s00, s01... 順序正確)
    sort($savedFiles);

    echo json_encode([
        "status" => "success",
        "count" => count($savedFiles),
        "files" => $savedFiles
    ]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "ZIP 檔案解壓失敗"]);
}