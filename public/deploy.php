<?php

declare(strict_types=1);

const DEPLOY_GITHUB_REPOSITORY = 'Shobahan758/rakib';
const DEPLOY_DEFAULT_BRANCH = 'master';
const DEPLOY_MAX_PAYLOAD_BYTES = 2 * 1024 * 1024;

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

function deployRespond(int $status, string $message): never
{
    http_response_code($status);
    echo json_encode(
        ['ok' => $status < 400, 'message' => $message],
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
    );
    exit;
}

function deployEnvironment(string $name, ?string $default = null): ?string
{
    $value = $_SERVER[$name] ?? $_ENV[$name] ?? getenv($name);

    return is_string($value) && $value !== '' ? $value : $default;
}

function deploySecret(string $projectRoot): ?string
{
    $secret = deployEnvironment('DEPLOY_WEBHOOK_SECRET');

    if ($secret !== null) {
        return $secret;
    }

    $secretFile = $projectRoot.'/.deploy-secret';
    if (! is_readable($secretFile)) {
        return null;
    }

    $secret = trim((string) file_get_contents($secretFile));

    return $secret !== '' ? $secret : null;
}

function deployWriteLog(string $logFile, string $message): void
{
    file_put_contents(
        $logFile,
        sprintf("[%s] %s\n", gmdate('Y-m-d\TH:i:s\Z'), $message),
        FILE_APPEND | LOCK_EX,
    );
}

function deployRun(string $command, string $directory, string $logFile): void
{
    $output = [];
    $exitCode = 0;
    $fullCommand = 'cd '.escapeshellarg($directory).' && '.$command.' 2>&1';

    exec($fullCommand, $output, $exitCode);
    deployWriteLog($logFile, '$ '.$command);

    if ($output !== []) {
        deployWriteLog($logFile, implode("\n", $output));
    }

    if ($exitCode !== 0) {
        throw new RuntimeException("Command exited with status {$exitCode}: {$command}");
    }
}

function deployComposerCommand(string $projectRoot): string
{
    $configured = deployEnvironment('DEPLOY_COMPOSER');
    if ($configured !== null) {
        return escapeshellarg($configured);
    }

    $localComposer = $projectRoot.'/composer.phar';
    if (is_file($localComposer)) {
        return escapeshellarg(PHP_BINARY).' '.escapeshellarg($localComposer);
    }

    foreach (['/opt/cpanel/composer/bin/composer', '/usr/local/bin/composer', '/usr/bin/composer'] as $composer) {
        if (is_executable($composer)) {
            return escapeshellarg($composer);
        }
    }

    return 'composer';
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    deployRespond(405, 'Only POST requests are accepted.');
}

if (! function_exists('exec')) {
    deployRespond(500, 'PHP exec is disabled on this server.');
}

if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > DEPLOY_MAX_PAYLOAD_BYTES) {
    deployRespond(413, 'Payload is too large.');
}

$projectRoot = dirname(__DIR__);
$secret = deploySecret($projectRoot);

if ($secret === null) {
    deployRespond(500, 'Webhook secret is not configured.');
}

$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

if (! is_string($payload) || $payload === '' || ! is_string($signature)) {
    deployRespond(400, 'Missing webhook payload or signature.');
}

$expectedSignature = 'sha256='.hash_hmac('sha256', $payload, $secret);
if (! hash_equals($expectedSignature, $signature)) {
    deployRespond(401, 'Invalid webhook signature.');
}

try {
    $data = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException) {
    deployRespond(400, 'Invalid JSON payload.');
}

if (! is_array($data)) {
    deployRespond(400, 'Webhook payload must be a JSON object.');
}

$event = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? '';
if ($event === 'ping') {
    deployRespond(200, 'GitHub webhook is configured correctly.');
}

if ($event !== 'push') {
    deployRespond(202, 'Event ignored.');
}

if (($data['repository']['full_name'] ?? '') !== DEPLOY_GITHUB_REPOSITORY) {
    deployRespond(403, 'Repository is not allowed to deploy.');
}

$branch = deployEnvironment('DEPLOY_BRANCH', DEPLOY_DEFAULT_BRANCH);
if (($data['ref'] ?? '') !== 'refs/heads/'.$branch) {
    deployRespond(202, 'Push was for a different branch.');
}

$appPath = rtrim(deployEnvironment('DEPLOY_APP_PATH', $projectRoot), DIRECTORY_SEPARATOR);
$logDirectory = $appPath.'/storage/logs';
$lockDirectory = $appPath.'/storage/framework';
$logFile = $logDirectory.'/deploy.log';
$lockFile = $lockDirectory.'/deploy.lock';

if (! is_dir($appPath) || ! is_dir($logDirectory) || ! is_dir($lockDirectory)) {
    deployRespond(500, 'Deployment path is not configured correctly.');
}

$lock = fopen($lockFile, 'c');
if ($lock === false || ! flock($lock, LOCK_EX | LOCK_NB)) {
    if (is_resource($lock)) {
        fclose($lock);
    }
    deployRespond(409, 'Another deployment is already running.');
}

ignore_user_abort(true);
set_time_limit(0);

$response = json_encode(
    ['ok' => true, 'message' => 'Deployment accepted.'],
    JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
);
http_response_code(202);
header('Content-Length: '.strlen($response));
header('Connection: close');
echo $response;

if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
} else {
    while (ob_get_level() > 0) {
        ob_end_flush();
    }
    flush();
}

$delivery = preg_replace('/[^a-zA-Z0-9-]/', '', (string) ($_SERVER['HTTP_X_GITHUB_DELIVERY'] ?? '')) ?: 'unknown';
$escapedBranch = escapeshellarg($branch);
$php = escapeshellarg(PHP_BINARY);

try {
    deployWriteLog($logFile, "Starting delivery {$delivery} for ".DEPLOY_GITHUB_REPOSITORY."@{$branch}");
    deployRun("git fetch --prune origin {$escapedBranch}", $appPath, $logFile);
    deployRun('git reset --hard '.escapeshellarg('origin/'.$branch), $appPath, $logFile);
    deployRun(deployComposerCommand($appPath).' install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader', $appPath, $logFile);
    deployRun("{$php} artisan optimize:clear", $appPath, $logFile);
    deployRun("{$php} artisan package:discover --ansi", $appPath, $logFile);
    deployRun("{$php} artisan migrate --force", $appPath, $logFile);
    deployRun("{$php} artisan storage:link", $appPath, $logFile);
    deployRun("{$php} artisan optimize", $appPath, $logFile);
    deployRun("{$php} artisan app:production-check", $appPath, $logFile);
    deployWriteLog($logFile, "Delivery {$delivery} completed successfully.");
} catch (Throwable $exception) {
    deployWriteLog($logFile, "Delivery {$delivery} failed: {$exception->getMessage()}");
} finally {
    flock($lock, LOCK_UN);
    fclose($lock);
}
