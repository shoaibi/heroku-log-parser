<?php
require_once(__DIR__ . '/../src/LogParser.php');

class LogParserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage File path must be a string
     */
    public function testNullFilePathConstruct()
    {
        new LogParser(null);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage File path must be a string
     */
    public function testIntegerFilePathConstruct()
    {
        new LogParser(1);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage File path must be a string
     */
    public function testArrayFilePathConstruct()
    {
        new LogParser(array());
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage File path must be a string
     */
    public function testObjectFilePathConstruct()
    {
        new LogParser(new stdClass());
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage LogParserTest is not a file
     */
    public function testRandomStringFilePathConstruct()
    {
        new LogParser(__CLASS__);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage ../tests is not a file
     */
    public function testValidFilePathButIsFolderConstruct()
    {
        new LogParser('../tests');
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage  is empty
     */
    public function testValidFilePathButIsEmptyFileConstruct()
    {
        $tmpHandle      = tmpfile();
        $metaData       = stream_get_meta_data($tmpHandle);
        $tmpFilename    = $metaData['uri'];
        new LogParser($tmpFilename);
        fclose($tmpHandle);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage  is not readable
     */
    public function testValidFilePathButUnreadableFileConstruct()
    {
        $tmpHandle      = tmpfile();
        $metaData       = stream_get_meta_data($tmpHandle);
        $tmpFilename    = $metaData['uri'];
        chmod($tmpFilename, 0000);
        new LogParser($tmpFilename);
        fclose($tmpHandle);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage   for reading
     */
    public function testParseWithFileBecomingUnreadableAfterConstruct()
    {
        $tmpHandle      = tmpfile();
        fwrite($tmpHandle, 'random');
        $metaData       = stream_get_meta_data($tmpHandle);
        $tmpFilename    = $metaData['uri'];
        $parser         = new LogParser($tmpFilename);
        chmod($tmpFilename, 0000);
        $parser->parse('GET', '/api/users/1');
        fclose($tmpHandle);
    }

    /**
     * @expectedException BadFunctionCallException
     * @expectedExceptionMessage method and requestPath both should be provided
     */
    public function testParseWithBothArgumentsAsNull()
    {
        $tmpHandle      = tmpfile();
        fwrite($tmpHandle, 'random');
        $metaData       = stream_get_meta_data($tmpHandle);
        $tmpFilename    = $metaData['uri'];
        $parser     = new LogParser($tmpFilename);
        $parser->parse(null, null);
        fclose($tmpHandle);
    }

    public function testParseWithNoOccurrencesFound()
    {
        $tmpHandle      = tmpfile();
        fwrite($tmpHandle, 'random');
        $metaData       = stream_get_meta_data($tmpHandle);
        $tmpFilename    = $metaData['uri'];
        $parser     = new LogParser($tmpFilename);
        ob_start();
        $parser->parse('GET', '/api/users/1');
        $contents   = ob_get_clean();
        $this->assertEquals('No occurrences of GET /api/users/1 found', $contents);
        fclose($tmpHandle);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Unable to parse for: connect.
     */
    public function testParseWithInvalidFormat()
    {
        $tmpHandle      = tmpfile();
        fwrite($tmpHandle, 'at=info method=GET path=/api/users/1 dyno=web.13');
        $metaData       = stream_get_meta_data($tmpHandle);
        $tmpFilename    = $metaData['uri'];
        $parser     = new LogParser($tmpFilename);
        $parser->parse('GET', '/api/users/1');
        fclose($tmpHandle);
    }

    public function testParseWithValidFormatAndData()
    {
        $tmpHandle      = tmpfile();
        $fileContent    = 'at=info method=GET path=/api/users/0 dyno=web.13 connect=10ms service=20ms ';
        // even if I have lines with invalid format as long as these don't match the requestUri and method
        // I wouldn't need to worry
        $fileContent    .= PHP_EOL . 'at=info method=GET path=/api/users/-1 dyno=web.1';
        for ($i = 1; $i <= 20; $i++)
        {
            $fileContent    .= PHP_EOL . 'at=info method=GET path=/api/users/1 dyno=web.' . ($i % 3);
            $fileContent    .= ' connect=' . (rand(1, 9) * $i) .'ms service=' . (rand(20, 50)*$i) . 'ms ';
        }
        fwrite($tmpHandle, $fileContent);
        $metaData       = stream_get_meta_data($tmpHandle);
        $tmpFilename    = $metaData['uri'];
        $parser     = new LogParser($tmpFilename);
        ob_start();
        $parser->parse('GET', '/api/users/1');
        fclose($tmpHandle);
        $contents   = ob_get_clean();
        $this->assertContains('Number of times request was made: 20', $contents);
        $this->assertContains('Most active Dyno(s): web.1, web.2', $contents);
        $this->assertContains('Least active Dyno(s): web.0', $contents);
        $this->assertContains('Min. Response Time: ', $contents);
        $this->assertContains('Max. Response Time: ', $contents);
        $this->assertContains('Mean Response Time: ', $contents);
        $this->assertContains('Median Response Time: ', $contents);
        $this->assertContains('Mode Response Time(s): ', $contents);
    }
}