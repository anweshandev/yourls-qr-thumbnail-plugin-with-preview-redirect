# Preview and Redirect Plugin - Yourls

## Description
When you use short-link like `https://sho.rt/mylink` and it redirects to `https://www.example.com/redirect-with-shortlink/long-link/my-link`, the user never knows where this redirect. To add a trusty layer over your user, I introduce you to the **Preview and Redirect Plugin** (with QR Code, Thumbnail Generation, Pre-Preview & Forced-Preview [Complementary]).

## Features (A Lot!!)
- [X] Support to generate a thumbnail by adding `.i` to the short-link.
- [X] Support for `QR` generation by adding `.qr` to end of the short link.
- [X] Support for preview and redirection:
	- [X] This can be done by adding `~` character to the end of the short-link. (This is an action caused by default)
	- [X] You can choose to enable/disable automatically preview before redirect for a preset default number of second.

## Enabling and Disabling
1. If you want to enable the `QR (.qr)` generation, then you need to modify in `users/config.php`, by adding:
```php
	# Defining the constant to disable QR feature
	define('F21_QR_PLUGIN', true); // true -> enabled, false -> disabled (default).
```
**By default:** The plugin is disabled.

2. If you want to enable the `thumbnail (.i) generation plugin`, then you need to modify in `users/config.php`, by adding:
```php
# Defining the constant to disable thumbnail feature.
define('F21_THUMB_PLUGIN', true); // true -> enabled, false -> disabled (default).
```
**By default:** The plugin is disabled.

3. If you choose to customize the preview before redirect, the following should be noted:
	- Auto Preview before redirect by default is **DISABLED**. To enable use the code below in `users/config.php`.
	```php
	# Defining the constant to enable preview before redirect
	define('F21_PREVIEW_REDIRECT', true); 	// false -> disabled (default), true -> enabled.
	```
	- You can also alter the wait time to show the preview page, before automatically redirecting.
	```php
	# Modifying the wait time (0) means disabled, i.e. the user needs to click to redirect same as adding ~
	define('F21_PREVIEW_TIME', 12); // Value is an integer in seconds, 0 -> disabled. By default it is 0 seconds, so disabled.
	```
	**The constant `F21_PREVIEW_TIME` is only effective if `F21_PREVIEW_REDIRECT` is `true`.**

	**Default:** The constant of time has a value `0`, which means infinite waiting time. If we do not find a valid integer. Then we consider the same to be `0` by default.

	**Range:** The valid range of the time is between [0, 60] both inclusive.

	**Unit of Measurement:** The time here is measured in seconds.

## Other features:
If you like to redirect without seeing the preview page at the same time also keeping the Preview Before Redirect Plugin active, regardless of time (`as defined or not defined by F21_PREVIEW_TIME`), we can use `?utm_redirect=auto&utm_preview=1`, to auto redirect without the preview page.

### Expected Enhanced:
- Ability to toggle on or off this feature.
- Defining your own key-value pair.

## Admin Area Add-On:

