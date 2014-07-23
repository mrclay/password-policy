<?php

namespace PasswordPolicy\Rules;

class Blacklist {

    protected $path;
    protected $description;
    protected $isCaseSensitive;

    /**
     * @param string $path            File path of blacklist, containing a newline-separated list of passwords
     * @param bool   $isCaseSensitive Should the blacklist matching be case sensitive?
     * @param string $description
     */
    public function __construct($path, $isCaseSensitive = true, $description = null) {
        if (null === $description) {
            $description = 'Expecting a password not in the list "' . basename($path) . '"';
        }
        $this->path = $path;
        $this->isCaseSensitive = (bool) $isCaseSensitive;
        $this->description = $description;
    }

    public function getMessage() {
        return $this->description;
    }

    public function setConstraint(\PasswordPolicy\Constraint $constraint) {
        // unused
    }

    public function test($password) {
        $options = "-m 1"; // stop at first match
        if (!$this->isCaseSensitive) {
            $options .= " -i";
        }
        $command = "fgrep $options " . escapeshellarg($password) . " " . escapeshellarg($this->path);
        $result = shell_exec($command);
        return (trim($result) === '');
    }

    public function toJavaScript() {
        return '{
            message: "Blacklists not implemented client-side",
            check: function(p) { return true; }
        }';
    }

}