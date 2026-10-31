<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/includes/db_connect.php';

use App\Domain\Destination\DestinationService;
use App\Support\Env;

$destService = new DestinationService($conn);
$appUrl = Env::get('APP_URL', 'http://localhost/GQ-Turismo');

header('Content-Type: application/xml; charset=utf-8');
echo $destService->generarSitemapXml($appUrl);
