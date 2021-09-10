<?php

function formula21_qr_code($request, $pattern = null){

  // --- START configurable variables ---

  // output file name, if false outputs to browser with required headers:
  $outfile = false;

  // error correction level (constants, don't use quotes):
  $level = QR_ECLEVEL_L; // QR_ECLEVEL_L, QR_ECLEVEL_M, QR_ECLEVEL_Q or QR_ECLEVEL_H

  // pixel size multiplier (3 = 3x3 pixels for QR):
  $size = 3;

  // outside margin in 'virtual' pixels:
  $margin = 4;

  // if true code is outputed to browser and saved to file,
  // otherwise only saved to file.
  // It is effective only if $outfile is specified.
  $saveandprint = false;

  // --- END configurable variables ---

  // Get authorized charset in keywords and make a regexp pattern
  if(!$pattern){
      $pattern = yourls_make_regexp_pattern( yourls_get_shorturl_charset() );
  }


  // if the shorturl is like bleh.qr...
  if( preg_match( "@^([$pattern]+)\.qr?/?$@", $request[0], $matches ) ) {

    // if this shorturl exists...
    $keyword = yourls_sanitize_keyword( $matches[1] );
    if( yourls_is_shorturl( $keyword ) ) {
      $url = yourls_link( $keyword );
      $yourls_url = yourls_site_url( false );

      // If Case-Insensitive plugin is enabled and YOURLS is not a sub-directory install...
      if( yourls_is_active_plugin( 'case-insensitive/plugin.php' )
        && ( 'http://'.$_SERVER['HTTP_HOST'] == $yourls_url
        ||  'https://'.$_SERVER['HTTP_HOST'] == $yourls_url ) ) {

        // Make the QR smaller
        // Alphanumeric URLs have less bits/char:
        // http://en.wikipedia.org/wiki/QR_code#Storage
        $url = strtoupper( $url );
      }

      // Show the QR code then!
      QRcode::png( $url, $outfile, $level, $size, $margin, $saveandprint );
      exit;
    }
   }
 }
