<?php

namespace PasswordPolicy;

interface Rule {

    public function getDescription();
    public function score($password);
    public function toJavaScript();

}