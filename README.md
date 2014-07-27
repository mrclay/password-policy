PasswordPolicy
==============

A tool for checking and creating password policies in PHP and JS.

## Installation

Use composer to setup an autoloader

    php composer.phar install

Require the composer autoload file:

    require_once 'vendor/autoload.php';

## Usage

To use, first instantiate the core policy object:

    $policy = new \PasswordPolicy\Policy;

Then, add rules with optional constraints:

    $policy
        ->length(8)
        ->containsUppercase()
        ->containsSymbol()->constrain()->atLeast(2);

By default all rules are required to pass, but you may set weights and allow some to fail.

    // password can be missing a symbol if it's at least 10 chars long
    $policy
        ->length(8)->setWeight(100)
        ->containsUppercase()->setWeight(100)

        ->length(10) // weight 1
        ->containsSymbol() // weight 1
        ->allowMissedPoints(1);

For complex policies, you can even include sub-policies as rules.

### Supported Rule Helper Methods

 * `contains($class, $description = '')`: Checks to see if a password contains a class of chars
 
    Supported Short-Cut classes:

    * `letter` - `a-zA-Z`
    * `lowercase` - `a-z`
    * `uppercase` - `A-Z`
    * `digit` - `0-9`
    * `symbol` - `^a-zA-Z0-9` (in other words, non-alpha-numeric)
    * `null` - `\0`
    * `alnum` - `a-zA-Z0-9`

    The second param is a constraint (optional)

 * `length($min, $max)`: Checks the length of the password matches a constraint

 * `endsWith($class, $description = '')`: Checks to see if the password ends with a character class.

 * `startsWith($class, $description = '')`: Checks to see if the password starts with a character class.

 * `notMatch($regex, $description)`: Checks if the password does not match a regex.

 * `match($regex, $description)`: Checks if the password matches the regex.

### Supported Constraints

After adding a rule

The policy also has short-cut helpers for creating constraints:

 * `atLeast($n)`: At least the param matches

    Equivilant to `between($n, PHP_INT_MAX)`

 * `atMost($n)`: At most the param matches

    Equivilant to `between(0, $n)`

 * `between($min, $max)`: Between $min and $max number of matches

 * `never()`: No matches
     
    Equivilant to `between(0, 0)`

## Testing the Policy (PHP)

Once you setup the policy, you can then test it in PHP using the `test($password)` method.

    $result = $policy->test($password);

A Result object is returned with methods:

 * `$result->passed()`: Returns boolean: is the password valid?

 * `$result->getScore()`: Returns the total score

 * `$result->getRequiredScore()`: Returns the score required to pass

 * `$result->getMessages()`: Returns an array of messages

Each message is a stdClass object with these members:

 * `$message->score` - The score given by the rule

 * `$message->weightedScore` - The score adjusted by weight, which adds to the total

 * `$message->description` - A textual description of the rule

### Scoring

Each rule is scored and then multiplied by a weight to arrive at the total score. The policy has a "required score" to determine if the given password is considering passing or failing.

By default, all rules must score 1 to pass, but you can fine-tune the policy by using `$policy->setRequiredScore($score)` and by adjusting the weight of your rules when adding them to the policy.

## Testing the Policy (JavaScript)

Once you've built the policy, you can call `toJavaScript()` to generate a JS anonymous function for injecting into JS code.

    $js = $policy->toJavaScript();
    echo "var policy = $js;";

Then, the policy object in JS is basically a wrapper for `$policy->test($password)`, and behaves the same (same return values).

    var result = policy(password);
    if (!result.result) {
        /* Process Messages To Display Failure To User */
    }

One note for the JavaScript, any regular expressions that you write need to be deliminated by `/` and be valid JS regexes (no PREG specific functionality is allowed).

