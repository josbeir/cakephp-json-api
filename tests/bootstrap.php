<?php
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Datasource\ConnectionManager;
use Cake\Log\Log;

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

define('ROOT', dirname(__DIR__));
define('APP_DIR', 'src');

define('TMP', ROOT . DS . 'tmp' . DS);
define('LOGS', TMP . 'logs' . DS);
define('CACHE', TMP . 'cache' . DS);
define('SESSIONS', TMP . 'sessions' . DS);

define('CAKE_CORE_INCLUDE_PATH', ROOT . '/vendor/cakephp/cakephp');
define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);
define('CAKE', CORE_PATH . APP_DIR . DS);
define('TEST_APP', ROOT . DS . 'tests' . DS . 'test_app' . DS);

define('APP', TEST_APP . 'TestApp' . DS);
define('WWW_ROOT', TEST_APP . 'webroot' . DS);
define('CONFIG', ROOT . DS . 'config' . DS);

//@codingStandardsIgnoreStart
@mkdir(TMP);
@mkdir(LOGS);
@mkdir(SESSIONS);
@mkdir(CACHE);
@mkdir(CACHE . 'views');
@mkdir(CACHE . 'models');
//@codingStandardsIgnoreEnd

require_once CORE_PATH . 'config/bootstrap.php';

date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');

Configure::write('debug', true);
Configure::write('App', [
    'namespace' => 'TestApp',
    'fullBaseUrl' => 'http://localhost',
    'encoding' => 'UTF-8',
    'base' => false,
    'baseUrl' => false,
    'dir' => APP_DIR,
    'webroot' => 'webroot',
    'wwwRoot' => WWW_ROOT
]);

Cache::config([
    '_cake_core_' => [
        'engine' => 'File',
        'prefix' => 'cake_core_',
        'serialize' => true
    ],
    '_cake_model_' => [
        'engine' => 'File',
        'prefix' => 'cake_model_',
        'serialize' => true
    ]
]);

//needed?
Plugin::load('JsonApi', ['path' => ROOT . DS, 'autoload' => true]);

// Ensure default test connection is defined
if (!getenv('db_dsn')) {
    putenv('db_dsn=sqlite:///:memory:');
}

ConnectionManager::config('test', ['url' => getenv('db_dsn')]);

Configure::write('Session', [
    'defaults' => 'php'
]);

Log::config([
    'debug' => [
        'engine' => 'Cake\Log\Engine\FileLog',
        'levels' => ['notice', 'info', 'debug'],
        'file' => 'debug',
    ],
    'error' => [
        'engine' => 'Cake\Log\Engine\FileLog',
        'levels' => ['warning', 'error', 'critical', 'alert', 'emergency'],
        'file' => 'error',
    ]
]);
