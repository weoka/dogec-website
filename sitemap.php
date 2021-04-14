<?php
include('inc_functions.php');
include('inc_doctype.php');

$cfgNavClass = 'alt light';
?>
<title>Site Map</title>
<meta name="description" content="" />
<?php include('inc_head.php'); ?>
<link rel="stylesheet" type="text/css" href="minify/assets/css/team.css">
<?php include('inc_header.php'); ?>
<div id="section01" class="black-pattern-bg">
	<div class="container">
		<div class="row pb-5">
			<div class="col-12 col-md-8  text-center text-md-left"  data-aos="fade-right">
				<h1 class="text-orange">SITE MAP</h1>
				<div class="text-white">
					<?php echo printSiteMap('.', false); ?>
				</div>
			</div>
			<div class="col-12 col-md-4">&nbsp;</div>
		</div>
	</div>
</div>
<?php include('inc_footer.php'); ?>
<?php include('inc_foot.php'); ?>