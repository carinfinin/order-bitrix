<?
define ('VG_ACCESS', true);
header('Content-Type:text/html; Charset=utf-8');
session_start();
require_once 'config.php';
require_once 'base/settings/internal_settings.php';

echo 'gggggggg';

use  base\controllers\RouteController;
use  base\exceptions\RouteException;


try {
    RouteController::getInstance();
}
catch (RouteException $e) {
    exit($e->getMessage());
}







































