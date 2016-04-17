<?php
/**
 * DebugController class.
 */

namespace Collector;

/**
 * Handles validating debug users and storing debug data.
 */
class DebugController
{
    /**
     * Indicates whether debug features should be used.
     *
     * @var bool
     */
    protected $debugMode = false;

    /**
     * The user's username.
     *
     * @var string
     */
    protected $username;

    /**
     * The secret phrase to login with to start debug mode.
     *
     * @var string
     */
    protected $debugName;

    /**
     * Indicates whether all logins should be debug enabled.
     *
     * @var bool
     */
    protected $debugSwitch;

    /**
     * Constructor.
     * Save values needed to check debug and then runs the debugCheck().
     *
     * @param string $username  User's login name
     * @param string $debugName The secret phrase to login with to start debug mode.
     * @param bool   $setting   Value from config that can turn debug on/off for all logins.
     */
    public function __construct($username, $debugName, $setting)
    {
        $this->username = $username;
        $this->debugName = $debugName;
        $this->debugSwitch = $setting;
        $this->debugCheck();
    }
    /**
     * Determines if debug mode should be enabled.
     * If any check returns true then debug mode is turned on and a boolean is
     * saved to $_SESSION['Debug'] which tells if debug is on/off.
     */
    public function debugCheck()
    {
        $this->debugMode = false;
        if ($this->checkName() or $this->checkConfig()) {
            $this->debugMode = true;
        }
        $_SESSION['Debug'] = $this->debugMode;
    }

    /**
     * Checks if the username begins with the debug phrase.
     *
     * @return bool True if login name begins with debug code, else false.
     */
    protected function checkName()
    {
        $name = $this->username;
        $code = $this->debugName;
        $dbLen = strlen($code);
        if (($dbLen > 0)
            && (substr($name, 0, $dbLen) === $code)
        ) {
            return true;
        }

        return false;
    }
    /**
     * Checks if the configuration file has specified debug mode be turned on.
     *
     * @return bool True if debug switch is on, else false.
     */
    protected function checkConfig()
    {
        return ($this->debugSwitch === true) ? true : false;
    }
    /**
     * Checks whether debug is on or off.
     *
     * @return bool True if debug is on, else false.
     */
    public function isOn()
    {
        return ($this->debugMode === true) ? true : false;
    }

    /**
     * Sets the username and re-runs the debugCheck.
     *
     * @param string $name New username to set.
     *
     * @uses DebugController::debugCheck() Used to check the newly set username.
     */
    public function changeName($name)
    {
        $this->username = $name;
        $this->debugCheck();
    }

    /**
     * Sets the debug status in $_SESSION to match this object.
     */
    public function toSession()
    {
        $_SESSION['Debug'] = $this->isOn();
    }

    /**
     * Updates the Pathfinder directory according to whether debug mode is on.
     *
     * @param Pathfinder $pathfinder The Pathfinder for the experiment.
     */
    public function feedPathfinder(Pathfinder $pathfinder)
    {
        if (!$this->isOn()) {
            $pathfinder->setDefault('Data Sub Dir', '');
        } else {
            $pathfinder->setDefault('Data Sub Dir', '/Debug');
        }
    }
}
