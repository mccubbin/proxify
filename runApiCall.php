<?php

/**
 * Note to Maurice and Nadya:
 * I specialize in the Laravel web framework.
 * There are better ways to make API calls
 * and deal with arrays (collections) in Laravel.
 *
 * I hope you will allow me the chance to show you
 * my Laravel skills some day.
 *
 * Sincerely,
 * Phillip McCubbin
 * pd.mccubbin@gmail.com
 */
require('src/Caller.php');

use \App\Caller;

$caller = new Caller;

$caller->make('https://api.github.com/users', 'get')
        ->where('site_admin','=', false)
        ->sort('node_id', 'DESC')
        ->sort('login', 'DESC')
        ->sort('avatar_url', 'DESC')
        ->only(['login', 'node_id', 'avatar_url', 'site_admin']);

