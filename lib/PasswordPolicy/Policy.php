<?php

namespace PasswordPolicy;

use PasswordPolicy\Constraints\Range;
use PasswordPolicy\Exceptions\NoConstraintsException;
use PasswordPolicy\Exceptions\NoRuleSetException;
use PasswordPolicy\Rules\Regex;

/**
 * @method Policy containsLetter()
 * @method Policy containsLowercase()
 * @method Policy containsUppercase()
 * @method Policy containsAlnum()
 * @method Policy containsDigit()
 * @method Policy containsSymbol()
 * @method Policy containsNull()
 */
class Policy {

    /**
     * @var WeightedRule[]
     */
    protected $weightedRules = array();

    /**
     * @return \PasswordPolicy\WeightedRule[]
     */
    public function getWeightedRules()
    {
        return $this->weightedRules;
    }

    /**
     * @var int|float|null if null, this is the total number of rules
     */
    protected $requiredScore;

    /**
     * Get the required score. If not explicitly set, this will be the sum of all rull weights,
     * effectively meaning all rules will need to score 1 to pass.
     *
     * @return int|float
     */
    public function getRequiredScore() {
        if (null === $this->requiredScore) {
            $total = 0;
            foreach ($this->weightedRules as $weightedRule) {
                $total += $weightedRule->getWeight();
            }
            return $total;
        }
        return $this->requiredScore;
    }

    /**
     * Set the required score.
     *
     * @param int|float|null $score Set to null to return to the default required score
     * @return Policy
     */
    public function setRequiredScore($score = null) {
        if (null === $score) {
            $this->requiredScore = null;
            $score = $this->getRequiredScore();
        }
        $this->requiredScore = $score;
        return $this;
    }

    /**
     * Set the required score so the password can pass will $points fewer points than by default
     *
     * @param int $points
     * @return Policy
     */
    public function allowMissedPoints($points) {
        $this->requiredScore = null;
        $this->setRequiredScore($this->getRequiredScore() - $points);
        return $this;
    }

    /**
     * Require some characters in the password
     *
     * @param string $chars
     * @param string $description
     * @return Policy
     */
    public function contains(
        $chars,
        $description = ''
    ) {
        list($chars, $desc) = Rules\Regex::toCharClass($chars);
        if ($desc && !$description) {
            $description = $desc;
        }
        $rule = new Rules\CharacterRange($chars, $description);
        $rule->setConstraint(new Range(1, PHP_INT_MAX));
        return $this->addRule($rule);
    }

    /**
     * Set length requirements of the password
     *
     * @param int $min
     * @param int $max
     * @return Policy
     */
    public function length($min, $max = PHP_INT_MAX) {
        $rule = new Rules\Size();
        $rule->setConstraint(new Range($min, $max));
        return $this->addRule($rule);
    }

    /**
     * Set characters that the password must end with
     *
     * @param string $chars
     * @param string $description
     * @return Policy
     */
    public function endsWith($chars, $description = '') {
        list($chars, $desc) = Rules\Regex::toCharClass($chars);
        if ($desc && !$description) {
            $description = $desc;
        }
        $description = 'Ends with ' . $description;
        $rule = new Rules\Regex('/[' . $chars . ']$/', $description);
        $rule->setConstraint(new Range(1, PHP_INT_MAX));
        return $this->addRule($rule);
    }

    /**
     * Set characters that the password must begin with
     *
     * @param string $chars
     * @param string $description
     * @return Policy
     */
    public function startsWith($chars, $description = '') {
        list($chars, $desc) = Rules\Regex::toCharClass($chars);
        if ($desc && !$description) {
            $description = $desc;
        }
        $description = 'Starts with ' . $description;
        $rule = new Rules\Regex('/^[' . $chars . ']/', $description);
        $rule->setConstraint(new Range(1, PHP_INT_MAX));
        return $this->addRule($rule);
    }

    /**
     * Set a pattern that the password must not match
     *
     * @param string $regex
     * @param string $description
     * @return Policy
     */
    public function notMatch($regex, $description) {
        $rule = new Rules\Regex($regex, $description);
        $rule->setConstraint(new Range(0, 0));
        return $this->addRule($rule);
    }

