<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title><?=$meta['title']?>Cart Information: Timeframe</title>
    <link href="/data/css/style.css" type="text/css" rel="stylesheet">
    <link href="/data/css/cart.css" type="text/css" rel="stylesheet">
<?if ($refresh) {?>
    <script type="text/javascript">
      window.parent.location.href = "/cart.html";
    </script>
<?}?>
  </head>
  <body id="cartFrame">
    <a href="/bin.html" target="_top"><img src="/data/img/clip_bin.jpg"
        alt="" align="absmiddle"> Clip Bin <?=$bin_count?></a>
    <img src="/data/img/menu_delimiter.jpg" alt="" align="absmiddle">
    <a href="/cart.html" target="_top"><img src="/data/img/basket.jpg"
      alt="" align="absmiddle"> Basket <?=$cart_count?></a>    
  </body>
</html>