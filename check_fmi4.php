<?php
// No HTML headers!
$imei = isset($_GET['imei']) ? trim($_GET['imei']) : '';
if (!$imei) { echo "IMEI/SN is required!"; exit; }

$api_url = 'http://127.0.0.1:5000/check';
$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['imei' => $imei]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 120);
$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($error) { echo "HTTP error: " . $error; exit; }

$json = json_decode($response, true);
if (!$json || !isset($json['success'])) {
    echo "API error: Invalid response\n" . $response; exit;
}

function device_html_to_telegram($html) {
    // Parse HTML block to DOM
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="UTF-8">' . $html);
    libxml_clear_errors();

    $xpath = new DOMXPath($doc);

    $model = '';
    $imei = '';
    $rows = [];
    $img = '';
    $checked = '';

    $modelNodes = $xpath->query("//div[contains(@style, 'font-size:2.1rem')]");
    if ($modelNodes->length) $model = trim($modelNodes->item(0)->nodeValue);

    $imgNodes = $xpath->query("//img");
    if ($imgNodes->length) $img = $imgNodes->item(0)->getAttribute('src');

    $popupDiv = $xpath->query("//div[contains(@class, 'device-popup')]");
    if ($popupDiv->length) {
        $innerHtml = $doc->saveHTML($popupDiv->item(0));
        preg_match_all('/([A-Za-z0-9 \/\-\(\)\:]+):\s*(.*?)\s*<br>?/i', $innerHtml, $matches, PREG_SET_ORDER);
        foreach ($matches as $m) {
            $rows[] = [$m[1], strip_tags($m[2])];
        }
    }

    // Fallback extraction
    if (!$rows && preg_match_all('/<div[^>]*>([\w \/\-\(\)\:]+)\:?\s*<\/div>\s*<div[^>]*>(.*?)<\/div>/si', $html, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $m) {
            $rows[] = [strip_tags($m[1]), strip_tags($m[2])];
        }
    }

    if (preg_match('/IMEI\:\s*([0-9]+)/i', $html, $m)) $imei = $m[1];
    if (preg_match('/Checked\:\s*([0-9a-zA-Z\:\, ]+)/i', $html, $m)) $checked = $m[1];

    $msg = '';
    if ($img) $msg .= "<a href=\"$img\">&#8205;</a>\n";
    $msg .= "<b>$model</b>\n";
    if ($imei) $msg .= "<b>IMEI:</b> <code>$imei</code>\n";
    $emoji = [
        'Activated' => '🟢', 'Active' => '🟢', 'Yes' => '🟢', 'Unlocked' => '🟢',
        'No' => '🔴', 'OFF' => '🔴', 'Locked' => '🔴',
        'ON' => '🔴', 'Device Activated' => '🟢', 'Device Not Activated' => '🔴',
        'Limited Warranty' => '🔵', 'Expired' => '⚪️'
    ];
    foreach ($rows as $row) {
        $field = trim($row[0]);
        $val = trim($row[1]);
        $e = '';
        foreach ($emoji as $k => $v) {
            if (stripos($val, $k) !== false) $e = $v;
        }
        $msg .= "<b>$field:</b> $e <code>$val</code>\n";
    }
    if ($checked) $msg .= "<i>Checked:</i> <code>$checked</code>\n";
    $msg .= "<i>ibst.services</i>";

    return $msg;
}

if ($json['success']) {
    $telegramText = device_html_to_telegram($json['html']);
    // Output *only* text for Telegram bot to send
    echo $telegramText;
} else {
    // Output error as plain text
    echo "Error: " . $json['html'];
}
?>
