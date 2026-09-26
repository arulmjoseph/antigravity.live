<?php
$_SERVER['HTTP_HOST'] = 'sparshayurvedic.com';
$_SERVER['REQUEST_URI'] = '/sitemap_index.xml';
$_SERVER['REQUEST_METHOD'] = 'GET';
wp();
echo "sitemap: " . var_export(get_query_var("sitemap"), true) . "\n";
echo "sitemap_n: " . var_export(get_query_var("sitemap_n"), true) . "\n";