    /**
     * Set a pattern that the password must match
     *
     * @param string $regex
     * @param string $description
     * @return Policy
     */
    public function match($regex, $description) {
        $rule = new Rules\Regex($regex, $description);
        $rule->setConstraint(new Range(1, PHP_INT_MAX));
        return $this->addRule($rule);
    }

    /**
     * Add a password policy to use as a rule.
     *
     * @param Policy $policy
     * @return Policy The policy to which the sub-policy has been added.
     */
    public function addPolicyAsRule(Policy $policy) {
        return $this->addRule(new Rules\SubPolicy($policy));
    }

    /**
     * Add a rule to the policy.
     *
     * @param Rule $rule
     * @return Policy
     */
    public function addRule(Rule $rule) {
        $this->weightedRules[] = new WeightedRule($rule);
        return $this;
    }

    /**
     * Set the weight of the last rule added
     *
     * @param int|float $weight
     * @return Policy
     * @throws NoRuleSetException
     */
    public function setWeight($weight) {
        $last = $this->getLastWeightedRule();
        if (!$last) {
            throw new NoRuleSetException("No rule has been added to the policy");
        }
        $last->setWeight($weight);
        return $this;
    }

    /**
     * @return ConstraintSetter
     * @throws NoRuleSetException|NoConstraintsException
     */
    public function constrain() {
        $last = $this->getLastWeightedRule();
        if (!$last) {
            throw new NoRuleSetException("No rule has been added to the policy");
        }
        $rule = $last->getRule();
        if (!$rule instanceof AcceptsConstraints) {
            $class = get_class($rule);
            throw new NoConstraintsException("The last rule $class does not accept constraints");
        }
        return new ConstraintSetter($rule, $this);
    }

    public function __call($name, $args) {
        if (preg_match("~^contains([A-Z][a-z]+)$~", $name, $m)) {
            $charClassName = strtolower($m[1]);
            $charClass = Regex::toCharClass($charClassName);
            if ($charClass[1]) {
                return $this->contains($charClassName);
            }
        }
        throw new \BadMethodCallException("There is no method $name");
    }

    /**
     * @return string
     */
    public function toJavaScript() {
        $weightedRules = array();
        foreach ($this->weightedRules as $weightedRule) {
            $weightedRules[] = "\n" . $weightedRule->toJavaScript();
        }
        $stub = "(function(p) {
            var weightedRules = [" . implode(', ', $weightedRules) . "
                ],
                requiredScore = {$this->getRequiredScore()},
                ruleLen = weightedRules.length,
                messages = [],
                totalScore = 0,
                score,
                weightedScore;
            for (var i = 0; i < ruleLen; i++) {
                score = weightedRules[i].rule.score(p);
                weightedScore = score * weightedRules[i].weight;
                totalScore += weightedScore;
                messages.push({
                    score: score,
                    weightedScore: weightedScore,
                    description: weightedRules[i].rule.description
                });
            }
            return {
                passed: (totalScore >= requiredScore),
                requiredScore: requiredScore,
                score: totalScore,
                messages: messages
            };
        })";
        return preg_replace('/\s\s+/', ' ', $stub);
    }

    /**
     * @param string $password
     * @return Result
     */
    public function test($password) {
        $messages = array();
        $totalScore = 0;
        $requiredScore = $this->getRequiredScore();

        foreach ($this->weightedRules as $weightedRule) {
            $score = $weightedRule->getRule()->score($password);
            $weightedScore = $score * $weightedRule->getWeight();
            $totalScore += $weightedScore;
            $messages[] = (object) array(
                'score' => $score,
                'weightedScore' => $weightedScore,
                'description' => $weightedRule->getRule()->getDescription(),
            );
        }

        return new Result($totalScore, $requiredScore, $messages);
    }

    /**
     * @return WeightedRule|null
     */
    protected function getLastWeightedRule() {
        if (!$this->weightedRules) {
            return null;
        }
        return end($this->weightedRules);
    }
}
