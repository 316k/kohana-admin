<?php defined('SYSPATH') or die('No direct script access.');


Route::set('manager', 'admin/<mode>/<model>(/<id>)', array(
        'mode' => 'list|edit|delete|properties',
        'model' => '\w+'
    ))
    ->defaults(array(
        'controller' => 'admin',
        'action'     => 'manager',
    )
);

