<!DOCTYPE html>
<html lang="en" class="no-js">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<title><?=meta('title')?></title>
		<meta name="keywords" content="<?=meta('keywords')?>" />
		<meta name="description" content="<?=meta('description')?>" />
		<meta name="viewport" content="width=device-width; initial-scale=1.0;">
		<link rel="apple-touch-icon" href="/apple-touch-icon.png">
		<link rel="shortcut icon" href="/favicon.ico">
		<?=canonical()?>

		<? register_css("style-reset", "style/reset.css", "all", 1); ?>
		<? register_css("style", "style/style.css", "all", 2); ?>
		<? // optional handheld stylesheet // register_css("style-handheld", "style/handheld.css", "all", 3); ?>
		<? // optional ie-only stylesheet // register_css("style-ie", "style/ie.css", "all", 6, "ie"); ?>
		<? // optional ie6 and below stylesheet // register_css("style-ie6", "style/ie6.css", "all", 7, "lt ie 7"); ?>
		
		<? register_javascript("jquery", "http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js", 1, false, false); ?>
		<? // optional jquery-ui // register_javascript("jquery-ui", "http://ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js", 2, false, false); ?>
		<? // optional jquery-fancybox // register_javascript("fancybox", "http://api.devcsd.com/js/jquery/jquery.fancybox.pack.js", 2, false, false); ?>
		<? // optional modernizer // register_javascript("modernizer", "javascript/modernizr-1.5.min.js", 8, false, false); ?>
		<? register_javascript("onload", "javascript/onload.js", 9999, false, false); ?>
		
		<? head_hook(); ?>
	</head>
	<? $body_id = (meta('body_id') <> "") ? meta('body_id') : str_replace("/", "-",  segments_full()); $body_class = (meta('body_class') <> "") ? meta('body_class') : str_replace("/", "-",  segments_page()); ?>
	<body id="body-<?=$body_id?>" class="body-<?=$body_class?>">
		<div id="container">
			<header>
				<?=layout_section("header")?>
			</header>
			<div id="main">
				<div id="content">
					<?=layout_section("content")?>
				</div>
				<div id="sidebar">
					<?=layout_section("sidebar")?>
				</div>
			</div>
			<footer>
				<?=layout_section("footer")?>
			</footer>
		</div>
		<? foot_hook(); ?>
	</body>
</html>