<base href="<?php echo getBase(); ?>" />

<link rel="canonical" href="<?php echo $strHost . $strUriQs; ?>">

<link rel="shortcut icon" type="image/png" href="/r2/16x16/images/favicon.png" />

<link rel="icon" type="image/png" href="/r2/16x16/images/favicon.png" sizes="16x16" />
<link rel="icon" type="image/png" href="/r2/32x32/images/favicon.png" sizes="32x32" />
<link rel="icon" type="image/png" href="/r2/192x192/images/favicon.png" sizes="192x192" />
<link rel="icon" type="image/png" href="/r2/512x512/images/favicon.png" sizes="512x512" />

<link rel="apple-touch-icon" href="/r2/180x180/images/favicon.png" sizes="180x180" />

<meta content="/r2/150x150/images/favicon.png" name="msapplication-square150x150logo" />
<meta content="#FFFFFF" name="msapplication-TileColor" />

<meta content="yes" name="mobile-web-app-capable" />
<meta content="yes" name="apple-mobile-web-app-capable" />

<meta content="" name="theme-color"><!-- #FFFFFF -->

<meta property="og:type" content="website" />
<meta property="og:url" content="<?php echo $strUrl; ?>" />
<meta property="og:image" content="<?php echo $strHost . '/r2/600x315/images/Logo.png'; ?>" />
<meta property="og:site_name" content="<?php echo $cfgCompany; ?>" />
<!-- <meta property="og:title" content="<?php echo $cfgCompany; ?>" /> -->

<meta name="twitter:card" content="summary">
<meta name="twitter:site" content="@"><!-- @username -->
<meta name="twitter:image" content="<?php echo $strHost . '/r2/240x240/images/favicon.png'; ?>">
<!-- <meta name="twitter:title" content="<?php echo $cfgCompany; ?>" /> -->
<!-- <meta name="twitter:description" content="<?php echo $cfgCompany; ?>" /> -->

<meta property="og:site_name" content="<?php echo $cfgCompany; ?>" />
<meta property="og:url" content="<?php echo $strUrl; ?>" />
<?php
if (!isset($strMetaImage)) {
	?>
	<meta property="og:type" content="website" />
	<meta property="og:image" content="<?php echo $strHost . '/r2/600x315/images/Logo.png'; ?>" />
	<meta property="og:image:type" content="image/png" />
	<meta property="og:image:width" content="600" />
	<meta property="og:image:height" content="315" />
	<!-- <meta property="og:title" content="<?php echo $cfgCompany; ?>" /> -->
	<?php
}
?>

<meta name="twitter:site" content=""><!-- @username -->
<?php
if (!isset($strMetaImage)) {
	?>
	<meta name="twitter:card" content="summary">
	<meta name="twitter:image" content="<?php echo $strHost . '/r2/240x240/images/favicon.png'; ?>">
	<!-- <meta name="twitter:title" content="<?php echo $cfgCompany; ?>" /> -->
	<!-- <meta name="twitter:description" content="<?php echo $cfgCompany; ?>" /> -->
	<?php
}
?>

<script type="application/ld+json">
{
	"@context": "http://schema.org",
	"@type":    "Organization",
	"name" :    "<?php echo $cfgCompany; ?>",
	"url":      "<?php echo $strHost; ?>",
	"logo":     "<?php echo $strHost . '/r2/600x600/images/Logo.png'; ?>",
	"address": {
		"@type":           "PostalAddress",
		"streetAddress":   "",
		"addressLocality": "",
		"addressRegion":   "",
		"postalCode":      "",
		"addressCountry":  "US"
	},
	"contactPoint": {
		"@type":       "ContactPoint",
		"contactType": "customer service",
		"telephone":   "+1-888-888-8888"
	},
	"sameAs": [
		"https://www.facebook.com/",
		"https://twitter.com/",
		"https://www.linkedin.com/company/",
		"https://plus.google.com/",
		"https://www.youtube.com/user/"
	]
}
</script>