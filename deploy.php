<?php

declare(strict_types=1);

const DEPLOY_WEBHOOK_SECRET = '5c39be1dd185024183b6edd2dff3e47efdb3a74bba5dfcff56e78ea2396aa84e';
const DEPLOY_REPOSITORY = 'Shobahan758/s';
const DEPLOY_BRANCH = 'master';

function jsonResponse(int $status, array $body): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($body, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    exit;
}

function requestHeader(string $name): string
{
    $key = 'HTTP_'.strtoupper(str_replace('-', '_', $name));

    return isset($_SERVER[$key]) && is_string($_SERVER[$key]) ? $_SERVER[$key] : '';
}

function writeDeployLog(string $message): void
{
    $line = sprintf("[%s] %s\n", gmdate('Y-m-d\TH:i:s\Z'), $message);
    file_put_contents(__DIR__.'/storage/logs/deploy.log', $line, FILE_APPEND | LOCK_EX);
}

function runDeployCommand(string $command): void
{
    $output = [];
    $exitCode = 0;
    exec($command.' 2>&1', $output, $exitCode);

    writeDeployLog('$ '.$command);
    if ($output !== []) {
        writeDeployLog(implode("\n", $output));
    }

    if ($exitCode !== 0) {
        throw new RuntimeException("Command failed with exit code {$exitCode}: {$command}");
    }
}

function composerCommand(): string
{
    $localComposer = __DIR__.'/composer.phar';
    if (is_file($localComposer)) {
        return escapeshellarg(PHP_BINARY).' '.escapeshellarg($localComposer);
    }

    foreach (['/opt/cpanel/composer/bin/composer', '/usr/local/bin/composer', '/usr/bin/composer'] as $composer) {
        if (is_file($composer) && is_executable($composer)) {
            return escapeshellarg($composer);
        }
    }

    return 'composer';
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    jsonResponse(405, ['ok' => false, 'message' => 'Method not allowed']);
}

if (! function_exists('exec')) {
    jsonResponse(500, ['ok' => false, 'message' => 'PHP exec is disabled on this server']);
}

$payload = file_get_contents('php://input');
$signature = requestHeader('X-Hub-Signature-256');

if (! is_string($payload) || $payload === '' || ! str_starts_with($signature, 'sha256=')) {
    jsonResponse(403, ['ok' => false, 'message' => 'Missing webhook signature']);
}

$expectedSignature = 'sha256='.hash_hmac('sha256', $payload, DEPLOY_WEBHOOK_SECRET);
if (! hash_equals($expectedSignature, $signature)) {
    jsonResponse(403, ['ok' => false, 'message' => 'Invalid webhook signature']);
}

try {
    $data = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException) {
    jsonResponse(400, ['ok' => false, 'message' => 'Invalid JSON payload']);
}

$event = requestHeader('X-GitHub-Event');
if ($event === 'ping') {
    jsonResponse(200, ['ok' => true, 'message' => 'Webhook configured']);
}

if ($event !== 'push') {
    jsonResponse(202, ['ok' => true, 'message' => 'Event ignored']);
}

if (($data['repository']['full_name'] ?? null) !== DEPLOY_REPOSITORY) {
    jsonResponse(403, ['ok' => false, 'message' => 'Repository not allowed']);
}

if (($data['ref'] ?? null) !== 'refs/heads/'.DEPLOY_BRANCH) {
    jsonResponse(202, ['ok' => true, 'message' => 'Branch ignored']);
}

$lockPath = __DIR__.'/storage/framework/deploy.lock';
$lock = fopen($lockPath, 'c');
if ($lock === false) {
    jsonResponse(500, ['ok' => false, 'message' => 'Could not create deployment lock']);
}

if (! flock($lock, LOCK_EX | LOCK_NB)) {
    fclose($lock);
    jsonResponse(409, ['ok' => false, 'message' => 'A deployment is already running']);
}

ignore_user_abort(true);
set_time_limit(0);

$response = json_encode(['ok' => true, 'message' => 'Deployment accepted'], JSON_THROW_ON_ERROR);
http_response_code(202);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('Connection: close');
header('Content-Length: '.strlen($response));
echo $response;

if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
} else {
    while (ob_get_level() > 0) {
        ob_end_flush();
    }
    flush();
}

$delivery = preg_replace('/[^a-zA-Z0-9-]/', '', requestHeader('X-GitHub-Delivery')) ?: 'unknown';
$project = escapeshellarg(__DIR__);
$php = escapeshellarg(PHP_BINARY);

try {
    writeDeployLog("Starting delivery {$delivery} for ".DEPLOY_REPOSITORY.'@'.DEPLOY_BRANCH);

    runDeployCommand("git -C {$project} fetch --prune origin ".escapeshellarg(DEPLOY_BRANCH));
    runDeployCommand("git -C {$project} reset --hard ".escapeshellarg('origin/'.DEPLOY_BRANCH));
    runDeployCommand('cd '.$project.' && '.composerCommand().' install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader');
    runDeployCommand("cd {$project} && {$php} artisan optimize:clear");
    runDeployCommand("cd {$project} && {$php} artisan package:discover --ansi");
    runDeployCommand("cd {$project} && {$php} artisan migrate --force");
    runDeployCommand("cd {$project} && {$php} artisan storage:link");
    runDeployCommand("cd {$project} && {$php} artisan optimize");

    writeDeployLog("Deployment {$delivery} completed successfully");
} catch (Throwable $exception) {
    writeDeployLog("Deployment {$delivery} failed: {$exception->getMessage()}");
} finally {
    flock($lock, LOCK_UN);
    fclose($lock);
}
