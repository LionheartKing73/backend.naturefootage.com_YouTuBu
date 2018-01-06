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

<?if($results):?>

<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr><td height="25">

<?if($cats):?><?=$this->lang->line('search_for')?> <?=$this->lang->line('category')?> <b><?=$cats?></b> <?=$this->lang->line('search_returned')?> <b><?=$all?></b> <?=$this->lang->line('search_res')?> (<a href="<?=$lang.'/cats'?>.html"><?=$this->lang->line('choose_other')?></a>)<br><?endif;?>
<?if($phrase):?><?=$this->lang->line('search_for')?> <b><?=$phrase?></b> <?=$this->lang->line('search_returned')?> <b><?=$all?></b> <?=$this->lang->line('search_res')?><br><?endif;?>

</td></tr>
<tr><td height="1" bgcolor="#eeeeee"></td></tr>
<tr><td>

<form action="<?=$uri?>" method="post" name="perpage_form">     
<table cellpadding="0" cellspacing="0" border="0" width="100%">

  <tr><td>
  
    <table cellpadding="4" cellspacing="0" border="0" width="100%">
    <tr>
      <td><?=$page_navigation?></td>
      <td width="150" align="right"> <?=$this->lang->line('results_perpage')?>:
        <select name="perpage" onchange="perpage_form.submit()">
           <option value="1" <?if($perpage==1) echo "selected"?>>1</option>
           <option value="2" <?if($perpage==2) echo "selected"?>>2</option>
           <option value="5" <?if($perpage==5) echo "selected"?>>5</option>
        </select>
      </td>
    </table>
    
  </td></tr>
  
<tr><td height="1" bgcolor="#eeeeee"></td></tr>  
<tr><td height="10"></td></tr>    
<tr><td>
  <?=$results?>
</td></tr>
</table>
</form>


</td></tr>
</table>


<?else:?>
  <br>There are no results.<br>     
  We are is in the process of uploading its entire archive...
  <a href="mailto:demo@big-easy-footage-library-software.co.uk">demo@big-easy-footage-library-software.co.uk</a>

<?endif;?>
