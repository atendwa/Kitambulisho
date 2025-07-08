<?php

declare(strict_types=1);

use Atendwa\Kitambulisho\Http\Controllers\DestroySession;

Route::post('/logout', DestroySession::class)->middleware('auth')->name('logout');
