<?php require 'application/system/YSSEnvironment.php'; ?>
<!DOCTYPE HTML>
<? //<html manifest="peeq.cache">?>
<html>
<head>
	<meta charset="utf-8">
	<title>peeq | Contact</title>
	<meta name="description" content="about peeq">
	<meta name="author" content="peeq">
	<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0;">
	<link rel="shortcut icon" href="" />
	<link rel="apple-touch-icon" href="" />
	<link rel="stylesheet" href="resources/css/contact.css?v=1">
</head>
<body>
	<div id="bg">
		<img src="resources/imgs/bg-views.png" alt="" />
		<img id="bg-default" src="resources/imgs/bg-default.png" alt="" />
	</div>
	<div id="container">
		<header>
			<a class="peeq" href="/"><img src="resources/imgs/peeq.png" alt="peeq" /></a>
			<section>
				<nav>
					<ul>
						<li><a href="/tour">Tour</a></li>
						<li><a href="/buzz">Buzz</a></li>
						<li><a href="/sign-up">Sign up</a></li>
						<li><a href="/login">Login</a></li>
					</ul>
				</nav> 
			</section>
		</header>
		<article id="main">
			<?php include('application/templates/contact.php');?>
		</article>
	</div>
	<?php include('application/templates/footer.php');?>
	
	<?php include('application/templates/tracking.php');?>
	
	<?if("release" == YSSConfiguration::applicationConfiguration()):?>
	<script src="resources/js/contact.min.js"></script>
	<?else:?>
	<script src="resources/js/src/jquery/jquery.js"></script>
	<script src="resources/js/src/jquery/plugins/jquery.toggle_form_field.js"></script>
	<script src="resources/js/src/jquery/plugins/jquery.validation.js"></script>
	<script src="resources/js/src/jquery/plugins/jquery.lastfieldentersubmit.js"></script>
	<script src="resources/js/src/peeq/peeq.contact.js"></script>
	<?endif;?>
</body>
</html>
