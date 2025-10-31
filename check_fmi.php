<?php
// Ensure no output before headers
if (ob_get_level()) ob_end_clean();

// Start tracking execution time
error_log("Starting FMI check process");
register_shutdown_function(function() {
    error_log("Script execution time: " . (microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]) . " seconds");
});

header('Content-Type: application/json');
error_reporting(0);

// Enhanced security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Validate request origin and method
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest' ||
    $_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'Invalid request']));
}

// Validate and sanitize IMEI input
$imei = isset($_GET['imei']) ? trim($_GET['imei']) : '';
if (empty($imei)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'IMEI/Serial is required']));
}
if (!preg_match('/^[0-9a-zA-Z]{10,20}$/', $imei)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'Invalid IMEI/Serial format (10-20 alphanumeric chars)']));
}

// Function to get device model from first API
function getDeviceModel($imei) {
    $myCheck = array(
        "service" => 0,
        "imei" => $imei,
        "key" => "FDA-ZHA-51Y-E47-VYW-21X-7OH-UK6"
    );
    
    $ch = curl_init("https://api.ifreeicloud.co.uk");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $myCheck);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    $myResult = json_decode(curl_exec($ch));
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if($httpcode == 200 && isset($myResult->success) && $myResult->success === true) {
        return $myResult->object->model ?? "Unknown Model";
    }
    return "Unknown Model";
}

// Configuration
$cacheFile = 'captcha_cache.json';
$cacheDuration = 30 * 60; // 30 minutes cache
$twoCaptchaApiKey = "7b52033dc202b6e080069c24188acc17";
$websiteURL = "https://dishwireless-trade.assurant.com/device-details-entry?mfgModel=Apple+iPhone+11+Pro&deviceType=Phone";
$websiteKey = "6LdzxnEnAAAAAL813t-OTERD-lCEpvB7hMJN8zhV";

// First get the device model
error_log("Getting device model for IMEI: $imei");
$deviceModel = getDeviceModel($imei);
error_log("Device model determined: $deviceModel");

// User agents for rotation
$userAgents = [
    "Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Mobile Safari/537.36",
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Safari/537.36",
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.3 Safari/605.1.15",
    "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.114 Safari/537.36",
    "Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1"
];
$selectedUserAgent = $userAgents[array_rand($userAgents)];

