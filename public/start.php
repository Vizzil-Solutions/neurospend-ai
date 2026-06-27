<?php
/**
 * NeuroSpend AI - Production Setup Terminal
 * Designed for robust execution in restricted/cPanel environments.
 */

// Disable output buffering
header('X-Accel-Buffering: no');
header('Content-Encoding: none');

$baseDir = dirname(__DIR__);

// Determine a writable log file path
$logFile = __DIR__ . '/setup_log.txt';
if (!is_writable(__DIR__)) {
    $storageLogs = $baseDir . '/storage/logs';
    if (!is_dir($storageLogs)) {
        @mkdir($storageLogs, 0755, true);
    }
    if (is_writable($storageLogs)) {
        $logFile = $storageLogs . '/setup_log.txt';
    }
}

// Handle actions
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'clear') {
    header('Content-Type: application/json');
    if (file_exists($logFile)) {
        @unlink($logFile);
    }
    echo json_encode(['status' => 'cleared']);
    exit;
}

if ($action === 'stream') {
    header('Content-Type: text/plain; charset=utf-8');
    if (file_exists($logFile)) {
        readfile($logFile);
    } else {
        echo "[PENDING] Waiting for execution to start...\n";
    }
    exit;
}

if ($action === 'run') {
    // Run setup tasks in background/AJAX request
    ignore_user_abort(true);
    set_time_limit(0);
    
    // Clear old log
    if (file_exists($logFile)) {
        @unlink($logFile);
    }
    
    function logMsg($msg) {
        global $logFile;
        file_put_contents($logFile, $msg . "\n", FILE_APPEND);
    }
    
    logMsg("=== NEUROSPEND IN-PROCESS SETUP STARTED ===");
    logMsg("Time: " . date('Y-m-d H:i:s'));
    logMsg("PHP Version: " . PHP_VERSION);
    logMsg("Host OS: " . PHP_OS_FAMILY . " (" . PHP_OS . ")");
    
    // 1. Copy .env
    logMsg("\n--- [STEP 1/4] Environment Configuration ---");
    $envPath = $baseDir . '/.env';
    if (!file_exists($envPath)) {
        if (file_exists($baseDir . '/.env.example')) {
            if (copy($baseDir . '/.env.example', $envPath)) {
                logMsg("SUCCESS: Created .env file from .env.example");
            } else {
                logMsg("ERROR: Failed to copy .env.example to .env. Please check folder permissions.");
            }
        } else {
            logMsg("ERROR: .env.example not found in " . $baseDir);
        }
    } else {
        logMsg("INFO: .env file already exists.");
    }
    
    // 2. Generate APP_KEY in pure PHP
    logMsg("\n--- [STEP 2/4] Generating Application Key ---");
    if (file_exists($envPath)) {
        $envContent = file_get_contents($envPath);
        $hasKey = false;
        if (preg_match('/^APP_KEY=(.+)$/m', $envContent, $matches)) {
            $existingKey = trim($matches[1]);
            if (!empty($existingKey)) {
                logMsg("INFO: Application key already exists in .env.");
                $hasKey = true;
            }
        }
        
        if (!$hasKey) {
            $newKey = 'base64:' . base64_encode(random_bytes(32));
            if (strpos($envContent, 'APP_KEY=') !== false) {
                $envContent = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $newKey, $envContent);
            } else {
                $envContent .= "\nAPP_KEY=" . $newKey . "\n";
            }
            if (file_put_contents($envPath, $envContent) !== false) {
                logMsg("SUCCESS: Generated secure key and saved to .env.");
            } else {
                logMsg("ERROR: Failed to write APP_KEY to .env.");
            }
        }
    } else {
        logMsg("ERROR: Cannot generate key because .env does not exist.");
    }
    
    // 3. Database Validation / Connection Test
    logMsg("\n--- [STEP 3/4] Database Configuration & Connection ---");
    $envContent = file_exists($envPath) ? file_get_contents($envPath) : '';
    $dbConnection = 'sqlite';
    $dbDatabase = '';
    if (preg_match('/^DB_CONNECTION=(.*)$/m', $envContent, $matches)) {
        $dbConnection = trim($matches[1]);
    }
    if (preg_match('/^DB_DATABASE=(.*)$/m', $envContent, $matches)) {
        $dbDatabase = trim($matches[1]);
    }
    
    if ($dbConnection === 'sqlite') {
        $sqliteFile = $dbDatabase;
        if (empty($sqliteFile)) {
            $sqliteFile = $baseDir . '/database/database.sqlite';
        } else {
            if (substr($sqliteFile, 0, 1) !== '/' && substr($sqliteFile, 1, 1) !== ':') {
                $sqliteFile = $baseDir . '/' . $sqliteFile;
            }
        }
        
        $sqliteDir = dirname($sqliteFile);
        if (!is_dir($sqliteDir)) {
            @mkdir($sqliteDir, 0755, true);
        }
        
        if (!file_exists($sqliteFile)) {
            if (@touch($sqliteFile)) {
                logMsg("SUCCESS: Created SQLite database file: " . str_replace($baseDir, '', $sqliteFile));
            } else {
                logMsg("ERROR: Failed to create SQLite database file at " . $sqliteFile);
            }
        } else {
            logMsg("INFO: SQLite database file exists.");
        }
    } else {
        logMsg("INFO: Custom connection '$dbConnection' configured. Testing database connection...");
        
        // Extract database credentials from .env
        $dbHost = '127.0.0.1';
        $dbPort = $dbConnection === 'pgsql' ? '5432' : '3306';
        $dbUser = '';
        $dbPass = '';
        
        if (preg_match('/^DB_HOST=(.*)$/m', $envContent, $matches)) { $dbHost = trim($matches[1]); }
        if (preg_match('/^DB_PORT=(.*)$/m', $envContent, $matches)) { $dbPort = trim($matches[1]); }
        if (preg_match('/^DB_USERNAME=(.*)$/m', $envContent, $matches)) { $dbUser = trim($matches[1]); }
        if (preg_match('/^DB_PASSWORD=(.*)$/m', $envContent, $matches)) { $dbPass = trim($matches[1]); }
        
        try {
            if ($dbConnection === 'pgsql') {
                $dsn = "pgsql:host=$dbHost;port=$dbPort;dbname=$dbDatabase";
            } else if ($dbConnection === 'mysql' || $dbConnection === 'mariadb') {
                $dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbDatabase";
            } else {
                $dsn = "$dbConnection:host=$dbHost;dbname=$dbDatabase";
            }
            
            $pdo = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5
            ]);
            logMsg("SUCCESS: Connected to database '$dbDatabase' on '$dbHost'.");
        } catch (\Throwable $e) {
            logMsg("ERROR: Failed to connect to the database. Error: " . $e->getMessage());
            logMsg("Please verify that your database credentials in '.env' are correct and that the database exists.");
        }
    }
    
    // 4. Programmatic Laravel Migrations (in-process)
    logMsg("\n--- [STEP 4/4] In-Process Database Migrations ---");
    $autoloadPath = $baseDir . '/vendor/autoload.php';
    if (!file_exists($autoloadPath)) {
        logMsg("\n[WARNING] 'vendor/autoload.php' is missing.");
        logMsg("Cannot run migrations because Composer dependencies are not present on the server.");
        logMsg("Please run 'composer install --no-dev' locally and upload your 'vendor/' folder.");
        logMsg("\n=== SETUP ENDED WITH WARNINGS ===");
        logMsg("[FINISHED]");
        exit;
    }
    
    try {
        logMsg("Booting Laravel framework in-memory...");
        require_once $autoloadPath;
        
        $appPath = $baseDir . '/bootstrap/app.php';
        if (!file_exists($appPath)) {
            logMsg("ERROR: 'bootstrap/app.php' not found.");
            logMsg("\n=== SETUP FAILED ===");
            logMsg("[FINISHED]");
            exit;
        }
        
        $app = require_once $appPath;
        $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
        
        logMsg("Calling 'migrate --force' programmatically...");
        $outputBuffer = new \Symfony\Component\Console\Output\BufferedOutput();
        
        $exitCode = $kernel->handle(
            new \Symfony\Component\Console\Input\ArrayInput([
                'command' => 'migrate',
                '--force' => true
            ]),
            $outputBuffer
        );
        
        $output = $outputBuffer->fetch();
        if (!empty($output)) {
            logMsg($output);
        }
        
        if ($exitCode === 0) {
            logMsg("SUCCESS: Migrations completed successfully!");
        } else {
            logMsg("ERROR: Migrations failed with exit code $exitCode.");
        }
    } catch (\Throwable $e) {
        logMsg("ERROR: Exception caught during programmatic migration:");
        logMsg($e->getMessage());
        logMsg($e->getTraceAsString());
    }
    
    logMsg("\n=== NEUROSPEND IN-PROCESS SETUP FINISHED ===");
    logMsg("[FINISHED]");
    exit;
}

