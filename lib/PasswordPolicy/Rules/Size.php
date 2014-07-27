<?php

namespace PasswordPolicy\Rules;

class Size extends Base {

    public function getDescription() {
        $constraint = parent::getDescription();
        return "Expecting a password length of $constraint characters";
    }

    public function score($password) {
        // TODO mb_strlen?
        return $this->testConstraint(strlen($password), $password) ? 1 : 0;
    }

    public function toJavaScript() {
        $ret = "{
            description: " . json_encode($this->getDescription()) . ",
            score: function(p) {
                return (" . $this->constraint->toJavaScript() . ")(p.length) ? 1 : 0;
            }
        }";
        return $ret;
    }

}