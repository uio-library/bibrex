<?php
return array(
  'default' => 'sqlite',
  'connections' => array(

    'sqlite' => array(
      'driver'   => 'sqlite',
      'database' => ':memory:',
      'prefix'   => ''
    ),

    'mysql' => array(
      'driver'    => 'mysql',
      'host'      => 'localhost',
      'database'  => 'bibrex_test',
      'username'  => 'tester',
      'password'  => '',
      'charset'   => 'utf8',
      'collation' => 'utf8_unicode_ci',
      'prefix'    => '',
    ),

  )
);