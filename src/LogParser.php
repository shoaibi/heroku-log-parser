<?php

require_once('ArrayUtil.php');

// file might have silly line ending character \r (Mac), need to account for that.
ini_set("auto_detect_line_endings", true);

/**
 * Class LogParser
 * Parse Heroku Log files for summary data
 */
class LogParser
{
    const DYNO_KEY              = 'dyno';

    const CONNECT_TIME_KEY      = 'connect';

    const SERVICE_TIME_KEY      = 'service';

    const TIME_UNIT             = 'ms';

    const KEY_VALUE_DELIMITER   = '=';

    protected $filePath;

    /**
     * @param $filePath
     */
    public function __construct($filePath)
	{
        $this->validateLogFile($filePath);
        $this->filePath = $filePath;
	}

    /**
     * Parse log file and output summary information
     * @param $method
     * @param $requestPath
     */
	public function parse($method, $requestPath)
	{
        if (!isset($method, $requestPath))
        {
            throw new BadFunctionCallException("method and requestPath both should be provided");
        }
        $summary    = $this->parseFileAndGenerateSummaryData($method, $requestPath);
        $this->formatOutput($method, $requestPath, $summary);
	}

    /**
     * Parse log file and generate summary data
     * @param $method
     * @param $requestPath
     * @return array
     */
    protected function parseFileAndGenerateSummaryData($method, $requestPath)
    {
        // using "@" is really bad practice but I know what I am doing. I am handling the failures manually
        // by using intelligent method(exceptions) over php's legacy trigger_error
        $handle = @fopen($this->filePath, 'r');
        if ($handle === false)
        {
            // chance of this happened are very rare due to validateFileExistsAndIsReadable()
            // still here to handle the exceptionally exceptional scenario
            // How rare?
            // @see check LogParserTest.testParseWithFileBecomingUnreadableAfterConstruct()
            throw new RuntimeException("Unable to open {$this->filePath} for reading");
        }
        // doing line by line parsing instead to save memory. In the worst case I would only consume
        // a little over than memory required for 1 line in log file

        $dynos                  = array();
        $totalOccurrences       = 0;
        $responseTimes          = array();
        // replace placeholder in requestPath with relevant regex
        $requestPathExpression  = $this->qualifyRequestPathForUserIdPattern($requestPath);
        // can't use steam_get_line instead of fgets. Even though log file may be quite large and even though
        // stream_get_line is bit more consistent with performance I don't know the line endings for sure.
        // and even if I do, hardcoding them wouldn't be very wise.
        while (($line = fgets($handle)) !== false)
        {
            $this->parseLogLine($method, $requestPathExpression, $line, $totalOccurrences, $responseTimes, $dynos);
        }
        if (!feof($handle))
        {
            throw new RuntimeException("Encountered unexpected error during file parsing");
        }
        fclose($handle);
        return compact('totalOccurrences', 'dynos', 'responseTimes');
    }

    /**
     * Parse a single log line for summary data and populate summary data into arguments
     * @param $method
     * @param $requestPathExpression
     * @param $line
     * @param $totalOccurrences
     * @param array $responseTimes
     * @param array $dynos
     */
    protected function parseLogLine($method, $requestPathExpression, $line, & $totalOccurrences, array & $responseTimes, array & $dynos)
    {
        if ($this->doesLineMatchMethodAndRequestPath($line, $method, $requestPathExpression))
        {
            $totalOccurrences++;
            $responseTimes[]    = $this->getResponseTimeValueFromLogEntry($line);
            $dynos[]            = $this->getValueFromLogEntryForKey($line, static::DYNO_KEY);
        }
    }

    /**
     * Devise if the line contains both, method and the requestPath provided.
     * @param $line
     * @param $method
     * @param $requestPathExpression
     * @return bool
     */
    protected function doesLineMatchMethodAndRequestPath($line, $method, $requestPathExpression)
    {
        // assuming that the lines may have data in different order, I would check for both expressions separately
        return (strpos($line, " method={$method} ") !== false && preg_match("#\spath={$requestPathExpression}\s#", $line));
    }

    /**
     * Qualify request Path for userId pattern by replacing placeholder with regex
     * @param $requestPath
     * @return mixed
     */
    protected function qualifyRequestPathForUserIdPattern(& $requestPath)
    {
        return str_replace('{user_id}', '((\d)+)', $requestPath);
    }

