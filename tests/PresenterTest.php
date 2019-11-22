<?php

namespace Louis1021\Presento\Tests;

use Louis1021\Presento\Presenter;
use Louis1021\Presento\Transformer;
use PHPUnit\Framework\TestCase;

final class PresenterTest extends TestCase {
    public static $sampleData = [
        "id" => 123456,
        "name" => "Nahid Bin Azhar",
        "email" => "talk@nahid.im",
        "type" => 1,
        "is_active" => 1,
        "created_at" => "2018-01-02 02:03:04",
        "updated_at" => "2018-01-02 02:03:04",
        "deleted_at" => "2018-01-02 02:03:04",
        "projects" => [
            [
                "id" => 1,
                "name" => "Laravel Talk",
                "url" => "https://github.com/nahid/talk",
                "license" => "CC0",
                "created_at" => "2016-02-02 02:03:04",
            ],
            [
                "id" => 2,
                "name" => "JsonQ",
                "url" => "https://github.com/nahid/jsonq",
                "license" => "MIT",
                "created_at" => "2018-01-02 02:03:04",
            ],
        ],
    ];

    function arrayEqual($arr1, $arr2) {
        foreach ($arr1 as $k => $v) {
            if (!array_key_exists($k, $arr2)) {
                return false;
            }

            if (!is_array($v)) {
                if ($v !== $arr2[$k]) {
                    return false;
                }
            }

            if (is_array($v)) {
                $resp = $this->arrayEqual($v, $arr2[$k]);

                if (!$resp) {
                    return false;
                }
            }

        }

        return true;
    }

    public function test_presenter_returns_only_selected_fields() {
        $presenter = new TestComplexPresenterObject(static::$sampleData);
        //var_dump($presenter);
        //var_dump("------");
        $expected = array(
            "id" => 123456,
            "name" => "Nahid Bin Azhar",
            "email" => "talk@nahid.im",
            "type" => 1,
            "is_active" => 1,
            "projects" => [
                [
                    "id" => 1,
                    "name" => "Laravel Talk",
                    "url" => "https://github.com/nahid/talk",
                    "license" => "CC0",
                    "created_at" => "2016-02-02 02:03:04",
                ],
                [
                    "id" => 2,
                    "name" => "JsonQ",
                    "url" => "https://github.com/nahid/jsonq",
                    "license" => "MIT",
                    "created_at" => "2018-01-02 02:03:04",
                ],
            ],
        );
        $this->assertTrue($this->arrayEqual($presenter->get(), $expected));

        $presenter = new TestDatatablePresenterObject(static::$sampleData);
        //var_dump($presenter);
        //var_dump("------");
        $expected = array(
            123456,
            "Nahid Bin Azhar",
            "talk@nahid.im",
            1,
            1,
        );

        $this->assertTrue($this->arrayEqual($presenter->get(), $expected));

        $presenter = new TestAliasPresenterWithTransformerObject(static::$sampleData);
        //var_dump($presenter);
        //var_dump("------");
        $expected = array(
            "user_id" => -123456,
            "name" => "Nahid Bin Azhar",
            "email" => "talk@nahid.im",
            "type" => 1,
            "is_active" => 1,
            "top_package" => "Laravel Talk",
        );

        $this->assertTrue($this->arrayEqual($presenter->get(), $expected));
    }

    public function test_presenter_returns_non_exists_fields_value_null() {
        $data = [
            'id' => 1,
        ];

        $presenter = new TestPresenterWithNonExistsFieldsObject($data);
        $expected = [
            "name" => null,
            "email" => null,
        ];

        $this->assertTrue($this->arrayEqual($presenter->get(), $expected));
    }
}

class TestComplexPresenterObject extends Presenter {
    public function present() {
        return [
            'id',
            'name',
            'email',
            'type',
            'is_active',
            'projects',
        ];
    }
}

class TestDatatablePresenterObject extends Presenter {
    protected $formatDatatables = true;

    public function present() {
        return [
            'id',
            'name',
            'email',
            'type',
            'is_active',
        ];
    }
}

class TestTransformer extends Transformer {
    public function getUserIdProperty($value) {
        return -$value;
    }
}

class TestAliasPresenterWithTransformerObject extends Presenter {
    public function present() {
        return [
            'user_id' => 'id',
            'name',
            'email',
            'type',
            'is_active',
            "top_package" => "projects.0.name",
        ];
    }

    public function transformer() {
        return TestTransformer::class;
    }
}

class TestPresenterWithNonExistsFieldsObject extends Presenter {
    public function present() {
        return [
            'name',
            'email',
        ];
    }
}