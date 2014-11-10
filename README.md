# README #

This repository contains the solution for the first assignment from Pocket Play Lab detailed at: https://docs.google.com/forms/d/1bmzuBRkNowhon5KbvTU8r-rEZKoCtcvPEDmvyfLZ_38/viewform?c=0&w=1

## Overview of Code Organization ##

* **data** contains the sample.log file provided as part of the assignment
* **src** contains the source for the assignment.
* **tests** contains unit tests for code developed for the assignment

## How to run the script? ##


```
php /path/to/invoker.php METHOD URI

```
Here 

* **METHOD** is any string that appears as value to "method" inside the log file
* **URI** is any string that appears as value to "path" inside the log file.

Do note that the pattern matches uses && so if the log file has an entry with:
```
method=POST path=/version_api/files
```
invoking script like:
```
php src/invoker.php GET /version_api/files
```
would give
```
No occurrences of GET /version_api/files found
```

If you run without any arguments you'd get a not so nice exception explaining the usage:
```
php src/invoker.php
PHP Fatal error:  Uncaught exception 'InvalidArgumentException' with message 'Wrong number of arguments.
Usage:
/home/shoaibi/public_html/pocketplaylab/src/invoker.php [HTTP_METHOD: GET|POST|...] [URI: /api/users/{user_id}/count_pending_messages]' in /home/shoaibi/public_html/pocketplaylab/src/invoker.php:15
Stack trace:
#0 {main}
  thrown in /home/shoaibi/public_html/pocketplaylab/src/invoker.php on line 15

Fatal error: Uncaught exception 'InvalidArgumentException' with message ' in /home/shoaibi/public_html/pocketplaylab/src/invoker.php on line 15

InvalidArgumentException: Wrong number of arguments.
Usage:
/home/shoaibi/public_html/pocketplaylab/src/invoker.php [HTTP_METHOD: GET|POST|...] [URI: /api/users/{user_id}/count_pending_messages] in /home/shoaibi/public_html/pocketplaylab/src/invoker.php on line 15

Call Stack:
    0.0001     230568   1. {main}() /home/shoaibi/public_html/pocketplaylab/src/invoker.php:0

```

## Additional Resources ##
None. Code is heavily commented and unit test are provided to further elaborate on system's functionality.

## What about test coverage? ##

* LogParser: 98.90%
* ArrayUtil: 100%

## Who do I talk to? ##
* shoaibi@dotgeek.me
* imshoaibi | skype

## License ##
Code is provided as is with no liability and terms whatsoever. It may turn your toaster to zombie, it may trigger doomsday device. Try at your own risk.