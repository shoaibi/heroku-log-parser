<?php
// checkout the script arguments before I do anything else.
if (count($argv) != 3)
{
    $message    = <<<USAGEERROR
Wrong number of arguments.
Usage:
%s [HTTP_METHOD: GET|POST|...] [URI: /api/users/{user_id}/count_pending_messages]
USAGEERROR;
    throw new InvalidArgumentException(sprintf($message, __FILE__));
}

// Not using autoloader. No need for such a small piece of code.
require_once('LogParser.php');

$parser = new LogParser(__DIR__ . '/../data/sample.log');
$parser->parse($argv[1], $argv[2]);

