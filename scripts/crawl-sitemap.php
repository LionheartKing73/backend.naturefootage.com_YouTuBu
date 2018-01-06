<?php
$DEBUG = false;
if (php_sapi_name() == "cli") {
    $KKK_PHP_EOL = PHP_EOL;
} else {
    $KKK_PHP_EOL = "<br/>";
}
//Get sitemap and parse url
$Sitemap = file_get_contents("//video.naturefootage.com/sitemaps/sitemap-links.xml");
$SitemapDom = new DOMDocument();
$SitemapDom->preserveWhiteSpace = false;
$SitemapDom->loadXML($Sitemap);
$URLDOMList = $SitemapDom->getElementsByTagName('loc');
foreach ($URLDOMList as $URLDOM) {
    $urls[] = $URLDOM->nodeValue;
}

//Break url into batch 10
$urls = array_chunk($urls, 10);
if ($DEBUG) {
    echo "<pre>";
    print_r($urls);
    echo "</pre>";
}

//Processing batch
$BatchNo = 1;
$TotalBatch = count($urls);
foreach ($urls as $URLBatch) {
    $master = curl_multi_init();
    $curl_arr = array();
    $i = 0;
    foreach ($URLBatch as $URL) {
        $curl_arr[$i] = curl_init($URL);
        curl_setopt($curl_arr[$i], CURLOPT_RETURNTRANSFER, true);
        curl_multi_add_handle($master, $curl_arr[$i]);
        echo "Processing: " . $URL . $KKK_PHP_EOL;
        $i++;
    }

    do {
        curl_multi_exec($master, $running);
    } while ($running > 0);

    if ($DEBUG) {
        for ($k = 0; $k < $i; $i++) {
            $results[] = curl_multi_getcontent($curl_arr[$i]);
        }
        print_r($results);
        exit;
    }
    echo $BatchNo . "/" . $TotalBatch . $KKK_PHP_EOL;
    $BatchNo++;
}
?>