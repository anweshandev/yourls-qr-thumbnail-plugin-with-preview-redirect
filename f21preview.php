<?php
  function formula21_preview($keyword, $preview = false){
    require_once( YOURLS_INC.'/functions-html.php' );

  	yourls_html_head( 'preview', yourls__('Preview short URL', 'formula21_translation') );
  	yourls_html_logo();
    $title		= yourls_get_keyword_title( $keyword );
  	$base		= YOURLS_SITE;
  	$shorturl	= "$base/$keyword";
  	$longurl	= yourls_get_keyword_longurl( $keyword );
  	$char  		= F21_PREVIEW_CHAR;
    $qrcode 	= YOURLS_SITE.'/'.$keyword.'.qr';
    $thumb		= YOURLS_SITE.'/'.$keyword.'.i';
    $secs = false;
    $utm = false;
    if($preview){
        if(!$secs && isset($_GET['utm_redirect']) && isset($_GET['utm_preview']) && $_GET['utm_redirect'] == 'auto' && $_GET['utm_preview'] == '1'){
            $secs = 1;
            $utm = true;
        }
        if(!$utm && defined('F21_PREVIEW_TIME') && is_int(F21_PREVIEW_TIME) &&
        F21_PREVIEW_TIME > 0 && F21_PREVIEW_TIME < 61){
            $secs = F21_PREVIEW_TIME + intval(F21_PREVIEW_TIME / 2);
            session_start();
            $_SESSION['refresh_secs_url'] = time();
            if(!isset($_SESSION['refresh_secs_url_end']) || $_SESSION['refresh_secs_url_end'] < $_SESSION['refresh_secs_url']){
            $_SESSION['refresh_secs_url_end'] = time()+$secs;
            }
            $secs = $_SESSION['refresh_secs_url_end'] - $_SESSION['refresh_secs_url'];
            $secs = $secs + 2;
            header( "refresh:".$secs.";url=$longurl");
        }
    }

    if($utm){
        header("location: $longurl");
    }
?>
  <style>
	.halves {
		display: flex;
		display: -webkit-flex;
		display: -moz-flex;
		justify-content: space-between;
		-webkit-justify-content: space-between;
		-moz-justify-content: space-between;
		align-items: flex-start;
		-webkit-align-items: flex-start;
		-moz-align-items: flex-start;
	}
	.half-width {

	}
	.desc-box {
		line-height: 1.6em;
		width: 65%;
	}
	.thumb-box {
		margin-right: 10px;
	}
	.short-thumb {
		width: 326px;
		height: 245px;
		border: 5px solid #151720;
	}
	.short-qr {
		border: 1px solid #ccc;
		width: 100px;
		margin-top: 3px;
	}
	hr {
		margin: 10px 0;
		border: 0;
		border-top: 1px solid #eee;
		border-bottom: 1px solid #fff;
		display: block;
		clear: both;
	}
	.disclaimer {
		color: black;
	}
	/* Mobile */
	@media screen and (max-width: 720px) {
		.halves {
			display: block;
		}
		.half-width {
			width: 100%;
		}
		.thumb-box {
			margin: 0;
		}
		.desc-box {

		}
	}
  h1{
    font-family: inherit !important;
    all: revert;
    margin: unset;
    font-weight: 500;
  }
	</style>
	<h1><?php yourls_e('Short URL ~ Preview', 'formula21_translation'); ?></h1>
  <hr/>
	<div class="halves">
		<div class="half-width thumb-box">
			<?php
				$x = explode(DIRECTORY_SEPARATOR, __DIR__);
				$y = array_search('user', $x);
				$x = array_merge([YOURLS_SITE], array_splice($x, $y), ['image','No-Image-Placeholder.svg']);
				$x = implode('/', $x);
        $onerror = $x;
      ?>
			<img class="short-thumb" src="<?php echo yourls_esc_url( $thumb ); ?>" onerror="this.src='<?php echo yourls_esc_url( $onerror );?>'">
		</div>
		<div class="half-width desc-box">
			<div>
				<?php yourls_e($preview==false?'You requested a shortened URL':'The preview for the shortened URL ', 'formula21_translation'); ?> <strong><?php echo yourls_esc_url( $shorturl ); ?></strong>:
			</div>
			<div>
				<?php yourls_e('The URL points to', 'formula21_translation'); ?> : <strong><a class="loc-replace" href="<?php echo yourls_esc_url( $preview==false?$shorturl:$longurl ); ?>"><?php  echo yourls_esc_url( $longurl ); ?></a></strong>
			</div>
			<div>
				<?php yourls_e('The page has a title', 'formula21_translation'); ?>: <strong><?php echo $title; ?></strong>
			</div>
			<div>
				<?php yourls_e('The QR code', 'formula21_translation'); ?>:
				<div>
					<img class="short-qr" src="<?php echo yourls_esc_url( $qrcode ); ?>" onerror="this.src='<?php echo yourls_esc_url( $onerror );?>'">
				</div>
			</div>
		</div>
	</div>
<?php
	if($preview){
?>
<hr>
	<div class="disclaimer">
		<?php if($secs){
      yourls_e('This is a <strong>preview page</strong>. You will be auto-redirected to another page within <strong>'.$secs.'</strong> second(s).', 'formula21_translation');
      yourls_e('If you do not want to spend time here so long, please click on the long link to redirect, please click', 'formula21_translation');
    ?>
    <strong>
			<a class="loc-replace" href="<?php echo yourls_esc_url($longurl ); ?>"><?php yourls_e('here', 'formula21_translation'); ?></a>
		</strong>.
    <?php
      }else {
      yourls_e('This is a <strong>preview page</strong>.', 'formula21_translation');
      yourls_e('If you want to go to the destination, please click', 'formula21_translation');
    ?>
    <strong>
      <a class="loc-replace" href="<?php echo yourls_esc_url($longurl ); ?>"><?php yourls_e('here', 'formula21_translation');?></a>
    </strong>.
    <?php
      }
      ?>
	</div>
<?php }else{
?>
  <hr/>
  <div class="disclaimer">
      <?php
        yourls_e('This is a <strong>preview page</strong>.', 'formula21_translation');
        yourls_e('If you want to go to the destination, please click', 'formula21_translation');
      ?>
      <strong>
        <a class="loc-replace" href="<?php echo yourls_esc_url($longurl ); ?>"><?php yourls_e('here', 'formula21_translation');?></a>
      </strong>.
	</div>
<?php
} ?>
<script>
	let c = document.querySelectorAll(".loc-replace");
	c.forEach(function(a, b){
		a.addEventListener('click', function(e){
			e.preventDefault();
			window.location.replace(this.href);
		});
	});
<?php if($preview && $secs): ?>
  	setTimeout(function(){
  		window.location.replace(<?php echo '\''.yourls_esc_url($longurl).'\''; ?>);
  	}, <?php echo $secs*1000; ?>);
<?php endif; ?>
</script>
<?php
	yourls_html_footer();
  exit();
}