    /**
     * Format and echo output from extracted data
     * @param $method
     * @param $requestPath
     * @param array $summary
     */
    protected function formatOutput($method, $requestPath, array $summary)
    {
        // these 3 aren't really needed.
        // here for:
        // 1- To not give warnings in IDE about unknown variables
        // 2- To set a default value, always a safer practice.
        $dynos                  = array();
        $totalOccurrences       = 0;
        $responseTimes          = array();

        extract($summary);
        if (!$totalOccurrences)
        {
            // not using die() as it causes issue with ob_*
            echo "No occurrences of {$method} {$requestPath} found" . PHP_EOL;
            return;
        }

        $mostCommonDyno         = ArrayUtil::findModes($dynos);
        $leastCommonDyno        = ArrayUtil::findLeastCommonValues($dynos);
        $meanResponseTime       = ArrayUtil::findMean($responseTimes);
        $medianResponseTime     = ArrayUtil::findMedian($responseTimes);
        $modeResponseTimes      = ArrayUtil::findModes($responseTimes);
        $minResponseTime        = min($responseTimes);
        $maxResponseTime        = max($responseTimes);
        $summary                = <<<SUMMARY
        Number of times request was made: %d
        Most active Dyno(s): %s
        Least active Dyno(s): %s
        Min. Response Time: %d
        Max. Response Time: %d
        Mean Response Time: %f
        Median Response Time: %f
        Mode Response Time(s): %s

SUMMARY;
        echo sprintf($summary, $totalOccurrences,
                                implode(', ' , $mostCommonDyno),
                                implode(', ' , $leastCommonDyno),
                                $minResponseTime,
                                $maxResponseTime,
                                $meanResponseTime,
                                $medianResponseTime,
                                implode(', ' , $modeResponseTimes)
                            );
    }

    /**
     * Validate the log file path provided against bunch of criteria
     * @param $filePath
     */
    protected function validateLogFile($filePath)
    {
        $this->validateFilePath($filePath);
        $this->validateFilePathIsFile($filePath);
        $this->validateFileExistsAndIsReadable($filePath);
        $this->validateFileIsNotEmpty($filePath);
    }

    /**
     * Validate provided file path is a string
     * @param $filePath
     */
    protected function validateFilePath($filePath)
    {
        if (!is_string($filePath))
        {
            throw new RuntimeException("File path must be a string");
        }
    }

    /**
     * Ensure provided path is a file
     * @param $filePath
     */
    protected function validateFilePathIsFile($filePath)
    {
        if (!is_file($filePath))
        {
            throw new RuntimeException("{$filePath} is not a file");
        }
    }

    /**
     * Ensure provided path exists and is readable
     * @param $filePath
     */
    protected function validateFileExistsAndIsReadable($filePath)
    {
        if (!is_readable($filePath))
        {
            throw new RuntimeException("{$filePath} is not readable");
        }
    }

    /**
     * Ensure provided file path is not empty
     * @param $filePath
     */
    protected function validateFileIsNotEmpty($filePath)
    {
        // === because filesize can return false on error which is == 0 but !== 0.
        if (filesize($filePath) === 0)
        {
            // another option here would be to die with the same message but I didn't feel like doing that.
            // or, one other option would be to exit with a specific error code(defined as constant in class)
            // which, again, I didn't feel like doing.
            throw new RuntimeException("{$filePath} is empty");
        }
    }

    /**
     * Calculate response time value(connect + service) from log entry
     * @param $logEntry
     * @return int
     */
    protected function getResponseTimeValueFromLogEntry($logEntry)
    {
        $connectTime    = $this->getTimeValueFromLogEntryForKey($logEntry, static::CONNECT_TIME_KEY);
        $serviceTime    = $this->getTimeValueFromLogEntryForKey($logEntry, static::SERVICE_TIME_KEY);
        return $connectTime + $serviceTime;
    }

    /**
     * Extract a time value from log entry
     * @param $logEntry
     * @param $key
     * @return int
     */
    protected function getTimeValueFromLogEntryForKey($logEntry, $key)
    {
        $value      = $this->getValueFromLogEntryForKey($logEntry, $key);
        // strip the time suffix off it.
        $value = substr($value, 0, -1 * strlen(static::TIME_UNIT));
        // this type cast is here just to be explicit about what I am returning
        return ((int)$value);
    }

    /**
     * Find the value for a given key inside log entry
     * @param $logEntry
     * @param $key
     * @return mixed
     */
    protected function getValueFromLogEntryForKey($logEntry, $key)
    {
        // the pattern in this case is pretty simple so there won't be much of a performance hit while
        // compiling this regex.
        // I could have done substr strpos amalgamation but it wouldn't be as nice to read as this regex.
        $pattern    = '|' . $key . static::KEY_VALUE_DELIMITER . '((\S)+)*|';
        $matches    = array();
        $found      = preg_match($pattern, $logEntry, $matches);
        if (!$found)
        {
            throw new RuntimeException("Unable to parse for: {$key}." . PHP_EOL . "Entry: " . PHP_EOL . $logEntry);
        }
        return $matches[1];
    }
}