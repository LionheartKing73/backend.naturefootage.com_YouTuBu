<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <title><?=$meta['title']?> :: $this->config->item('title')</title>
  <meta name="description" content="<?=$meta['desc']?>">
  <meta name="keywords" content="<?=$meta['keys']?>">

  <base href="<?=$this->config->config['base_url']?>">
  <link href="data/css/style.css?<?=$this->config->item('release_date')?>" type="text/css" rel="stylesheet">
<?
if($add_css):
  if(is_array($add_css)):
    foreach($add_css as $css):
?>
  <link href="<?=$css?>" type="text/css" rel="stylesheet">
<?
    endforeach;
  else:
?>
  <link href="<?=$add_css?>" type="text/css" rel="stylesheet">
<?
  endif;
endif;
?>
  <script src="data/js/script.js?201003181325" type="text/javascript"></script>
<?
if($add_js):
  if(is_array($add_js)):
    foreach($add_js as $js):
?>
  <script src="<?=$js?>" type="text/javascript"></script>
<?
    endforeach;
  else:
?>
  <script src="<?=$add_js?>" type="text/javascript"></script>
<?
  endif;
endif;
?>
</head>

<body>
  <?=$content?>
</body>

</html>