// Check current state for UI
$stateEnvExists = file_exists($baseDir . '/.env');
$stateAutoloadExists = file_exists($baseDir . '/vendor/autoload.php');
$stateBuildExists = file_exists($baseDir . '/public/build/manifest.json') || glob($baseDir . '/public/build/assets/*.css');

// Determine database status and extension readiness
$stateDbReady = false;
$dbStatusLabel = 'Not Setup';
$dbConnection = 'sqlite';
if ($stateEnvExists) {
    $envContent = file_get_contents($baseDir . '/.env');
    $dbDatabase = '';
    if (preg_match('/^DB_CONNECTION=(.*)$/m', $envContent, $matches)) {
        $dbConnection = trim($matches[1]);
    }
    if (preg_match('/^DB_DATABASE=(.*)$/m', $envContent, $matches)) {
        $dbDatabase = trim($matches[1]);
    }
    
    if ($dbConnection === 'sqlite') {
        $sqliteFile = $dbDatabase;
        if (empty($sqliteFile)) {
            $sqliteFile = $baseDir . '/database/database.sqlite';
        } else {
            if (substr($sqliteFile, 0, 1) !== '/' && substr($sqliteFile, 1, 1) !== ':') {
                $sqliteFile = $baseDir . '/' . $sqliteFile;
            }
        }
        $stateDbReady = file_exists($sqliteFile);
        $dbStatusLabel = $stateDbReady ? 'Ready' : 'Not Setup';
    } else {
        // For non-sqlite connections, verify that the required PDO extension is loaded
        $extensionName = 'pdo_' . $dbConnection;
        if ($dbConnection === 'pgsql') {
            $extensionName = 'pdo_pgsql';
        } else if ($dbConnection === 'mysql' || $dbConnection === 'mariadb') {
            $extensionName = 'pdo_mysql';
        }
        $stateDbReady = extension_loaded($extensionName);
        $dbStatusLabel = $stateDbReady ? 'Ready' : "Missing $extensionName";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NeuroSpend AI | Setup Terminal</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;700&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0b0f19;
            --card-bg: rgba(17, 24, 39, 0.7);
            --card-border: rgba(255, 255, 255, 0.08);
            --terminal-bg: #030712;
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
            
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --primary-glow: rgba(79, 70, 229, 0.4);
            
            --success: #10b981;
            --success-glow: rgba(16, 185, 129, 0.2);
            --error: #ef4444;
            --error-glow: rgba(239, 68, 68, 0.2);
            --warning: #f59e0b;
            --info: #3b82f6;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            overflow-x: hidden;
            position: relative;
        }

        /* Decorative background glow */
        body::before {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.12) 0%, rgba(0, 0, 0, 0) 70%);
            top: -200px;
            left: -200px;
            z-index: 0;
            pointer-events: none;
        }
        body::after {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.08) 0%, rgba(0, 0, 0, 0) 70%);
            bottom: -200px;
            right: -200px;
            z-index: 0;
            pointer-events: none;
        }

        .container {
            width: 100%;
            max-width: 960px;
            z-index: 1;
        }

        .card {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
            padding: 32px;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            border-bottom: 1px solid var(--card-border);
            padding-bottom: 20px;
        }

        .brand-section {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary) 0%, #818cf8 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px var(--primary-glow);
            font-weight: 800;
            font-size: 20px;
            color: white;
        }

        .brand-name h1 {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.5px;
            background: linear-gradient(to right, #ffffff, #e0e7ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-name p {
            font-size: 13px;
            color: var(--text-muted);
        }

        .controls {
            display: flex;
            gap: 12px;
        }

        button {
            padding: 12px 24px;
            border-radius: 10px;
            font-family: 'Outfit', sans-serif;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            border: none;
            box-shadow: 0 4px 14px var(--primary-glow);
        }

        .btn-primary:hover:not(:disabled) {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.6);
        }

        .btn-primary:active:not(:disabled) {
            transform: translateY(0);
        }

        .btn-primary:disabled {
            background: #374151;
            color: #6b7280;
            cursor: not-allowed;
            box-shadow: none;
        }

        .btn-secondary {
            background: transparent;
            color: var(--text-main);
            border: 1px solid var(--card-border);
        }

        .btn-secondary:hover:not(:disabled) {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.15);
        }

        .btn-secondary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Status & Steps Panel */
        .status-panel {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .status-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .status-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
        }

        .pulse-dot {
            width: 10px;
            height: 10px;
            background-color: var(--text-muted);
            border-radius: 50%;
            display: inline-block;
        }

        .status-badge.idle .pulse-dot {
            background-color: var(--text-muted);
        }

        .status-badge.running .pulse-dot {
            background-color: var(--info);
            animation: pulse 1.5s infinite;
        }

        .status-badge.success .pulse-dot {
            background-color: var(--success);
            box-shadow: 0 0 8px var(--success);
        }

        .status-badge.error .pulse-dot {
            background-color: var(--error);
            box-shadow: 0 0 8px var(--error);
        }

        @keyframes pulse {
            0% { transform: scale(0.9); opacity: 0.6; }
            50% { transform: scale(1.2); opacity: 1; box-shadow: 0 0 10px var(--info); }
            100% { transform: scale(0.9); opacity: 0.6; }
        }

        /* Progress Steps */
        .steps-container {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin: 10px 0;
        }

        .steps-progress-line {
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background: rgba(255, 255, 255, 0.1);
            z-index: 1;
        }

        .steps-progress-bar {
            position: absolute;
            top: 15px;
            left: 0;
            width: 0%;
            height: 2px;
            background: linear-gradient(to right, var(--primary), var(--success));
            z-index: 2;
            transition: width 0.4s ease;
        }

        .step-node {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            z-index: 3;
            width: 60px;
            text-align: center;
        }

        .step-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--bg-color);
            border: 2px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-muted);
            transition: all 0.3s ease;
        }

        .step-node.active .step-circle {
            border-color: var(--primary);
            color: var(--primary);
            box-shadow: 0 0 12px var(--primary-glow);
        }

        .step-node.completed .step-circle {
            border-color: var(--success);
            background: var(--success);
            color: white;
        }

        .step-node.failed .step-circle {
            border-color: var(--error);
            background: var(--error);
            color: white;
        }

        .step-label {
            font-size: 11px;
            font-weight: 500;
            color: var(--text-muted);
            white-space: nowrap;
            transition: color 0.3s ease;
        }

        .step-node.active .step-label {
            color: var(--text-main);
            font-weight: 600;
        }

        .step-node.completed .step-label {
            color: var(--success);
        }

        /* Checklist Panel */
        .section-title {
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            margin-bottom: 12px;
        }

        .checklist-panel {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 20px;
        }

        .checklist-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }

        @media (min-width: 640px) {
            .checklist-grid {
                grid-template-columns: 1fr 1fr;
                gap: 16px;
            }
        }

        .checklist-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.01);
            border: 1px solid var(--card-border);
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .checklist-item.ready {
            border-color: rgba(16, 185, 129, 0.2);
            background: rgba(16, 185, 129, 0.02);
        }

        .checklist-item.missing {
            border-color: rgba(245, 158, 11, 0.2);
            background: rgba(245, 158, 11, 0.02);
        }

        .item-status {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            font-size: 12px;
            font-weight: 700;
        }

        .ready .status-icon {
            background: var(--success);
            color: white;
        }

        .missing .status-icon {
            background: var(--warning);
            color: #0b0f19;
        }

        .checklist-item .badge {
            font-size: 11px;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 12px;
            text-transform: uppercase;
        }

        .ready .badge {
            background: rgba(16, 185, 129, 0.15);
            color: var(--success);
        }

        .missing .badge {
            background: rgba(245, 158, 11, 0.15);
            color: var(--warning);
        }

        .guide-box {
            margin-top: 16px;
            padding: 16px;
            background: rgba(59, 130, 246, 0.03);
            border: 1px solid rgba(59, 130, 246, 0.15);
            border-radius: 8px;
            font-size: 13px;
        }

        .guide-box h3 {
            color: var(--info);
            font-size: 14px;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .guide-box p {
            color: var(--text-muted);
            line-height: 1.5;
            margin-bottom: 8px;
        }

        .guide-box ul {
            margin-left: 20px;
            margin-bottom: 8px;
            color: var(--text-main);
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .guide-box code {
            font-family: 'Fira Code', monospace;
            background: rgba(255, 255, 255, 0.06);
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 11.5px;
            color: #d4d4d4;
        }

        .guide-tip {
            font-style: italic;
            margin-top: 10px;
            color: var(--text-muted);
        }

        /* Terminal Window */
        .terminal-window {
            background-color: var(--terminal-bg);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 350px;
            box-shadow: inset 0 2px 8px rgba(0,0,0,0.8);
        }

        .terminal-header {
            background: rgba(255, 255, 255, 0.03);
            padding: 10px 16px;
            border-bottom: 1px solid var(--card-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .terminal-dots {
            display: flex;
            gap: 6px;
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .dot-red { background-color: #ff5f56; }
        .dot-yellow { background-color: #ffbd2e; }
        .dot-green { background-color: #27c93f; }

        .terminal-title {
            font-family: 'Fira Code', monospace;
            font-size: 12px;
            color: var(--text-muted);
        }

        .terminal-body {
            flex-grow: 1;
            padding: 16px;
            overflow-y: auto;
            font-family: 'Fira Code', monospace;
            font-size: 13px;
            line-height: 1.6;
            color: #d4d4d4;
            white-space: pre-wrap;
            word-break: break-all;
        }

        .terminal-body span {
            display: inline;
        }

        /* Scrollbar Styling */
        .terminal-body::-webkit-scrollbar {
            width: 8px;
        }
        .terminal-body::-webkit-scrollbar-track {
            background: var(--terminal-bg);
        }
        .terminal-body::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }
        .terminal-body::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .info-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            color: var(--text-muted);
            border-top: 1px solid var(--card-border);
            padding-top: 16px;
        }

        .info-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .info-footer a:hover {
            color: #818cf8;
            text-decoration: underline;
        }

        /* Ansi color translation overrides inside terminal styles */
        .ansi-bold { font-weight: bold; }
        .ansi-grey { color: #808080; }
        .ansi-red { color: #ef4444; }
        .ansi-green { color: #10b981; }
        .ansi-yellow { color: #f59e0b; }
        .ansi-blue { color: #3b82f6; }
        .ansi-magenta { color: #d946ef; }
        .ansi-cyan { color: #06b6d4; }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <!-- Header -->
        <div class="header">
            <div class="brand-section">
                <div class="logo-icon">N</div>
                <div class="brand-name">
                    <h1>NeuroSpend AI</h1>
                    <p>In-Process Setup Terminal (cPanel Compatible)</p>
                </div>
            </div>
            <div class="controls">
                <button id="btnClear" class="btn-secondary" onclick="clearLog()">Reset</button>
                <button id="btnRun" class="btn-primary" onclick="startSetup()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                    Run Database Setup
                </button>
            </div>
        </div>

        <!-- Status Panel -->
        <div class="status-panel">
            <div class="status-header">
                <span class="status-label">Overall Progress</span>
                <span id="statusBadge" class="status-badge idle">
                    <span class="pulse-dot"></span>
                    <span id="statusText">System Ready</span>
                </span>
            </div>
            
            <!-- Step Nodes -->
            <div class="steps-container">
                <div class="steps-progress-line"></div>
                <div id="progressBar" class="steps-progress-bar"></div>
                
                <div class="step-node" id="stepNode1">
                    <div class="step-circle">1</div>
                    <div class="step-label">Env File</div>
                </div>
                <div class="step-node" id="stepNode2">
                    <div class="step-circle">2</div>
                    <div class="step-label">App Key</div>
                </div>
                <div class="step-node" id="stepNode3">
                    <div class="step-circle">3</div>
                    <div class="step-label">DB Conn</div>
                </div>
                <div class="step-node" id="stepNode4">
                    <div class="step-circle">4</div>
                    <div class="step-label">Migrations</div>
                </div>
            </div>
        </div>

        <!-- Checklist Panel -->
        <div class="checklist-panel">
            <h2 class="section-title">Deployment Status Checklist</h2>
            <div class="checklist-grid">
                <div class="checklist-item <?php echo $stateEnvExists ? 'ready' : 'missing'; ?>">
                    <div class="item-status">
                        <span class="status-icon"><?php echo $stateEnvExists ? '✓' : '✗'; ?></span>
                        <span class="item-name">Environment Configuration (.env)</span>
                    </div>
                    <span class="badge"><?php echo $stateEnvExists ? 'Detected' : 'Missing'; ?></span>
                </div>
                
                <div class="checklist-item <?php echo $stateAutoloadExists ? 'ready' : 'missing'; ?>">
                    <div class="item-status">
                        <span class="status-icon"><?php echo $stateAutoloadExists ? '✓' : '✗'; ?></span>
                        <span class="item-name">Composer Dependencies (vendor/)</span>
                    </div>
                    <span class="badge"><?php echo $stateAutoloadExists ? 'Detected' : 'Action Required'; ?></span>
                </div>
                
                <div class="checklist-item <?php echo $stateBuildExists ? 'ready' : 'missing'; ?>">
                    <div class="item-status">
                        <span class="status-icon"><?php echo $stateBuildExists ? '✓' : '✗'; ?></span>
                        <span class="item-name">Compiled Assets (public/build/)</span>
                    </div>
                    <span class="badge"><?php echo $stateBuildExists ? 'Detected' : 'Action Required'; ?></span>
                </div>
                
                <div class="checklist-item <?php echo $stateDbReady ? 'ready' : 'missing'; ?>">
                    <div class="item-status">
                        <span class="status-icon"><?php echo $stateDbReady ? '✓' : '✗'; ?></span>
                        <span class="item-name">Database & PDO Driver (<?php echo htmlspecialchars($dbConnection); ?>)</span>
                    </div>
                    <span class="badge"><?php echo htmlspecialchars($dbStatusLabel); ?></span>
                </div>
            </div>
            
            <?php if (!$stateAutoloadExists || !$stateBuildExists): ?>
            <div class="guide-box">
                <h3>💡 Manual Upload Instructions</h3>
                <p>Since your server has terminal execution disabled, you must complete the setup by uploading these folders from your local machine:</p>
                <ul>
                    <?php if (!$stateAutoloadExists): ?>
                    <li><strong>Composer Vendor Folder</strong>: Run <code>composer install --no-dev --optimize-autoloader</code> on your local computer, zip the generated <code>vendor/</code> folder, upload it to your cPanel root using the File Manager, and extract it there.</li>
                    <?php endif; ?>
                    <?php if (!$stateBuildExists): ?>
                    <li><strong>Compiled Frontend Assets</strong>: Run <code>npm run build</code> on your local computer, zip the generated <code>public/build/</code> folder, upload it into the <code>public/</code> folder on your cPanel, and extract it.</li>
                    <?php endif; ?>
                </ul>
                <p class="guide-tip">Once uploaded, refresh this page to see the checklist turn green, then run the database setup!</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Terminal Window -->
        <div class="terminal-window">
            <div class="terminal-header">
                <div class="terminal-dots">
                    <div class="dot dot-red"></div>
                    <div class="dot dot-yellow"></div>
                    <div class="dot dot-green"></div>
                </div>
                <div class="terminal-title">php - in_process_setup.php</div>
                <div style="width: 48px;"></div> <!-- spacer -->
            </div>
            <div id="terminalBody" class="terminal-body">Click "Run Database Setup" to initialize the database configurations and in-process migrations...</div>
        </div>

        <!-- Footer Info -->
        <div class="info-footer">
            <span>Powered by NeuroSpend Heuristics Engine</span>
            <span><a href="/index.php">Go to Application &rarr;</a></span>
        </div>
    </div>
</div>

<script>
    let pollInterval = null;
    let isRunning = false;

    // Check if a log file already exists and populate it on page load
    window.addEventListener('DOMContentLoaded', () => {
        checkExistingLog();
    });

    function checkExistingLog() {
        fetch('start.php?action=stream')
            .then(res => res.text())
            .then(text => {
                if (text && !text.includes('[PENDING]')) {
                    updateTerminal(text);
                    // Determine state from log
                    if (text.includes('[FINISHED]')) {
                        setSystemStatus('success', 'Setup Completed');
                        updateStepsFromLog(text);
                    } else if (text.includes('=== SETUP FAILED ===') || text.includes('[FATAL ERROR]')) {
                        setSystemStatus('error', 'Setup Failed');
                        updateStepsFromLog(text);
                    } else if (text.includes('=== NEUROSPEND IN-PROCESS SETUP STARTED ===')) {
                        setSystemStatus('running', 'Restoring connection...');
                        startPolling();
                    }
                }
            });
    }

    function setSystemStatus(status, text) {
        const badge = document.getElementById('statusBadge');
        const badgeText = document.getElementById('statusText');
        const btnRun = document.getElementById('btnRun');
        
        badge.className = 'status-badge ' + status;
        badgeText.textContent = text;
        
        if (status === 'running') {
            isRunning = true;
            btnRun.disabled = true;
            document.getElementById('btnClear').disabled = true;
        } else {
            isRunning = false;
            btnRun.disabled = false;
            document.getElementById('btnClear').disabled = false;
        }
    }

    function startSetup() {
        if (isRunning) return;
        
        setSystemStatus('running', 'Executing tasks...');
        const terminal = document.getElementById('terminalBody');
        terminal.innerHTML = '<span style="color:#808080">Initializing process...</span>\n';
        
        // Trigger the run background action
        fetch('start.php?action=run')
            .catch(err => {
                console.error("Initiation request ended, execution continues in background.", err);
            });
            
        // Start polling immediately
        startPolling();
    }

    function clearLog() {
        if (isRunning) return;
        fetch('start.php?action=clear')
            .then(() => {
                document.getElementById('terminalBody').innerHTML = 'Click "Run Database Setup" to initialize the database configurations and in-process migrations...';
                setSystemStatus('idle', 'System Ready');
                // Reset step nodes
                for (let i = 1; i <= 4; i++) {
                    const node = document.getElementById('stepNode' + i);
                    node.className = 'step-node';
                }
                document.getElementById('progressBar').style.width = '0%';
            });
    }

    function startPolling() {
        if (pollInterval) clearInterval(pollInterval);
        
        pollInterval = setInterval(() => {
            fetch('start.php?action=stream')
                .then(res => res.text())
                .then(text => {
                    updateTerminal(text);
                    updateStepsFromLog(text);
                    
                    if (text.includes('[FINISHED]')) {
                        clearInterval(pollInterval);
                        setSystemStatus('success', 'Setup Completed');
                        // Reload page after a success to update checklist state
                        setTimeout(() => {
                            window.location.reload();
                        }, 2500);
                    } else if (text.includes('=== SETUP FAILED ===') || text.includes('[FATAL ERROR]')) {
                        clearInterval(pollInterval);
                        setSystemStatus('error', 'Setup Failed');
                    }
                })
                .catch(err => {
                    console.error("Polling error:", err);
                });
        }, 1000);
    }

    function updateTerminal(text) {
        const terminal = document.getElementById('terminalBody');
        // Parse and style logs
        let styledText = text;
        
        // Escape HTML
        styledText = styledText
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");

        // Translate ANSI codes
        styledText = styledText
            .replace(/\x1b\[0m/g, '</span>')
            .replace(/\x1b\[1m/g, '<span class="ansi-bold">')
            .replace(/\x1b\[30m/g, '<span class="ansi-grey">')
            .replace(/\x1b\[31m/g, '<span class="ansi-red">')
            .replace(/\x1b\[32m/g, '<span class="ansi-green">')
            .replace(/\x1b\[33m/g, '<span class="ansi-yellow">')
            .replace(/\x1b\[34m/g, '<span class="ansi-blue">')
            .replace(/\x1b\[35m/g, '<span class="ansi-magenta">')
            .replace(/\x1b\[36m/g, '<span class="ansi-cyan">')
            .replace(/\x1b\[39m/g, '</span>')
            .replace(/\x1b\[[0-9;]*m/g, ''); // strip other

        terminal.innerHTML = styledText;
        terminal.scrollTop = terminal.scrollHeight;
    }

    function updateStepsFromLog(text) {
        const steps = [
            { id: 1, key: 'STEP 1/4', progress: '25%' },
            { id: 2, key: 'STEP 2/4', progress: '50%' },
            { id: 3, key: 'STEP 3/4', progress: '75%' },
            { id: 4, key: 'STEP 4/4', progress: '100%' }
        ];

        let currentActive = 0;
        let lastCompleted = 0;
        let stepFailed = false;

        steps.forEach((step, index) => {
            const node = document.getElementById('stepNode' + step.id);
            if (text.includes(step.key)) {
                currentActive = step.id;
                
                let nextStepKeyExists = false;
                for (let j = index + 1; j < steps.length; j++) {
                    if (text.includes(steps[j].key)) {
                        nextStepKeyExists = true;
                        break;
                    }
                }
                
                const stepTextIndex = text.indexOf(step.key);
                const nextStepTextIndex = index < steps.length - 1 ? text.indexOf(steps[index + 1].key) : -1;
                
                let stepSegment = "";
                if (nextStepTextIndex !== -1) {
                    stepSegment = text.substring(stepTextIndex, nextStepTextIndex);
                } else {
                    stepSegment = text.substring(stepTextIndex);
                }
                
                if (stepSegment.includes('ERROR:') || (stepSegment.includes('exited with code') && !stepSegment.includes('code: 0'))) {
                    stepFailed = true;
                    node.className = 'step-node failed';
                } else if (nextStepKeyExists || text.includes('[FINISHED]')) {
                    node.className = 'step-node completed';
                    lastCompleted = step.id;
                } else {
                    node.className = 'step-node active';
                }
            } else {
                if (stepFailed) {
                    node.className = 'step-node';
                } else if (lastCompleted >= step.id) {
                    node.className = 'step-node completed';
                } else {
                    node.className = 'step-node';
                }
            }
        });

        // Update progress bar width
        const progressBar = document.getElementById('progressBar');
        if (text.includes('[FINISHED]')) {
            progressBar.style.width = '100%';
        } else if (currentActive > 0) {
            progressBar.style.width = steps[currentActive - 1].progress;
        } else {
            progressBar.style.width = '0%';
        }
    }
</script>
</body>
</html>
