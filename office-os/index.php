<?php

declare(strict_types=1);

header('Content-Type: application/json');

echo json_encode([
    'service' => 'Office OS API',
    'version' => 'v1',
    'message' => 'Use /api/v1.php?action=... endpoints',
]);
