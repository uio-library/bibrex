<?php

namespace App;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class ThingProperties implements JsonSerializable, Arrayable
{
    protected $data;

    protected $schema = [
        'loan_time' => [
            'type' => 'integer',
            'default' => 1,
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
                        $this->data[$key][$lang] = array_get($data, "$key.$lang");
                    }
                    break;

                default:
                    $this->data[$key] = array_get($data, $key, array_get($options, 'default'));
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
        return array_get($this->data, $key, $default);
    }

    public function set($key, $value)
    {
        return array_set($this->data, $key, $value);
    }

    public function __get($key)
    {
        return $this->get($key);
    }
}