function solveRecaptchaV2() {
    global $twoCaptchaApiKey, $websiteURL, $websiteKey;
    
    error_log("Starting 2Captcha solving process");
    
    // Submit the captcha to 2Captcha
    $submitUrl = "http://2captcha.com/in.php?key=".$twoCaptchaApiKey."&method=userrecaptcha&googlekey=".$websiteKey."&pageurl=".urlencode($websiteURL)."&json=1";
    
    error_log("Submitting to 2Captcha: $submitUrl");
    $ch = curl_init($submitUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $submitResponse = curl_exec($ch);
    
    if (curl_errno($ch)) {
        error_log("2Captcha submit error: " . curl_error($ch));
        curl_close($ch);
        return false;
    }
    curl_close($ch);
    
    error_log("2Captcha submit response: " . $submitResponse);
    $submitData = @json_decode($submitResponse, true);
    
    if (!$submitData || !isset($submitData['status'])) {
        error_log("Invalid 2Captcha submit response");
        return false;
    }
    
    if ($submitData['status'] != 1) {
        error_log("2Captcha submit failed: " . print_r($submitData, true));
        return false;
    }
    
    $captchaId = $submitData['request'];
    error_log("2Captcha task created with ID: $captchaId");
    
    // Check for solution
    $resultUrl = "http://2captcha.com/res.php?key=".$twoCaptchaApiKey."&action=get&id=".$captchaId."&json=1";
    $startTime = time();
    $maxWaitTime = 120; // Max 2 minutes waiting time
    $firstCheckDelay = 15; // Wait 15 seconds before first check
    
    sleep($firstCheckDelay);
    
    while (time() - $startTime < $maxWaitTime) {
        error_log("Checking 2Captcha result (elapsed: " . (time() - $startTime) . "s)");
        $ch = curl_init($resultUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $resultResponse = curl_exec($ch);
        
        if (curl_errno($ch)) {
            error_log("2Captcha result check error: " . curl_error($ch));
            curl_close($ch);
            sleep(15);
            continue;
        }
        curl_close($ch);
        
        error_log("2Captcha result response: " . $resultResponse);
        $resultData = @json_decode($resultResponse, true);
        
        if (!$resultData) {
            error_log("Invalid 2Captcha result response");
            sleep(15);
            continue;
        }
        
        if ($resultData['status'] == 0) {
            if ($resultData['request'] == 'CAPCHA_NOT_READY') {
                error_log("Captcha not ready yet, waiting...");
                sleep(15);
                continue;
            } else {
                error_log("2Captcha solving error: " . print_r($resultData, true));
                return false;
            }
        }
        
        if ($resultData['status'] == 1) {
            error_log("Captcha solved successfully");
            return $resultData['request'];
        }
        
        sleep(15);
    }
    
    error_log("2Captcha timeout after " . (time() - $startTime) . " seconds");
    return false;
}

// Main FMI check process
$apiUrl = "https://api.assurantlogistics.com/cp-gf/api/External/ScanVi";
$postData = [
    "serialNumber"    => $imei,
    "modelIdentifier" => "99760000058455"
];

$maxRetries = 3;
$attempt = 0;
$status = "Unknown";
$response = "";

error_log("Starting FMI check with max $maxRetries attempts");

while ($attempt < $maxRetries) {
    $attempt++;
    error_log("Attempt $attempt of $maxRetries");
    
    $useCachedToken = false;
    if (file_exists($cacheFile)) {
        $cacheContent = file_get_contents($cacheFile);
        $cacheData = json_decode($cacheContent, true);
        if (isset($cacheData['timestamp']) && isset($cacheData['token'])) {
            if (time() < $cacheData['timestamp'] + $cacheDuration) {
                $recaptchaToken = $cacheData['token'];
                $useCachedToken = true;
                error_log("Using cached reCAPTCHA token");
            }
        }
    }
    
    if (!$useCachedToken) {
        error_log("Solving new reCAPTCHA");
        $recaptchaToken = solveRecaptchaV2();
        if (!$recaptchaToken) {
            error_log("Failed to solve reCAPTCHA on attempt $attempt");
            continue;
        }
        
        $cacheData = [
            'timestamp' => time(),
            'token'     => $recaptchaToken
        ];
        file_put_contents($cacheFile, json_encode($cacheData));
        error_log("Saved new reCAPTCHA token to cache");
    }
    
    // Prepare the API request
    $headers = [
        "Abp.TenantId: 22",
        "Accept: application/json, text/plain, */*",
        "Accept-Language: en-US,en;q=0.9",
        "Cache-Control: no-cache",
        "Connection: keep-alive",
        "Content-Type: application/json",
        "Identifier: 0edb17ed-5cf5-4106-bec4-4c3338e9f3f5",
        "Ocp-Apim-Subscription-Key: 2e407ba3cb2149dcbed1f068b20de834",
        "Origin: https://dishwireless-trade.assurant.com",
        "Recaptcha.IsV2: True",
        "Recaptcha.Token: " . $recaptchaToken,
        "Referer: https://dishwireless-trade.assurant.com/device-details-entry?mfgModel=Apple+iPhone+11+Pro&deviceType=Phone",
        "Sec-Fetch-Dest: empty",
        "Sec-Fetch-Mode: cors",
        "Sec-Fetch-Site: cross-site",
        "TenantName: Boost",
        "User-Agent: " . $selectedUserAgent,
        "dnt: 1"
    ];
    
    error_log("Making API request to Assurant");
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($postData),
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => $headers
    ]);
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curlError = curl_error($curl);
    curl_close($curl);
    
    if ($curlError) {
        error_log("CURL error: " . $curlError);
        continue;
    }
    
    if (empty($response)) {
        error_log("Empty response from API");
        continue;
    }
    
    error_log("API response (HTTP $httpCode): " . $response);
    $data = json_decode($response, true);
    
    if (isset($data['result']['fError'])) {
        $status = ($data['result']['fError'] === "warning") ? "ON" : "OFF";
        error_log("FMI status determined: $status");
        break;
    }
    
    error_log("Unexpected API response format");
}

// Return JSON response
$response = [
    'success' => $status !== "Unknown",
    'data' => [
        'model' => $deviceModel,
        'imei' => $imei,
        'fmi_status' => $status,
        'fmi_display' => $status === 'ON' ? '🔴 ON' : '✅ OFF',
        'attempts' => $attempt
    ]
];

if ($status === "Unknown") {
    $response['error'] = "Failed to determine FMI status after $attempt attempts";
    error_log("Failed to determine FMI status after all attempts");
}

error_log("Final response: " . json_encode($response));
die(json_encode($response));
?>