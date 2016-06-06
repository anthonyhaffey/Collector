<?php
/**
 * Functions to aid logging into Collector/Tools.
 */

/**
 * Checks that the scrambled+hashed response matches what the server
 * calculates when it does the same scramble+hash with the stored password.
 *
 * This is done so the user doesn't send us the password. Instead we 
 * hash(salt+password) server side and challenge the user to get the 
 * same resultant hash (i.e., they entered the correct password)
 *
 * By doing this procedure anyone sniffing the transmission is never shown
 * the password and the response they see transmitted cannot be used in the
 * future to login becasue each login is dependent on a unique salt the 
 * sever generates for every login attempt
 * 
 * @param string $response  The user response = hash(user input + salt).
 * @param string $password  The stored password in Password.php.
 * @param string $salt      The salt for the password.
 * @param string $hash_algo The hash algorithm to use.
 * 
 * @return bool True if the password is correct.
 */
function checkPass($response, $password, $salt, $hash_algo)
{
    $correct = hash($hash_algo, $salt.$password);
    if ($correct === $response) {
        return true;
    }
    
    return false;
}

/**
 * Determines the appropriate login state.
 * 
 * @param string $Password The currently set password. Will bounce if it has not
 *                         been altered since downloading Collector.
 * 
 * @return string The current login state.
 */
function loginState($Password)
{
    // after this many seconds you must login again
    $LoginExpiration = 60 * 60 * 2;

    // No password set
    if ($Password === null) {
        return 'noPass';
    }

    // not logged in
    if (!isset($_SESSION['admin']['status'])
    ) {
        
        return 'newChallenger';
    }

    // wrong password
    if ($_SESSION['admin']['status'] === 'failed') {
        
        return 'wrongPass';
    }

    // logged in
    if ($_SESSION['admin']['status'] === 'loggedIn') {
        $age = time() - $_SESSION['admin']['birth'];
        
        // check expiration
        if ($age > $LoginExpiration) {
            
            return 'expired';
        } else {
            return 'loggedIn';
        }
    } else {
        
        return 'newChallenger';
    }

    // how'd you do that?
    return 'unknownState';
}

/**
 * Shows the appropriate response page.
 * 
 * @param string $state The current login state.
 */
function loginPrompt($state)
{
    $salt = $_SESSION['admin']['challenge'];

    $expired = '<h3>Your session has expired and you must login again to continue</h3>';
    $wrong = '<p class="wrong">Thank you Mario! But our princess is in another castle... I mean, wrong password</p>';
    $noPass =
        '<div class="error">'.
          '<h2>You are not allowed to use <code>Tools</code> until you have set a password</h2>'.
          '<p> The password can be set within <code>Experiments/Common/Password.php</code></p>'.
        '</div>';
    $unknown =
        '<p>We have no idea how you got here.'.
           'Post this as an issue on the <a href="http://www.github.com/gikeymarcia/collector">project Github Page</a>.'.
        '</p>';
    $loginPrompt =
        '<p>Login to access tools</p>'.
        '<input type="password" id="pass" class= "collectorInput" autofocus></input>'.
        '<input id="fauxSubmit" type="submit" value="Submit" class="collectorButton"></input>'.
        '<form id="hashSubmit" action="login.php" method="post" class="hidden">'.
          "<span id='nonce'>$salt</span>".
          '<input id="realInput" name="response" type="text"></input>'.
        '</form>';

    echo '<div id="login">';
    switch ($state) {
        case 'noPass':
            echo $noPass;
            break;
        case 'newChallenger':
            echo $loginPrompt;
            break;
        case 'wrongPass':
            echo $wrong.$loginPrompt;
            break;
        case 'expired':
            echo $expired.$loginPrompt;
            break;
        default:
            echo $unknown;
            break;
    }
    echo '</div>';
    echo "<div id='salt'><b>salt=</b>$salt</div>";
}

/**
 * Makes a long random string that will be used to salt the password before 
 * hashing it.
 * @param int $bits The number of bits to use.
 * @return string
 */
function makeNonce($bits = 512)
{
    // adapted from http://stackoverflow.com/a/4145848 User:ircmaxell
    $bytes = ceil($bits / 8);
    $seed = '';
    for ($i = 0; $i < $bytes; ++$i) {
        $seed .= chr(mt_rand(0, 255));
    }
    $nonce = hash('sha512', $seed, false);

    return $nonce;
}

/**
 * Checks $_SESSION state to see if an admin is logged in and exits if not so.
 */
function adminOnly()
{
    if (!isset($_SESSION)) {
        session_start();
    }

    // kill for anyone not properly logged in
    if (!(isset($_SESSION['admin']['status']))
        || ($_SESSION['admin']['status'] !== 'loggedIn')
    ) {
        exit('You must be logged in as an admin to perform this action.');
    }
}