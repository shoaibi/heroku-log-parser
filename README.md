# README #

## What is this about? ##
Check requirements.md

## Overview of Code Organization ##

* **data** contains the sample.log file provided as part of the assignment
* **src** contains the source for the assignment.
* **tests** contains unit tests for code developed for the assignment

## How to run the script? ##

```
$ php /path/to/invoker.php METHOD URI

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
$ php src/invoker.php GET /version_api/files
```
would give
```
No occurrences of GET /version_api/files found
```

If you run without any arguments you'd get an error explaining the usage:
```
$ php src/invoker.php POST
  An Error Occurred.
  Type: InvalidArgumentException
  Message:
  Wrong number of arguments.
  Usage:
  invoker.php [HTTP_METHOD: GET|POST|...] [URI: /api/users/{user_id}/count_pending_messages]
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