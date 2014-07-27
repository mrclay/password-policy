<?php

namespace PasswordPolicy;

class WeightedRule {

    /**
     * @var Rule
     */
    protected $rule;

    /**
     * @var int|float
     */
    protected $weight;

    /**
     * @param Rule $rule
     * @param int|float $weight
     */
    public function __construct(Rule $rule, $weight = 1) {
        $this->rule = $rule;
        $this->weight = $weight;
    }

    /**
     * @param int|float $weight
     */
    public function setWeight($weight) {
        $this->weight = $weight;
    }

    /**
     * @return Rule
     */
    public function getRule() {
        return $this->rule;
    }

    /**
     * @return float|int
     */
    public function getWeight() {
        return $this->weight;
    }

    /**
     * @return string
     */
    public function toJavaScript() {
        return "{
            rule: " . $this->rule->toJavaScript() . ",
            weight: " . json_encode($this->weight) . "
        }";
    }

}