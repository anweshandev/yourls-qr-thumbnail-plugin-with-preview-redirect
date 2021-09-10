<?php

/*
Plugin Name: Preview and Redirect Plugin with QR, Thumbnail.
Plugin URI: https://github.com/formula21/yourls-qr-thumbnail-plugin-with-preview-redirect.
Description: Preview URLs before you're redirected there with QR code and Thumbnail Image.
Version: 1.0
Author: formula21
Author URI: https://github.com/formula21
*/

if( !defined( 'YOURLS_ABSPATH' ) ) die();

if( !defined('F21_QR_PLUGIN')) define('F21_QR_PLUGIN', false);

if(!defined('F21_THUMB_PLUGIN')) define('F21_THUMB_PLUGIN', false);

if(!defined('F21_PREVIEW_REDIRECT')) define('F21_PREVIEW_REDIRECTf', false);

if(!defined('F21_PREVIEW_TIME')) define('F21_PREVIEW_TIME', 0);

define('F21_PREVIEW_CHAR', '~');


require_once( dirname(__FILE__).'/phpqrcode.php' );
require_once( dirname(__FILE__).'/f21qrcode.php' );
require_once( dirname(__FILE__).'/f21thumb.php' );
require_once( dirname(__FILE__).'/f21preview.php' );

yourls_add_action( 'loader_failed', 'formula21_loader_failed' );

function formula21_loader_failed($request){
   $pattern = yourls_make_regexp_pattern( yourls_get_shorturl_charset() );

  if(defined('F21_QR_PLUGIN') && is_bool(F21_QR_PLUGIN) && F21_QR_PLUGIN == !false && function_exists('formula21_qr_code')){
      formula21_qr_code($request, $pattern);
  }

  if(defined('F21_THUMB_PLUGIN') && is_bool(F21_THUMB_PLUGIN) && F21_THUMB_PLUGIN == !false && function_exists('formula21_thumb')){
      formula21_thumb($request, $pattern);
  }

  if( preg_match( "@^([$pattern]+)".F21_PREVIEW_CHAR."?/?$@", $request[0], $matches ) ) {
      $keyword = yourls_sanitize_keyword( $matches[1] );
      if( yourls_is_shorturl( $keyword ) ) {
          formula21_preview($keyword);
      }
  }

}

yourls_add_action('redirect_shorturl', 'formula21_redirect_shorturl');
function formula21_redirect_shorturl($args){
    header('Cache-Control: no-cache, no-store, private, must-revalidate');
    if(defined('F21_PREVIEW_REDIRECT') && is_bool(F21_PREVIEW_REDIRECT) && F21_PREVIEW_REDIRECT == !false){
        $request = $args[1];
        $pattern = yourls_make_regexp_pattern( yourls_get_shorturl_charset() );
	      if( preg_match( "@^([$pattern]+)$@", $request, $matches ) ) {
            $keyword = isset( $matches[1] ) ? $matches[1] : '';
            $keyword = yourls_sanitize_keyword( $keyword );
            if(yourls_is_shorturl($keyword)){
              formula21_preview($keyword, true);
            }
        }
    }
}

yourls_add_filter( 'action_links', 'formula21_action_links' );
function formula21_action_links( $action_links, $keyword, $url, $ip, $clicks, $timestamp ) {
   // One single action is already there, which is the preview...
    $surl = yourls_link( $keyword );
    $id = yourls_string2htmlid( $keyword ); // used as HTML #id
    if(defined('F21_QR_PLUGIN') && is_bool(F21_QR_PLUGIN) && F21_QR_PLUGIN == !false){
        $qr = '.qr';
      	$qrlink = $surl . $qr;

      	// Define the QR Code
      	$qrcode = array(
      		'href'    => $qrlink,
      		'id'      => "qrlink-$id",
      		'title'   => 'QR Code',
      		'anchor'  => 'QR Code'
      	);
        $action_links .= sprintf( '<a href="%s" id="%s" title="%s" class="%s">%s</a>',
      		$qrlink, $qrcode['id'], $qrcode['title'], 'button button_qrcode', $qrcode['anchor']
      	);
    }

    if(defined('F21_THUMB_PLUGIN') && is_bool(F21_THUMB_PLUGIN) && F21_THUMB_PLUGIN == !false){
      $thumb = '.i';
      $thumblink = $surl . $thumb;
      $thumbnail = array(
        'href' => $thumblink,
        'id'   => "thumblink-$id",
        'title'=> 'Thumbnail',
        'anchor'=> 'Thumbnail'
      );

      $action_links .= sprintf( '<a href="%s" id="%s" title="%s" class="%s">%s</a>',
        $thumblink, $thumbnail['id'], $thumbnail['title'], 'button button_thumbnail', $thumbnail['anchor']
      );

    }

    // We're adding ~ to the end of the URL, right?
    $preview = F21_PREVIEW_CHAR;
    $previewlink = $surl . $preview;

  	// Define the Preview Code
  	$previewcode = array(
    		'href'    => $previewlink,
    		'id'      => "previewlink-$id",
    		'title'   => 'Preview',
    		'anchor'  => 'Preview'
    	);

    // Add our Preview Code generator button to the action links list
  	$action_links .= sprintf( '<a href="%s" id="%s" title="%s" class="%s">%s</a>',
  		$previewlink, $previewcode['id'], $previewcode['title'], 'button button_previewcode', $previewcode['anchor']
  	);
    return $action_links;
}

