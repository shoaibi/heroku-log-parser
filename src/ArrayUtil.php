<?php

abstract class ArrayUtil
{
    /**
     * Validate if provided array is not empty
     * @param array $values
     */
    protected static function validateArrayIsNotEmpty(array $values)
    {
        if (empty($values))
        {
            throw new RuntimeException("Array cannot be empty");
        }
    }

    /**
     * Calculates mean of values provided in an array
     * @param array $values
     * @return float
     */
    public static function findMean(array $values)
    {
        static::validateArrayIsNotEmpty($values);
        return (array_sum($values) / count($values));
    }

    /**
     * Calculates modes of values provided in an array
     * @param array $values
     * @return array
     */
    public static function findModes(array $values)
    {
        return static::findLeastOrMostCommonValues($values, true);
    }

    /**
     * Find least common values in an array
     * @param array $values
     * @return array
     */
    public static function findLeastCommonValues(array $values)
    {
        return static::findLeastOrMostCommonValues($values, false);
    }

    /**
     * Find the least or most common values in an array
     * @param array $values
     * @param bool $mostCommon
     * @return array
     */
    protected static function findLeastOrMostCommonValues(array $values, $mostCommon = true)
    {
        static::validateArrayIsNotEmpty($values);
        $frequency              = array_count_values($values);
        if ($mostCommon)
        {
            $occurrenceCount    = max($frequency);
        }
        else
        {
            $occurrenceCount    = min($frequency);
        }
        return array_keys($frequency, $occurrenceCount);
    }

    /**
     * Find mean of values provided in an array
     * @param array $values
     * @return float
     */
    public static function findMedian(array $values)
    {
        asort($values, SORT_NUMERIC);
        $valueCount     = count($values);
        $medianIndex    = floor($valueCount/2);
        if ($valueCount % 2 == 0)
        {
            // I have even number of values
            // get the middle 2 values and get the mean of those.
            $medianArray    = array_slice($values, $medianIndex - 1, 2);
            return static::findMean($medianArray);
            // I could have hardcoded it to be:
            //return (($values[$medianIndex - 1] + $values[$medianIndex]) / 2);
            // but that violates separation of concern principle.
        }
        else
        {
            return $values[$medianIndex];
        }
    }
}
