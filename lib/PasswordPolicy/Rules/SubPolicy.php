<?php

namespace PasswordPolicy\Rules;

use PasswordPolicy\Policy;
use PasswordPolicy\Rule;

class SubPolicy implements Rule {

    /**
     * @var Policy
     */
    protected $policy;

    /**
     * @param Policy $policy
     */
    public function __construct(Policy $policy) {
        $this->policy = $policy;
    }

    /**
     * @param string $password
     * @return int 1 or 0
     */
    public function score($password) {
        return $this->policy->test($password)->passed() ? 1 : 0;
    }

    /**
     * @return string
     */
    public function getDescription() {
        $requiredScore = $this->policy->getRequiredScore();
        if ($requiredScore <= 0) {
            return "The password will always be accepted.";
        }

        $messages = array();
        foreach ($this->policy->getWeightedRules() as $weightedRule) {
            $desc = $weightedRule->getRule()->getDescription();
            $weight = $weightedRule->getWeight();
            $messages[] = "$desc (for $weight points)";
        }
        return "The password must receive $requiredScore points via the rules ["
            . implode(', ', $messages) . "]";
    }

    public function toJavaScript() {
        return '{
            description: ' . json_encode($this->getDescription()) . ',
            score: function(p) {
                var policy = ' . $this->policy->toJavaScript() . ';
                return policy(p).passed ? 1 : 0;
            }
        }';
    }
}