<?php
// define the autoloader
require_once 'lib/adianti/core/AdiantiCoreLoader.php';
spl_autoload_register(array('Adianti\Core\AdiantiCoreLoader', 'autoload'));
Adianti\Core\AdiantiCoreLoader::loadClassMap();

$loader = require 'vendor/autoload.php';
$loader->register();

// read configurations
$ini  = parse_ini_file('application.ini');
date_default_timezone_set($ini['timezone']);

// define constants
define('APPLICATION_NAME', $ini['application']);
define('OS', strtoupper(substr(PHP_OS, 0, 3)));
define('PATH', dirname(__FILE__));
define('LANG', $ini['language']);

new TSession;
TSession::setValue('language', LANG);
AdiantiCoreTranslator::setLanguage(LANG);
ApplicationTranslator::setLanguage(LANG);