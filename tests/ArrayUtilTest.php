<?php
require_once(__DIR__ . '/../src/ArrayUtil.php');

class ArrayUtilTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Array cannot be empty
     */
    public function testFindMeanWithEmptyArray()
    {
        ArrayUtil::findMean(array());
    }

    public function testFindMeanWithSingleElementArray()
    {
        $mean   = ArrayUtil::findMean(array(3));
        $this->assertEquals(3, $mean);
    }

    /**
     * @depends testFindMeanWithSingleElementArray
     */
    public function testFindMeanWithMultipleElementsArray()
    {
        $mean   = ArrayUtil::findMean(array(1, 4, 5, 7));
        $this->assertEquals(4.25, $mean);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Array cannot be empty
     */
    public function testFindModeWithEmptyArray()
    {
        ArrayUtil::findModes(array());
    }

    public function testFindModeWithSingleElementArray()
    {
        $mode   = ArrayUtil::findModes(array(4));
        $this->assertEquals(array(4), $mode);
    }

    /**
     * @depends testFindModeWithSingleElementArray
     */
    public function testFindModeWithSingleMostCommonElementArray()
    {
        $mode   = ArrayUtil::findModes(array(1, 2, 4, 2));
        $this->assertEquals(array(2), $mode);
    }

    /**
     * @depends testFindModeWithSingleMostCommonElementArray
     */
    public function testFindModeWithSingleMultipleCommonElementsArray()
    {
        $mode   = ArrayUtil::findModes(array(1, 2, 4, 2, 1));
        $this->assertEquals(array(1, 2), $mode);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Array cannot be empty
     */
    public function testFindLeastCommonValuesWithEmptyArray()
    {
        ArrayUtil::findLeastCommonValues(array());
    }

    public function testFindLeastCommonValuesWithSingleElementArray()
    {
        $mode   = ArrayUtil::findLeastCommonValues(array(4));
        $this->assertEquals(array(4), $mode);
    }

    /**
     * @depends testFindLeastCommonValuesWithSingleElementArray
     */
    public function testFindLeastCommonValuesWithSingleLeastCommonElementArray()
    {
        $mode   = ArrayUtil::findLeastCommonValues(array(1, 2, 4, 2, 4, 2));
        $this->assertEquals(array(1), $mode);
    }

    /**
     * @depends testFindLeastCommonValuesWithSingleLeastCommonElementArray
     */
    public function testFindLeastCommonValuesWithMultipleLeastCommonElementsArray()
    {
        $mode   = ArrayUtil::findLeastCommonValues(array(1, 2, 4, 2));
        $this->assertEquals(array(1, 4), $mode);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Array cannot be empty
     */
    public function testFindMedianWithEmptyArray()
    {
        ArrayUtil::findMedian(array());
    }

    /**
     * @depends testFindMeanWithSingleElementArray
     */
    public function testFindMedianWithSingleElementArray()
    {
        $mean   = ArrayUtil::findMedian(array(3));
        $this->assertEquals(3, $mean);
    }

    /**
     * @depends testFindMedianWithSingleElementArray
     * @depends testFindMeanWithMultipleElementsArray
     */
    public function testFindMedianWithEvenNumberOfItemsArrays()
    {
        $mean   = ArrayUtil::findMedian(array(1, 4, 5, 7));
        $this->assertEquals(4.5, $mean);
    }

    /**
     * @depends testFindMedianWithEvenNumberOfItemsArrays
     */
    public function testFindMedianWithOddNumberOfItemsArrays()
    {
        $mean   = ArrayUtil::findMedian(array(1, 4, 5));
        $this->assertEquals(4, $mean);
    }
}