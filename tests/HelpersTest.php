<?php

namespace Nahid\Presento\Tests;

use PHPUnit\Framework\TestCase;

final class HelpersTest extends TestCase {
    public function camelCaseDataProvider() {
        return [
            // [ 'actual data', 'expected data', 'delimiter' (optional) ]
            ["method", "Method"],
            ["double_word", "DoubleWord"],
            ["a_lot_of_words", "ALotOfWords"],
            ["FIX_CAPITALIZATION", "FixCapitalization"],
            ["this-should-work-too", "ThisShouldWorkToo", "-"],
        ];
    }

    /**
     * @dataProvider camelCaseDataProvider
     *
     * @param string $string
     * @param string $expected
     * @param string|null $delimiter
     */
    public function testToCamelCaseMethod($string, $expected, $delimiter = null) {
        if ($delimiter) {
            $actual = to_camel_case($string, $delimiter);
        } else {
            $actual = to_camel_case($string);
        }

        $this->assertEquals($expected, $actual);
    }

    public function isCollectionDataProvider() {
        return [
            // [ 'actual data', 'expected data' ]
            ["scalar_data", false],
            [[], false],
            [[1, 2, 3], false],
            [['numbers' => [1, 2, 3], 'names' => ["john", "Doe"]], true],
        ];
    }

    /**
     * @dataProvider isCollectionDataProvider
     *
     * @param mixed $data
     * @param bool $expected
     */
    public function testIsCollectionMethod($data, $expected) {
        $this->assertEquals($expected, is_collection($data));
    }

    public function getFromArrayDataProvider() {
        return [
            // [ 'actual data', 'path', 'expected data' ]
            [["key" => "value"], "key", "value"],
            [["key" => "value"], "", ["key" => "value"]],
            [null, "invalid_path", null],
            [["key" => [1, 2]], "key.1", 2],
            [["key" => ["foo" => ["bar" => "value"]]], "key.bar.foo", null],
            [["key" => ["foo" => ["bar" => "value"]]], "key.foo.bar", "value"],
            [["key" => ["foo" => [["bar" => "value"]]]], "key.foo.0.bar", "value"],
        ];
    }

    /**
     * @dataProvider getFromArrayDataProvider
     *
     * @param mixed $data
     * @param string $path
     * @param mixed $expected
     */
    public function testGetFromArrayMethod($data, $path, $expected) {
        $this->assertEquals($expected, get_from_array($data, $path));
    }

    public function blankDataProvider() {
        return [
            // [ 'actual data', 'expected data' ]
            [1, false],
            [true, false],
            [[], true],
            [null, true],
            ["empty", false],
            ["   ", true],
        ];
    }

    /**
     * @dataProvider blankDataProvider
     *
     * @param mixed $data
     * @param bool $expected
     */
    public function testBlankMethod($data, $expected) {
        $this->assertEquals($expected, blank($data));
    }
}