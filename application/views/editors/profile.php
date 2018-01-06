<table cellspacing="0" cellpadding="1" border="0" width="100%">        
  <tr><td valign="top">
    <? if($profile['avatar']):?>
    <img src="<?=$this->config->item('avatar_path').$profile['avatar']?>" border="1" width="25"><br>
    <? endif;?>
    <?=$this->lang->line('username');?>: <?=$profile['login'];?><br>
    <?=$this->lang->line('name_surname');?> : <?=$profile['fname'];?> <?=$profile['lname'];?><br>
    <?=$this->lang->line('country');?>: <?=$profile['city'];?> <? echo ($profile['country'] && $profile['city']) ? ', ' : '';?><?=$profile['country'];?><br>
    <?=$this->lang->line('since');?>: <?=$profile['since'];?><br>
    <?=$this->lang->line('portfolio');?>: <? echo $content_count['images'] ? $content_count['images'] : 0;?> images, <? echo $content_count['clips'] ? $content_count['clips'] : 0;?> clips<br>
  </td>
  <td width="100" valign="top">
    <div class="avatar">
      <img src="<?=$this->config->item('avatar_path').$profile['avatar']?>" border="0">
    </div>
  </td>
  </tr> 
</table>
<br>
<table cellspacing="0" cellpadding="1" border="0">        
  <tr><td>
    <a href="<?=$lang?>/editors/profile/<?=$id?>"><?=$this->lang->line('popular_content');?></a> |
    <a href="<?=$lang?>/editors/portfolio/<?=$id?>"><?=$this->lang->line('portfolio');?></a> |
    <a href="<?=$lang?>/editors/cats/<?=$id?>"><?=$this->lang->line('categories');?></a> | 
    <a href="<?=$lang?>/editors/testimonials/<?=$id?>"><?=$this->lang->line('testimonials');?></a>
  </td></tr>
</table>
<br>

<? if($results): ?>
<table cellspacing="0" cellpadding="5" border="0">
<tr><td valign="top">
  <?=$results?>
</td>
</tr>
</table>
<? endif;?>

<link type="text/css" rel="stylesheet" href="data/css/canvasGallery.css">
<script src="data/js/prototype/prototype.js" type="text/javascript"></script>
<script>var count = <?=$im['all'];?>; </script>
<script src="data/js/canvasGallery.js" type="text/javascript"></script>

<div class="gallery">
  <a id="ml" onclick="return false;"><img src="data/img/arrow_left.png" border="0"></a>
  <a id="md" onclick="return false;"><img src="data/img/arrow_right.png" border="0"></a>
  
  <div id="imgs">

  	<div id="img-holder" width="600">
  	
  	<? foreach ($im['results'] as $key => $line): ?>
  	
  			<div class="gallery-item">
  				<a href="<?=$line['url'];?>"><img src="<? echo $line['type'] == 2 ? $line['thumb']['img'] : $line['thumb'];?>" id="img00<?=$key+1;?>"/></a>
  				<canvas  width="102" height="50" id="canvas00<?=$key+1;?>"/>
  			</div>
  			
  	<? endforeach;?>		
    </div>
  </div>	
</div>
