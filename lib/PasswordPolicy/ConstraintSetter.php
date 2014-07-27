<?php

namespace PasswordPolicy;

use PasswordPolicy\Constraints\Range;

class ConstraintSetter {

    /**
     * @var AcceptsConstraints
     */
    protected $rule;

    /**
     * @var Policy
     */
    protected $policy;

    public function __construct(AcceptsConstraints $rule, Policy $policy) {
        $this->rule = $rule;
        $this->policy = $policy;
    }

    protected function applyConstraint(Constraint $constraint) {
        $this->rule->setConstraint($constraint);
        return $this->policy;
    }

    /**
     * Constrain the rule to require at least $n matches
     *
     * @param int $min
     * @return Policy
     */
    public function atLeast($min) {
        return $this->applyConstraint(new Range($min, PHP_INT_MAX));
    }

    /**
     * Constrain the rule to allow at most $n matches
     *
     * @param int $max
     * @return Policy
     */
    public function atMost($max) {
        return $this->applyConstraint(new Range(0, $max));
    }

    /**
     * Constrain the rule to require between $min and $max matches
     *
     * @param int $min
     * @param int $max
     * @return Policy
     */
    public function between($min, $max) {
        return $this->applyConstraint(new Range($min, $max));
    }

    /**
     * Constrain the rule to require no matches
     *
     * @return Policy
     */
    public function never() {
        return $this->applyConstraint(new Range(0, 0));
    }

}