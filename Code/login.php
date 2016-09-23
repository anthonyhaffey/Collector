<?php
/*  Collector
    A program for running experiments on the web
 */

require __DIR__ . '/initiateCollector.php';

$input_username  = filter_input(INPUT_GET, 'Username');
$input_exp       = filter_input(INPUT_GET, 'Experiment');
$input_condition = filter_input(INPUT_GET, 'Condition');

// wipe out session and replace it with new login info
$_SESSION = Login::run($input_username, $_SETTINGS);

// restore FileSystem alias
$_FILES = $_SESSION['_FILES'];

$_FILES->set_default('Current Experiment', $input_exp);

// if data doesn't exist for this user, create it
if ($_FILES->read('User Data') === null) {
    $user_data = create_experiment($_FILES, $input_condition);
    save_user_data($user_data, $_FILES);
}

header('Location: ' . $_FILES->get_path('Experiment Page'));
exit;
