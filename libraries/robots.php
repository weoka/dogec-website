<?php
/* Robots.txt */
header('Content-Type: text/plain');
echo 'User-agent: *' . "\r\n";
echo 'Disallow: /admin/' . "\r\n";
echo 'Disallow: /backups/' . "\r\n";
echo 'Disallow: /inc_*' . "\r\n";
echo 'Allow: /' . "\r\n";
echo 'Sitemap: http://' . $_SERVER['HTTP_HOST'] . '/sitemap.xml';
?>