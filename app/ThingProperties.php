<?php

namespace App;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use JsonSerializable;

class ThingProperties implements JsonSerializable, Arrayable
{
    protected $data;

    protected $schema = [
        'loan_time' => [
            'type' => 'integer',
            'default' => 1,
        ],
        'name' => [
            'type' => 'multilingual'
        ],
        'name_indefinite' => [
            'type' => 'multilingual'
        ],
        'name_definite' => [
            'type' => 'multilingual',
        ],
    ];

    protected $languages = ['nob', 'nno', 'eng'];

    public function __construct($data)
    {
        $this->data = [];
        foreach ($this->schema as $key => $options) {
            switch ($options['type']) {
                case 'multilingual':
                    foreach ($this->languages as $lang) {
                        $this->data[$key][$lang] = Arr::get($data, "$key.$lang");
                    }
                    break;

                default:
                    $this->data[$key] = Arr::get($data, $key, Arr::get($options, 'default'));
            }
        }
    }

    public function toArray()
    {
        return $this->data;
    }

    public function jsonSerialize()
    {
        return $this->data;
    }

    public function get($key, $default = null)
    {
        return Arr::get($this->data, $key, $default);
    }

    public function set($key, $value)
    {
        return Arr::set($this->data, $key, $value);
    }

    public function __get($key)
    {
        return $this->get($key);
    }
}
