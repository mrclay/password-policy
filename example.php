<?php

use PasswordPolicy\Policy;

require 'vendor/autoload.php';

$policy = (new Policy())
    // effectively required
    ->length(6)->setWeight(2)
    ->containsUppercase()->setWeight(2)
    ->containsLowercase()->setWeight(2)
    ->containsDigit()->setWeight(2)

    // these have weight 1 and can substitute
    ->containsSymbol()->constrain()->atLeast(2)
    ->length(10)

    ->allowMissedPoints(1);

assert(false == $policy->test("Ax4k45")->passed()); // no symbols and not 10 length
assert(true == $policy->test("Ax#k$5")->passed()); // 2 symbols
assert(true == $policy->test("Ax4k45huig")->passed()); // 10 length
assert(false == $policy->test("gx4k45huig")->passed()); // no uppercase


// Policies as rules make this a bit easier:
$requirements = (new Policy())
    ->length(6)
    ->containsUppercase()
    ->containsLowercase()
    ->containsDigit();
$substitutions = (new Policy())
    ->containsSymbol()->constrain()->atLeast(2)
    ->length(10)
    ->allowMissedPoints(1);
$policy = (new Policy())
    ->addPolicyAsRule($requirements)
    ->addPolicyAsRule($substitutions);

?>
(See console)
<script>
!function () {
    function assert(password, expected) {
        var passed = policy(password).passed;
        console.log(password, passed, (expected == passed ? 'PASS' : 'FAIL'));
    }
    var policy = <?php echo $policy->toJavaScript() ?>;

    assert("Ax4k45", false); // no symbols and not 10 length
    assert("Ax#k$5", true); // 2 symbols
    assert("Ax4k45huig", true); // 10 length
    assert("gx4k45huig", false); // no uppercase

    console.log(policy("Ax4k45hu!g"));
}();
</script>
