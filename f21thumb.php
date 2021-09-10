<?php
require_once 'vendor/autoload.php';
use GuzzleHttp\Client;


if(!defined('F21_THUMB_URL'))
    define( 'F21_THUMB_URL', 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?screenshot=true&url=%s' );



function formula21_thumb($request, $pattern = null){
    $thumb_url = F21_THUMB_URL;
    if(!$thumb_url || !is_string($thumb_url) || strlen($thumb_url) == 0){
        $thumb_url = "https://www.googleapis.com/pagespeedonline/v5/runPagespeed?screenshot=true&url=%s";
    }

    $longurl = '';
    $keyword = '';
    $api_key = (defined('F21_API_KEY') && is_string(F21_API_KEY) && strlen(F21_API_KEY) > 0)?F21_API_KEY:false;
    $append = [];

    if($api_key){
        $thumb_url .='&key=%s';
        $append = ['', $api_key];
    }

    if(!$pattern){
        $pattern = yourls_make_regexp_pattern( yourls_get_shorturl_charset() );
    }

    if( preg_match( "@^([$pattern]+)\.i?/?$@", $request[0], $matches ) ) {
        // this shorturl exists ?
    	$keyword = yourls_sanitize_keyword( $matches[1] );
    	if( yourls_is_shorturl( $keyword ) ) {
      	    $longurl = yourls_get_keyword_longurl( $keyword );
            $append[0] = urlencode($longurl);
            // var_dump($append);
            $thumb_url = sprintf($thumb_url, ...$append);
        }
        // Call your function here...
        formula21_thumb_guzzle($request, $longurl, $keyword, $thumb_url, $ref = (defined('F21_API_REF') && is_bool(F21_API_REF) && F21_API_REF == true)?YOURLS_SITE:null);
    }
}


function formula21_thumb_guzzle($request, $longurl, $keyword, $thumb_url, $ref = null){

    $method = 'GET';
    // The query to be parsed from the url.
    $query = parse_url($thumb_url)['query'];
    // This is the base uri
    $uri = stristr($thumb_url, '?'.$query, true);
    // Changing query to an array
    parse_str($query, $query);

    $headers = [
        'User-Agent' => $_SERVER['HTTP_USER_AGENT'],
    ];

    if($ref){
        $headers['Referer'] = $ref;
    }

    $client = new Client();
    $response = $client->request($method, $thumb_url, [
        'query' => $query,
        'headers'=> $headers
    ]);

    if($response->getStatusCode() == 200){
        $body =  $response->getBody();
        $screen_shot = json_decode($body, true);
        $screen_shot = $screen_shot['lighthouseResult']['audits']['full-page-screenshot']['details']['screenshot']['data'];
        formula21_show_image($screen_shot);
    }

    formula21_no_image();

    exit;
}



function formula21_thumb_file($request, $longurl, $keyword, $thumb_url){
  // try{
        $screen_shot_json_data = file_get_contents($thumb_url);
        if($screen_shot_json_data){
                $screen_shot_result = json_decode($screen_shot_json_data, true);
                $screen_shot = $screen_shot_result['lighthouseResult']['audits']['final-screenshot']['details']['data'];
                formula21_show_image($screen_shot);
        }else{
            formula21_no_image();
        }
   /*}catch(Exception $e){
        formula21_no_image();
   }*/
}

function formula21_show_image($screen_shot){
    $screen_shot = explode(",", $screen_shot)[1];
    $code_binary = base64_decode($screen_shot);
    $image= imagecreatefromstring($code_binary);
    ob_start();
    imagejpeg($image);
    $contents = ob_get_contents();
    $length = ob_get_length();
    ob_end_clean();
    imagedestroy($image);
    header("Content-Length: $length");
    header("Cache-Control: max-age=172800, public, private", true);
    header('Content-Type: image/jpeg');
    http_response_code(200);
    echo $contents;
    exit;
}


function formula21_no_image(){
    $contents = file_get_contents(__DIR__.'/image/No-Image-Placeholder.svg');
    header('Content-Type: image/svg+xml');
    header('Content-Length: '.strlen($contents));
    http_response_code(200);
    echo $contents;
    exit;
}
