<?php

namespace PasswordPolicy\Rules;

class Regex extends Base {

    protected $description = '';
    protected $regex = '';

    public function __construct($regex, $textDescription) {
        $this->description = $textDescription;
        $this->regex = $regex;
    }

    public function getDescription() {
        $constraint = parent::getDescription();
        return sprintf($this->description, $constraint);
    }

    public function score($password) {
        $matches = array();
        $num = preg_match_all($this->regex, $password, $matches);
        return $this->testConstraint($num, $password) ? 1 : 0;
    }

    public function toJavaScript() {
        $ret = "{
            description: " . json_encode($this->getDescription()) . ",
            score: function(p) {
                var r = {$this->regex}g;";
        if ($this->constraint) {
            $ret .= "
                var c = " . $this->constraint->toJavaScript() . ";
                var l = p.match(r);
                l = l ? l.length : 0;
                return c(l) ? 1 : 0;";
        } else {
            $ret .= "
                return r.test(p) ? 1 : 0;";
        }
        $ret .= "
            }
        }";
        return $ret;
    }

    public static function toCharClass($desc) {
        switch ($desc) {
            case 'letter':
                return array('a-zA-Z', 'letter');
            case 'lowercase':
                return array('a-z', 'lowercase');
            case 'uppercase':
                return array('A-Z', 'uppercase');
            case 'alnum':
                return array('a-zA-Z0-9', 'alpha numeric');
            case 'digit':
                return array('0-9', 'digit');
            case 'symbol':
                return array('^a-zA-Z0-9', 'symbol');
            case 'null':
                return array('\0', 'null');
        }
        return array($desc, '');
    }

}