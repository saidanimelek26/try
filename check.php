<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Free Check FMI OFF / ON</title>

  <link rel="icon" href="/assets/newkt.png" type="image/x-icon" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }
    body {
      background: radial-gradient(circle at top left, #0f172a, #020617);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 30px;
      color: #fff;
      perspective: 1000px;
    }
    .container {
      max-width: 850px;
      width: 100%;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 20px;
      backdrop-filter: blur(20px);
      box-shadow: 0 0 30px rgba(255, 255, 255, 0.05);
      overflow: hidden;
      transform-style: preserve-3d;
      animation: fadeInCard 1s ease forwards;
    }
    @keyframes fadeInCard {
      from { transform: rotateX(-15deg) scale(0.95); opacity: 0; }
      to { transform: rotateX(0deg) scale(1); opacity: 1; }
    }
    header {
      text-align: center;
      padding: 30px;
      background: rgba(255, 255, 255, 0.02);
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }
    .logo lottie-player {
      width: 80px;
      height: 80px;
      margin: 0 auto 10px;
    }
    header h1 {
      font-size: 2.2rem;
      font-weight: 600;
    }
    header p {
      font-size: 0.95rem;
      color: #94a3b8;
      margin-top: 5px;
    }
    .imei-checker {
      padding: 30px;
    }
    .checker-form {
      background: rgba(255, 255, 255, 0.04);
      border-radius: 16px;
      padding: 25px;
      border: 1px solid rgba(255, 255, 255, 0.08);
      margin-bottom: 30px;
      transition: transform 0.3s ease;
    }
    .checker-form:hover {
      transform: translateY(-4px);
    }
    .form-group {
      margin-bottom: 20px;
    }
    .form-label {
      margin-bottom: 10px;
      font-size: 1rem;
    }
    .form-input {
      width: 100%;
      padding: 14px;
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 12px;
      color: #fff;
      font-size: 1rem;
      transition: all 0.3s ease;
    }
    .form-input:focus {
      border-color: #3b82f6;
      box-shadow: 0 0 15px rgba(59, 130, 246, 0.3);
      transform: scale(1.02);
      outline: none;
    }
    .btn {
      width: 100%;
      padding: 14px;
      border: none;
      border-radius: 12px;
      font-size: 1rem;
      font-weight: bold;
      background: linear-gradient(90deg, #3b82f6, #60a5fa);
      color: #fff;
      cursor: pointer;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .btn:hover {
      transform: translateY(-2px) scale(1.02);
      box-shadow: 0 6px 20px rgba(59, 130, 246, 0.45);
    }
    .btn:active {
      transform: scale(0.98);
    }
    .server-features {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  justify-content: center;
  margin-top: 25px; /* <-- Add this line to create space */
}

    .feature-tag {
      background: rgba(255, 255, 255, 0.07);
      border-radius: 30px;
      padding: 10px 18px;
      font-size: 0.85rem;
      display: flex;
      align-items: center;
      gap: 8px;
      color: #60a5fa;
    }
    .result-container {
      background: rgba(255, 255, 255, 0.04);
      border-left: 4px solid #3b82f6;
      border-radius: 16px;
      padding: 25px;
      display: none;
      animation: fadeIn 0.4s ease-out;
    }
    .result-header {
      display: flex;
      align-items: center;
      gap: 10px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      margin-bottom: 20px;
      padding-bottom: 10px;
    }
    .result-header i {
      font-size: 1.8rem;
      color: #3b82f6;
    }
    .result-content {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 15px;
    }
    .result-item {
      background: rgba(255, 255, 255, 0.06);
      padding: 15px;
      border-radius: 12px;
      color: #eee;
      box-shadow: inset 0 0 10px rgba(255,255,255,0.02);
    }
    .result-label {
      font-size: 0.85rem;
      color: #bbb;
      margin-bottom: 5px;
    }
    .result-value {
      font-size: 1rem;
      font-weight: 600;
    }
    .fmi-status-on { color: #ef4444; }
    .fmi-status-off { color: #22c55e; }
    .loading {
      text-align: center;
      padding: 20px;
      display: none;
    }
    .spinner {
      width: 50px;
      height: 50px;
      border: 5px solid rgba(59, 130, 246, 0.2);
      border-top: 5px solid #3b82f6;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin: auto;
    }
    .error-message {
      background: rgba(239, 68, 68, 0.1);
      color: #ef4444;
      padding: 15px;
      border-left: 4px solid #ef4444;
      border-radius: 12px;
      margin-top: 20px;
      display: none;
    }
    footer {
      text-align: center;
      padding: 20px;
      font-size: 0.8rem;
      background: rgba(255, 255, 255, 0.02);
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      color: #aaa;
    }
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    @media (max-width: 600px) {
      .imei-checker { padding: 20px; }
      .checker-form { padding: 20px; }
      .result-content { grid-template-columns: 1fr; }
    }
    .logo-3d {
  height: 90px;
  border-radius: 20px;
  transition: transform 0.6s ease, box-shadow 0.6s ease;
  animation: float 3s ease-in-out infinite;
  box-shadow: 0 10px 20px rgba(59, 130, 246, 0.25);
}

.logo-3d:hover {
  transform: translateY(-6px) scale(1.05) rotateX(5deg);
  box-shadow: 0 20px 30px rgba(59, 130, 246, 0.5);
}

@keyframes float {
  0%   { transform: translateY(0px); }
  50%  { transform: translateY(-8px); }
  100% { transform: translateY(0px); }
}

  </style>
</head>
<body>
  <div class="container">
    <header>
  <div class="logo">

</div>

  <h1>DEKAN-UNLOCK-Server</h1>
  <p>Check your device status with our free IMEI/Serial verification service</p>
</header>
        <section class="imei-checker">
            <div class="checker-form">
                <div class="form-group">
                    <label class="form-label">Enter IMEI or Serial</label>
                    <input type="text" id="imeiInput" class="form-input" placeholder="123456789012345">
                </div>
                <button class="btn btn-primary" onclick="checkIMEI()">
                    <i class="fas fa-search"></i> Check Device
                </button>
            </div>
            
            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p>Checking device status...</p>
            </div>
            
            <div class="error-message" id="errorMessage">
                <i class="fas fa-exclamation-circle"></i> <span id="errorText"></span>
            </div>
            
            <div class="result-container" id="resultContainer">
                <div class="result-header">
                    <i class="fas fa-mobile-alt"></i>
                    <h2>Device Status Report</h2>
                </div>
                <div class="result-content" id="resultContent">
                    <!-- Results will be displayed here -->
                </div>
            </div>
            
            <div class="server-features">
                <span class="feature-tag primary">
                    <i class="fas fa-check-circle"></i> FMI-Checkers
                </span>
                <span class="feature-tag secondary">
                    <i class="fas fa-tag"></i> Free IMEI/Serial Check!
                </span>
            </div>
        </section>
        
        <footer>
            <p>© 2025 DEKAN-UNLOCK-Server | Secure Device Verification</p>
        </footer>
    </div>
    
    <script>
    async function checkIMEI() {
        const imei = document.getElementById('imeiInput').value.trim();
        const loading = document.getElementById('loading');
        const resultContainer = document.getElementById('resultContainer');
        const resultContent = document.getElementById('resultContent');
        const errorMessage = document.getElementById('errorMessage');
        const errorText = document.getElementById('errorText');
        
        // Reset UI
        resultContainer.style.display = 'none';
        errorMessage.style.display = 'none';
        loading.style.display = 'block';
        
        // Validate input
        if (!imei) {
            showError('Please enter an IMEI/Serial number');
            loading.style.display = 'none';
            return;
        }
        
        if (!/^[0-9a-zA-Z]{10,20}$/.test(imei)) {
            showError('Invalid IMEI/Serial format (10-20 alphanumeric chars)');
            loading.style.display = 'none';
            return;
        }
        
        try {
            const response = await fetch(`check_fmi.php?imei=${encodeURIComponent(imei)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.error || 'Unknown error occurred');
            }
            
            displayResults(data.data);
            
        } catch (error) {
            console.error('Check failed:', error);
            showError(error.message || 'Failed to check device. Please try again later.');
        } finally {
            loading.style.display = 'none';
        }
    }
    
   function displayResults(data) {
    const resultContent = document.getElementById('resultContent');
    
    let modelHtml = `<div class="result-item">
        <div class="result-label">Device Model</div>
        <div class="result-value">${escapeHtml(data.model)}</div>
    </div>`;
    
    if (data.model === 'Unknown Model') {
        modelHtml += `<div class="result-item">
            <div class="result-label">Note</div>
            <div class="result-value" style="color: #ff9800;">
                Model detection service is temporarily unavailable
            </div>
        </div>`;
    }
    
    let html = modelHtml + `
        <div class="result-item">
            <div class="result-label">IMEI/SN</div>
            <div class="result-value">${escapeHtml(data.imei)}</div>
        </div>
        <div class="result-item">
            <div class="result-label">Find My Status</div>
            <div class="result-value">
                <span class="fmi-status-${data.fmi_status.toLowerCase()}">
                    ${escapeHtml(data.fmi_display)}
                </span>
            </div>
        </div>
    `;
    
    resultContent.innerHTML = html;
    document.getElementById('resultContainer').style.display = 'block';
}
    
    function showError(message) {
        const errorText = document.getElementById('errorText');
        errorText.textContent = message;
        document.getElementById('errorMessage').style.display = 'block';
    }
    
    // Basic HTML escaping
    function escapeHtml(unsafe) {
        if (!unsafe) return '';
        return unsafe.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
    
    // Enter key support
    document.getElementById('imeiInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') checkIMEI();
    });
    </script>
</body>
</html>