- Enabling this plugin will add ![Preview Button](https://image.flaticon.com/icons/png/16/142/142336.png "Preview Button 16x16"), at the admin area. Clicking on the same will redirect to the preview page, i.e. `sho.rt/link~`.

- If QR Module is enabled, the plugin will add ![QR Code Button](https://image.flaticon.com/icons/png/16/1287/1287558.png "QR Button 16x16") at the admin area. Clicking on the same will redirect to the QR Page i.e. `sho.rt/link.qr`.

- If the thumb module is enabled, the plugin will add ![Thumbnail Button](https://image.flaticon.com/icons/png/16/570/570975.png "Thumbnail Button 16x16") at the admin area. Clicking on the same will redirect to the QR Page i.e. `sho.rt/link~`.

## Installation:

### Steps
1. Download the code by either by `git clone` or by downloading as `ZIP`.
2. Extract and place it in `user/plugins` folder.
3. Go to the Administration Page -> Manage Plugins. Click to activate plugin.

### Google PageSpeed Insights

We use Google PageSpeed Insights API to get you a thumbnail of the page you are requesting. This API works without an `API_KEY`. However if you have too much traffic, you may exceed the quota. This will give you a `429 Quota exceeded for quota metric 'Queries' and limit 'Queries per minute`. To avoid such we request you acquire an API Key. Before you do so you may want to see the docs at [https://developers.google.com/speed/docs/insights/v5/get-started](https://developers.google.com/speed/docs/insights/v5/get-started). You can create an API Key from the docs too. If you are willing to use an existing API Key, you may do so. Please do not forget to enable *PageSpeed Insights API*. For security Google (and also myself) recommend you restrict the use of your API key to a subset of websites.

**Caution:** If you are limiting your keys to certain referrers only, please do not forget to add the exact domain (*and path if any*), to the list of exceptions as defined by the constant `YOURLS_SITE` in `users/config.php`.

*If you are willing to use with an API Key, please use the following code to associate it correctly:*
```php
# Defining the API Key.
define('F21_API_KEY', 'xxxxxxxxxxxxxxxxxxxxx');

# Do we add the referrer header
# By default NO (by false)
define('F21_API_REF', true);
```

**Note:** Please avoid disclosing your *API_KEY* to anyone. If you see any unwanted traffic, consider **regenerating** your key.

#### Composer stuff:

If you want to you can always run the composer statement.

```cmd
composer update
```
OR,

```cmd
php composer.phar update
```

For more information about composer, visit [Composer Documentation](https://getcomposer.org/doc/).

To know how to install `composer.phar`, please see [Installation Guidelines](https://getcomposer.org/download/).

### Word of caution!!

#### Introduction
I have been testing this on `localhost` as well as `https://stp.rf.gd`. I myself use the renowned [Plugin Name: Sean's QR Code Short URLs](https://github.com/seandrickson/YOURLS-QRCode-Plugin) with [404 If Not Found ☑️](https://github.com/YOURLS/404-if-not-found). However somehow if I go to the URL `https://sho.rt/link.qr` it immediately shows me a `HTTP 404 - Not Found Error`. I understood that the action `loader_failed` was being fired after one of the following actions:
- [x] redirect_keyword_not_found
- [ ] infos_keyword_not_found
- [ ] redirect_no_keyword
- [ ] infos_no_keyword

#### Investigation & Faulty Code
On investigation I found these actions at https://yourls.org/hooklist.php. From there I searched each action being called. If you notice both `redirect_keyword_not_found` and `loader_failed` are being fired on the page `yourls_loader.php`. On inspecting I found:

```php
/* Code Before */
// Past this point this is a request the loader could not understand : not a valid shorturl, not a bookmarklet
--> yourls_do_action( 'redirect_keyword_not_found', $keyword );
--> yourls_do_action( 'loader_failed', $request );
/* Code After */
```
Please note `-->` is not part of the code. It **here denotes the faulty piece of code**.

If you closely notice `redirect_keyword_not_found` was fired befire `loader_failed`. So `link.qr` was being flagged by `redirect_keyword_not_found` action (as it does not exist in the database) and hence a `404 Not Found`.

#### How to get rescued?

There are actually two ways.

1. The simple way is to swap the faulty piece of code with each other, i.e.,

```php
/** @ yourls_loader.php nearly end of file **/
yourls_do_action('loader_failed', $request);
yourls_do_action('redirect_keyword_not_found', $keyword);
```
**However it is not encouraged as if you change the code, you need to do the same for every update, and it is discouraged to directly modify the core.**

2. Find and comment each plugin(s) which use the action `redirect_keyword_not_found` such as the 404 Plugin by ozh.
```php
// yourls_add_action('redirect_keyword_not_found', 'callback_function');
```
*Note: `callback_function` can be* **action or filter** *present here in the [hooklist](https://yourls.org/hooklist.php).*

## Translating
This plugin already translated to English and Russian, simply uses whatever language YOURLS uses, as described [here](https://github.com/YOURLS/YOURLS/wiki/YOURLS-in-your-language#install-yourls-in-your-language).

If you want to translate this plugin into your own language, [this blog post](http://blog.yourls.org/2013/02/workshop-how-to-create-your-own-translation-file-for-yourls/) from YOURLS describes how to do it. You can find the latest .pot file in the `languages` folder of the plugin directory. Please follow the contributing guidelines below to add your translation to plugin.

## Demo:

1. For QR Preview: [https://stp.rf.gd/f21.qr](https://stp.rf.gd/f21.qr)
2. For Thumbnail Preview: [https://stp.rf.gd/f21.i](https://stp.rf.gd/f21.i)
3. For Preview: [https://stp.rf.gd/f21~](https://stp.rf.gd/f21~)
4. For Pre-Redirect Preview: [https://stp.rf.gd/f21](https://stp.rf.gd/f21)



## Testing:
This plugin has been tested on `YOURLS 1.5 through 1.8.1`, both on `localhost` and on a live server at `https://stp.rf.gd`.

## License:
The code is licensed under *MIT License*. A copy of the license should be included in every export of this plugin. However if the same has not been exported, the terms are as below.

```
MIT License

Copyright (c) 2021 Anweshan Roy Chowdhury

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

## Contact Me

#### Website

[![👉 Website of @formula21](https://s0.wp.com/mshots/v1/https%3A%2F%2Fformula21.github.io?w=250 "👉 @formula21")](https://formula21.github.io)

#### Scan
![Scan](https://gravatar.com/11250e280ebaee84010f3b5ca23a918a.qr?size=80)

#### 4. Social Media

[![@anweshanrc151](https://image.flaticon.com/icons/png/32/733/733579.png "@anweshanrc151 Twitter")](http://twitter.com/anweshanrc151) [![anweshan.roychowdhury](https://image.flaticon.com/icons/png/32/145/145802.png)](https://www.facebook.com/anweshan.roychowdhury) [![anweshan_roy_chowdhury](https://image.flaticon.com/icons/png/32/2111/2111463.png)](https://www.instagram.com/anweshan_roy_chowdhury/) [![mailto:anweshanrc15@gmail.com](https://image.flaticon.com/icons/png/32/1932/1932975.png)](mailto:anweshanrc15@gmail.com)