yourls_add_action('html_head', 'formula21_html_head');
function formula21_html_head($context){
    foreach($context as $k):
		// If we are on the index page, use this css code for the button
  		if( $k == 'index' ):
        if(defined('F21_QR_PLUGIN') && is_bool(F21_QR_PLUGIN) && F21_QR_PLUGIN == !false):
  ?>
<style type="text/css">
    td.actions .button_qrcode {
    margin-right: 0;
    background: url(data:image/png;base64,R0lGODlhEAAQAIAAAAAAAP///yH5BAAAAAAALAAAAAAQABAAAAIvjI9pwIztAjjTzYWr1FrS923NAymYSV3borJW26KdaHnr6UUxd4fqL0qNbD2UqQAAOw==) no-repeat 2px 50%;
    }
</style>
<?php
      endif;
      if(defined('F21_THUMB_PLUGIN') && is_bool(F21_THUMB_PLUGIN) && F21_THUMB_PLUGIN == !false):
?>
<style type="text/css">
    td.actions .button_thumbnail{
      margin-right: 0;
  		margin-left: 5px;
  		background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAAgQAAAIEBHRF40wAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAAGRSURBVDiNpZI7SxxRGIafM1dX3fUGapQgCRHstLKwE7W0t/c/GPITTG1pIWhIE1IkqQIBbUTwEpJ4gV0UR1F31F3xuuPM2TkpNtmJ7Jgp/LqP9z3PdzmfmHzzdoonhCFg+kkAgJ7uTsZHhh41NapdMuVtPP05RdEPCD5/W8Y5ylcApmnQ0pSOfWwHezTn5wl9SVpfJd3hcZUawzQNALSkFu37bUJfAqDKIdbdrwd6IsC3XyFMHQChCXy794FuJAE8sw+tc4LUzTqB/ZLrhtFkgFQhUoXUaRX5zhoknx4gY1o13tgRPhazfChmq/nSyQEzW2uoGG9NB1nvgq+XDgCDjc9oUTbvdjcpScniscNwV8/jgFIYMHv6A/Wn1tzZJg0FnZKs/ML7vS3629pptVPxIyyc71CQXjV3g1tyXEQFpGQu9zN+Bxu3Lis3xzUz6hkNYYlq/v3cZfUs8hkAJRXwqZCjTa+rCleBT1kpQFDfbJG6jCBf9nO8kEEEOHJcdMfl32OOP+woDv92oOB1gve/8Rs7E46Hy4meGQAAAABJRU5ErkJggg==) no-repeat 2px 50%;
    }
</style>
<?php endif; ?>
<style type="text/css">
  	td.actions .button_previewcode {
  		margin-right: 0;
  		margin-left: 5px;
  		background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAA30lEQVQ4T5VTvRrCIBBLXN10cvYZfQuf0dlJJ1fP7/4oUPpjh3JAG5JcIPyRGGNgvfQGcGr3p5l+mQBZl10CEsiLIP4TIRDMAIJG0nkBOPdMVhkAFFdXJM1ANgBm5ylgw9QmoXUkoUdIOrVPlQekeuGWMPBEcABuX+A+8uo/BnbAQELl9qjdVUaKZUVuFOa2WZ0vXUkxHWrjQw+waVqfm/CAorZN+c2ykGqA666t58DacbwAn2ci9C3fESR3wxJJzybIh4hcI0OTV96lXO4vKGFCFYZUvQayJ30LN9k79wPrOV8R4y7I7QAAAABJRU5ErkJggg==) no-repeat 2px 50%;
  	}
</style>
<?php
    endif;
  endforeach;
}



function url_validate($url){
  $reg = '%^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@|\d{1,3}(?:\.\d{1,3}){3}|(?:(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)(?:\.(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)*(?:\.[a-z\x{00a1}-\x{ffff}]{2,6}))(?::\d+)?(?:[^\s]*)?$%iu';
  if(is_string($url) && strlen($url = trim($url)) > 0 && preg_match($reg, $url) !== false){
    return true;
  }
  return false;
}
