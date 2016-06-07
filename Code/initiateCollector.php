<?php

// configure and register autoloader
require __DIR__ . '/classes/Autoloader.php';
$autoloader = new Collector\Autoloader();
$autoloader->register();
$autoloader->add('Collector', __DIR__.'/classes');
$autoloader->add('adamblake\Parse', __DIR__.'/vendor/adamblake/Parse');
$autoloader->add('phpbrowscap', __DIR__.'/vendor/phpbrowscap');

// start session
session_start();
error_reporting(E_ALL);

// load file locations
if (!isset($_SESSION['_PATH'])) $_SESSION['_PATH'] = new Pathfinder();
$_PATH = $_SESSION['_PATH'];

require_once $_PATH->get('Helpers');

// check if they switched Collectors
// (e.g., went from 'MG/Collector/Code/Done.php' to 'TK/Collector/Code/Done.php')
$currentCollector = $_PATH->get('root', 'url');
if (!isset($_SESSION['Current Collector'])
    ||  $_SESSION['Current Collector'] !== $currentCollector
) {
    $_SESSION = array();
    $_SESSION['Current Collector'] = $currentCollector;
    $_PATH = $_SESSION['_PATH'] = new Pathfinder();

    // if inside Code/ redirect to index
    if ($_PATH->inDir('Code') && !$_PATH->atLocation('Login')) {
        header('Location: '.$_PATH->get('index'));
        exit;
    }
}
unset($currentCollector);

// load settings
if (isset($_SESSION['settings']) 
    && (get_class($_SESSION['settings']) == "Collector\Settings")
) {
    $_SETTINGS = &$_SESSION['settings'];
    $_SETTINGS->upToDate($_PATH);
} else {
    $_SESSION['settings'] = new Collector\Settings(
        $_PATH->get('Common Settings'),
        $_PATH->get('Experiment Settings'),
        $_PATH->get('Password')
    );
    $_SETTINGS = &$_SESSION['settings'];
}

// load Kint in debug mode
if ($_SETTINGS->debug_mode) {
    require __DIR__ . '/vendor/Kint/Kint.class.php';
}

if ($_SETTINGS->password === null) {
    $noPass = true;
    require $_PATH->get("Set Password");
    if ($noPass === true) {
        exit;
    }
}

// if experiment has been loaded (after login) set the variable
if (isset($_SESSION['_EXPT'])
    && (get_class($_SESSION['_EXPT']) == "Collector\Experiment")
) {
    $_EXPT = $_SESSION['_EXPT'];
    $_TRIAL = $_EXPT->getCurrent();
}
// alias SideData as well
if (isset($_SESSION['_SIDE'])) {
    $_SIDE = $_SESSION['_SIDE'];
}
