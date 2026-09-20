<?php

declare(strict_types=1);

const DEPLOY_URL_TOKEN_HASH = '7c1f2f1fe2bff55b1f9e94b25895af79dd3096b80703dff0a77e25f6a2224ad4';
const DEPLOY_GITHUB_REPOSITORY = 'Shobahan758/bargur';

/*
 * GitHub webhook deployment endpoint.
 *
 * Server environment variables:
 *   DEPLOY_WEBHOOK_SECRET  Optional alternative to URL-token authentication.
 *   DEPLOY_APP_PATH        Optional. Defaults to the Laravel project root.
 *   DEPLOY_BRANCH          Optional. Defaults to "master".
 *   DEPLOY_COMPOSER        Optional. Defaults to "composer".
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

$defaultAppPath = dirname(__DIR__);
$autoloadFile = $defaultAppPath.'/vendor/autoload.php';

if (is_file($autoloadFile)) {
    require_once $autoloadFile;

    if (class_exists(Dotenv\Dotenv::class)) {
        Dotenv\Dotenv::createImmutable($defaultAppPath)->safeLoad();
    }
}

function respond(int $status, string $message): never
{
    http_response_code($status);
    echo json_encode(['message' => $message], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function environment(string $name, ?string $default = null): ?string
{
    $value = $_SERVER[$name] ?? $_ENV[$name] ?? getenv($name);

    return $value === false || $value === '' ? $default : $value;
}

function runCommand(string $command, string $directory, string $logFile): void
{
    $output = [];
    $exitCode = 0;
    $fullCommand = 'cd '.escapeshellarg($directory).' && '.$command.' 2>&1';

    exec($fullCommand, $output, $exitCode);

    $entry = sprintf(
        "[%s] $ %s\n%s\nExit code: %d\n\n",
        date(DATE_ATOM),
        $command,
        implode("\n", $output),
        $exitCode,
    );
    file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);

    if ($exitCode !== 0) {
        throw new RuntimeException('Deployment command failed. Check storage/logs/deploy.log.');
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    respond(405, 'Only POST requests are accepted.');
}

$contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);

if ($contentLength > 2 * 1024 * 1024) {
    respond(413, 'Payload is too large.');
}

$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

if ($payload === false) {
    respond(400, 'Missing webhook payload.');
}

$secret = environment('DEPLOY_WEBHOOK_SECRET');
$urlToken = is_string($_GET['token'] ?? null) ? $_GET['token'] : '';
$validUrlToken = $urlToken !== '' && hash_equals(DEPLOY_URL_TOKEN_HASH, hash('sha256', $urlToken));
$validSignature = false;

if ($secret !== null && $signature !== '') {
    $validSignature = hash_equals('sha256='.hash_hmac('sha256', $payload, $secret), $signature);
}

if (! $validUrlToken && ! $validSignature) {
    respond(401, 'Invalid webhook authentication.');
}

$event = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? '';

if ($event === 'ping') {
    respond(200, 'GitHub webhook is configured correctly.');
}

if ($event !== 'push') {
    respond(202, 'Event ignored.');
}

$data = json_decode($payload, true);

if (! is_array($data)) {
    respond(400, 'Invalid JSON payload.');
}

if (($data['repository']['full_name'] ?? '') !== DEPLOY_GITHUB_REPOSITORY) {
    respond(403, 'Repository is not allowed to deploy.');
}

$branch = environment('DEPLOY_BRANCH', 'master');

if (($data['ref'] ?? '') !== 'refs/heads/'.$branch) {
    respond(202, 'Push was for a different branch.');
}

$appPath = rtrim(environment('DEPLOY_APP_PATH', dirname(__DIR__)), DIRECTORY_SEPARATOR);
$composer = environment('DEPLOY_COMPOSER', 'composer');
$logDirectory = $appPath.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'logs';
$logFile = $logDirectory.DIRECTORY_SEPARATOR.'deploy.log';
$lockFile = $appPath.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'deploy.lock';

if (! is_dir($appPath) || ! is_dir($logDirectory)) {
    respond(500, 'Deployment path is not configured correctly.');
}

$lock = fopen($lockFile, 'c');

if ($lock === false || ! flock($lock, LOCK_EX | LOCK_NB)) {
    respond(409, 'Another deployment is already running.');
}

try {
    set_time_limit(300);

    runCommand('git pull --ff-only origin '.escapeshellarg($branch), $appPath, $logFile);
    // Install the new code's dependencies before booting Artisan. Run package
    // discovery explicitly after stale configuration has been cleared.
    runCommand(escapeshellcmd($composer).' install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader', $appPath, $logFile);
    // Clear stale route/view/config caches before the new application code boots.
    runCommand('php artisan optimize:clear', $appPath, $logFile);
    runCommand('php artisan package:discover --ansi', $appPath, $logFile);
    runCommand('php artisan migrate --force', $appPath, $logFile);
    runCommand('php artisan storage:link', $appPath, $logFile);
    runCommand('php artisan optimize', $appPath, $logFile);
    runCommand('php artisan app:production-check', $appPath, $logFile);

    file_put_contents(
        $logFile,
        sprintf("[%s] Deployment completed for commit %s.\n\n", date(DATE_ATOM), $data['after'] ?? 'unknown'),
        FILE_APPEND | LOCK_EX,
    );

    respond(200, 'Deployment completed successfully.');
} catch (Throwable $exception) {
    file_put_contents(
        $logFile,
        sprintf("[%s] ERROR: %s\n\n", date(DATE_ATOM), $exception->getMessage()),
        FILE_APPEND | LOCK_EX,
    );

    respond(500, $exception->getMessage());
} finally {
    flock($lock, LOCK_UN);
    fclose($lock);
}
