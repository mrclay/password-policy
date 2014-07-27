<?php

namespace PasswordPolicy\Rules;

use PasswordPolicy\AcceptsConstraints;
use PasswordPolicy\Constraint;
use PasswordPolicy\Rule;

abstract class Base implements Rule, AcceptsConstraints {

    /**
     * @var Constraint
     */
    protected $constraint = null;

    public function getDescription() {
        if ($this->constraint) {
            return $this->constraint->getMessage();
        }
        return '';
    }

    public function setConstraint(Constraint $constraint) {
        $this->constraint = $constraint;
    }

    public function toJavaScript() {
        return '{
            description: "Not Implemented",
            score: function(p) { return 0; }
        }';
    }

    protected function testConstraint($num, $password) {
        if (empty($this->constraint)) {
            return (bool) $num;
        }
        return $this->constraint->check($num, $password);
    }

}