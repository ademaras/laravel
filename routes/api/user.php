<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('new-user-notification', 'App\Http\Controllers\Api\UserController@newUserNotification');
Route::post('login', 'App\Http\Controllers\Api\AuthController@login');
Route::post('login-phone', 'App\Http\Controllers\Api\AuthController@loginPhone');
Route::post('register', 'App\Http\Controllers\Api\AuthController@register');
Route::post('login-phone-verify-code', 'App\Http\Controllers\Api\AuthController@loginVerifyCode');
Route::post('forgot-password', 'App\Http\Controllers\Api\AuthController@forgotpassword');
Route::post('reset-password', 'App\Http\Controllers\Api\AuthController@resetpassword');

Route::get('/wallpapers', 'App\Http\Controllers\Api\MainController@wallpapers')->name('wallpapers');

Route::prefix('user')->middleware('auth:sanctum')->namespace('App\Http\Controllers\Api')->group(function () {
    
    Route::post('social/toggle', 'ListController@userSocialToggle')->name('userSocialToggle');

    Route::post('change-package', 'UserController@changePackage')->name('changePackage');
    Route::get('socials', 'ListController@userSocials')->name('usocials');
    Route::post('social/reorder', 'ListController@userSocialReorder')->name('userSocialReorder');
    Route::post('social/{id}', 'UserController@updateLink')->name('link.update');


    Route::post('social/create', 'UserController@createLink')->name('link.create');

    Route::get('statics', 'ListController@statApi')->name('statApi');

    Route::post('contact', 'UserController@updateContact')->name('contact');

    Route::get('detail/{id}', 'UserController@userDetail')->name('userDetail');

    Route::get('details', 'UserController@details')->name('details');
    Route::post('update', 'UserController@update')->name('update');
    Route::post('username-update', 'UserController@usernameUpdate')->name('usernameUpdate');
    Route::delete('acc', 'UserController@delete')->name('delete');
    Route::post('change-password', 'UserController@changePassword')->name('change-password');

    Route::get('notify', 'UserController@notifyToggle')->name('notify');
    Route::get('notifications', 'UserController@notifications')->name('notifications');

    Route::prefix('stories')->name('stories.')->group(function () {
        Route::get('/me', 'StoryController@me')->name('me');
        Route::get('/past', 'StoryController@past')->name('past');
        Route::get('/', 'StoryController@index')->name('index');
        Route::get('/{id}', 'StoryController@show')->name('show');
        Route::post('/', 'StoryController@store')->name('store');
        Route::delete('/{id}', 'StoryController@destroy')->name('destroy');
    });

    Route::prefix('otocall')->name('otoCall.')->group(function () {
        Route::get('/', 'OtoCallController@index')->name('index');
        Route::get('/{id}', 'OtoCallController@show')->name('show');
        Route::post('/', 'OtoCallController@store')->name('store');
        Route::post('/{id}', 'OtoCallController@update')->name('update');
        Route::delete('/{id}', 'OtoCallController@destroy')->name('destroy');
    });

    Route::get('levels', 'UserController@levels')->name('levels');

    Route::prefix('card')->name('card.')->group(function () {
        Route::get('personal', 'CardController@personal')->name('personal');
        Route::post('personal', 'CardController@personalUpdate')->name('personal.update');

        Route::delete('image', 'CardController@deleteImage')->name('image.delete');

        Route::get('business', 'CardController@business')->name('business');
        Route::post('business', 'CardController@businessUpdate')->name('business.update');

        Route::get('socials', 'CardController@socials')->name('socials');
        Route::post('social/add2', 'CardController@socialAdd')->name('social.add');
        Route::post('social/add', 'CardController@socialAdd2')->name('social.add2');
        Route::get('social/statics', 'CardController@staticLinks')->name('staticLinks');
        Route::post('social/static-link-add', 'CardController@staticLinkAdd')->name('staticLinkAdd');
        Route::delete('social/static-link-delete/{id}', 'CardController@staticLinkDelete')->name('staticLinkDelete');
        Route::post('social/contact-add', 'CardController@contactAdd')->name('contact.add');
        Route::post('social/contact-edit/{id}', 'CardController@contactEdit')->name('contact.edit');
        Route::post('social/{id}', 'CardController@socialEdit')->name('social.edit');
        Route::delete('social/{id}', 'CardController@socialDelete')->name('social.delete');
    });

    Route::prefix('list')->name('list.')->group(function () {
        Route::get('socials2', 'ListController@socials')->name('socials');
        Route::get('socials', 'ListController@socials2')->name('socials2');
        Route::get('links', 'ListController@links')->name('links');
        Route::post('reorder', 'ListController@reorder')->name('reorder');
    });


    Route::prefix('country')->name('country.')->group(function () {
        Route::get('list', 'CountryController@index')->name('index');
    });

    Route::prefix('department')->name('department.')->group(function () {
        Route::get('list', 'DepartmanController@list')->name('list');
        Route::post('add', 'DepartmanController@create')->name('add');
        Route::post('{id}/user', 'DepartmanController@addUser')->name('addUser');
        Route::post('edit/{id}', 'DepartmanController@update')->name('edit');
        Route::delete('{id}', 'DepartmanController@delete')->name('delete');
        Route::post('{id}/remove', 'DepartmanController@removeUser')->name('removeUser');
    });

    Route::prefix('coupon')->name('coupon.')->group(function () {
        Route::get('/', 'CouponController@dijiCoupons')->name('dijicoupons');
        Route::get('list', 'CouponController@list')->name('list');
        Route::post('add', 'CouponController@add')->name('add');
        Route::post('edit/{id}', 'CouponController@edit')->name('edit');
        Route::delete('{id}', 'CouponController@delete')->name('delete');
    });

    Route::prefix('qr')->name('qr.')->group(function () {
        Route::get('detail', 'QrController@detail')->name('detail');
        Route::post('update', 'QrController@update')->name('update');
    });

    Route::prefix('directory')->name('directory.')->group(function () {
        Route::get('/', 'DirectoryController@index')->name('index');
        Route::post('control', 'DirectoryController@store')->name('store');
    });

    Route::prefix('contact')->name('contact.')->group(function () {
        Route::get('list', 'ContactController@list')->name('list');
        Route::post('add', 'ContactController@add')->name('add');
        Route::post('edit/{id}', 'ContactController@edit')->name('edit');
        Route::delete('{id}', 'ContactController@delete')->name('delete');
    });

    Route::prefix('shop')->name('shop.')->group(function () {
        Route::get('list', 'ShopController@list')->name('list');
        Route::get('category/{id}', 'ShopController@category')->name('category');
        Route::get('detail/{id}', 'ShopController@detail')->name('detail');
        Route::get('products', 'ShopController@products')->name('products');
    });

    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('list', 'CartController@list')->name('list');
        Route::post('add', 'CartController@add')->name('add');
        Route::get('{id}/plus', 'CartController@plus')->name('plus');
        Route::get('{id}/minus', 'CartController@minus')->name('minus');
        Route::delete('{id}', 'CartController@delete')->name('delete');
        Route::post('complete', 'CartController@complete')->name('complete');
        Route::get('orders', 'CartController@orders')->name('orders');
    });

    Route::prefix('activation')->name('activation.')->group(function () {
        Route::get('category', 'ActivationController@category')->name('category');
        Route::post('send', 'ActivationController@send')->name('send');
    });

    Route::prefix('packets')->name('packets.')->group(function () {
        Route::get('list', 'PacketController@list')->name('list');
    });

    Route::prefix('catalog')->name('catalog.')->group(function () {
        Route::get('list', 'CatalogController@index')->name('index');
        Route::post('add', 'CatalogController@add')->name('add');
    });

    Route::prefix('youtube')->name('youtube.')->group(function () {
        Route::post('/', 'YoutubeController@update')->name('update');
        Route::get('/remove', 'YoutubeController@remove')->name('remove');
    });

    Route::prefix('survey')->name('survey.')->group(function () {
        Route::get('/', 'SurveyController@index')->name('index');
        Route::post('/reorder', 'SurveyController@reorder')->name('reorder');
        Route::post('/{survey}', 'SurveyController@store')->name('store');
    });
});

Route::middleware('auth:sanctum')->namespace('App\Http\Controllers\Api')->group(function () {
    Route::get('promotions', 'PromotionController@index')->name('promotions.index');
});
