<?php

declare(strict_types=1);

// Compatibility entry point for hosts whose document root is the project root.
// The preferred document root is public/, where the canonical endpoint lives.
require __DIR__.'/public/deploy.php';
