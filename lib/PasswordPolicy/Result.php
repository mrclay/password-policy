<?php

namespace PasswordPolicy;

class Result {

    protected $score;
    protected $requiredScore;
    protected $messages;

    /**
     * @param int|float $score
     * @param int|float $requiredScore
     * @param array $messages
     */
    public function __construct($score, $requiredScore, array $messages) {
        $this->score = $score;
        $this->requiredScore = $requiredScore;
        $this->messages = $messages;
    }

    /**
     * @return bool
     */
    public function passed() {
        return ($this->score >= $this->requiredScore);
    }

    /**
     * @return array
     */
    public function getMessages() {
        return $this->messages;
    }

    /**
     * @return int|float
     */
    public function getScore() {
        return $this->score;
    }

    /**
     * @return int|float
     */
    public function getRequiredScore() {
        return $this->requiredScore;
    }

}