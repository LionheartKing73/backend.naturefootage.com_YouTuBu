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

<table cellpadding="4" cellspacing="1" border="0" bgcolor="#ffffff">
<tr><td valign="top" width="180" bgcolor="#efefef">
<?if($cats):?>
<?foreach($cats as $cat):$num++;?>
  <table cellpadding="4" cellspacing="1" border="0" bgcolor="#efefef"> 
    <tr><td><?=$this->lang->line('categ')?>: <a href="<?=$lang.'/search/cat/'.$cat['id'].'.html'?>"><?=$cat['title']?></a></td></tr>
    <tr><td><a href="<?=$lang.'/search/cat/'.$cat['id'].'.html'?>"><img src="<?=$cat['thumb']?>" border="0"></a></td></tr>
    
    <?if($cat['child']):?>
    <tr>
      <td>
         <?=$this->lang->line('subcategories')?>:<br>
         <?foreach($cat['child'] as $subcat):?>
          <a href="<?=$lang.'/search/cat/'.$cat['id'].'-'.$subcat['id'].'.html'?>"><?=$subcat['title']?></a><br>
         <?endforeach;?>
        
      </td>
    </tr>
    <?endif;?>
    
  </table>
  <?if($num != $count):?> 
    <?if(!($num%3)):?> 
      </td></tr><tr><td valign="top" width="180" bgcolor="#efefef"> 
    <?else:?>
      </td><td valign="top" width="180" bgcolor="#efefef"> 
    <?endif;?>
  <?endif;?>
      
<?endforeach;?>
<?endif;?>    
</td></tr>    
</table>
