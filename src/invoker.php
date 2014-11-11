<?php
try
{
    // why the "&&" instead of subsequent calls on separate lines?
    // Right now I throw exception on every error. In future I might decide to handle
    // exceptions inside methods, say I don't throw InvalidArgumentException in isArgumentCountValid but
    // instead print an error. In that case I could change the return value to false still keeping the code sane.
    isArgumentCountValid() && bootstrapParser();
    // 0 - shell code for "we're all alright!"
    exit(0);
}
catch (Exception $e)
{
    echo 'An Error Occurred.' . PHP_EOL . 'Type: ' . get_class($e) . PHP_EOL . 'Message: ' . PHP_EOL . $e->getMessage() . PHP_EOL;
    // 1 - something went "big bada boom"
    exit(1);
}


function isArgumentCountValid()
{
    global $argv;
    if (count($argv) != 3)
    {
        $message    = <<<USAGEERROR
Wrong number of arguments.
Usage:
%s [HTTP_METHOD: GET|POST|...] [URI: /api/users/{user_id}/count_pending_messages]
USAGEERROR;
        throw new InvalidArgumentException(sprintf($message, basename(__FILE__)));
    }
    return true;
}

function bootstrapParser()
{
    global $argv;
    // Not using autoloader. Not needed for such a small piece of code.
    // why require_once is here and not on top of the file? because I would only need it here. Lets keep the memory
    // footprint small and include only the bare minimum code we have to.
    require_once('LogParser.php');

    $parser = new LogParser(__DIR__ . '/../data/sample.log');
    $parser->parse($argv[1], $argv[2]);
    return true;
}