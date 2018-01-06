<?if($results):?>
<?foreach($results as $item):?>

<div class="floated">   
<table cellpadding="4" cellspacing="1" border="0" class="results">
<tr><td bgcolor="#ffffff">

  <table cellpadding="0" cellspacing="0" border="0" width="170">
    <tr height="20"><td class="header_<?=($item['type']==2) ? 'clip' : 'image'?>"><?=$item['rights']?></td></tr>
    <tr><td align="center" class="obj" height="100">
    
    <?if($item['type']==2):?>
       <object classid="clsid:D27CDB6E-aE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0"  width="120" height="67">
         <param name="movie" value="data/swf/container.swf?ct=201004281450">
         <param name="quality" value="high">
         <param name="bgcolor" value="#ffffff">
         <param name="FlashVars" value="pict=<?=$item['thumb']['img']?>&muv=<?=$item['thumb']['swf']?>&url1=<?=$item['url']?>">
         <embed src="data/swf/container.swf?ct=201004281450" FlashVars="pict=<?=$item['thumb']['img']?>&muv=<?=$item['thumb']['swf']?>&url1=<?=$item['url']?>" quality="high" bgcolor="#ffffff" width="120" height="67" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash"></embed>
       </object>
    <?else:?>
        <a href="<?=$item['url']?>"><img src="<?=$item['thumb']?>" border="0"></a></td></tr>
    <?endif;?>
    
    
    <tr height="18" class="pad"><td align="center" valign="middle"><?=$this->lang->line('code');?>: <?=$item['code']?></td></tr>
  </table>
  
</td></tr>

<?if($item['keys']):?>
<tr><td height="25" bgcolor="#ffffff" align="center">
<?=$item['keys']?>
</td></tr>
<?endif;?>

<tr><td bgcolor="#ffffff" align="center">

<?if($checks):?>
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
 <td width="20">
  <input type="checkbox" name="id[]" value="<?=$item['type']?>-<?=$item['id']?>">
 </td>
 <td align="center">
   <a href="<?=$item['url']?>"><?=$this->lang->line('info');?></a>
   <?if(count($item['res'])):?> | <a href="<?=$lang.'/cart/add/'.$item['type'].'/'.$item['id']?>.html"><?=$this->lang->line('to_cart');?></a><?endif;?>
 </td>
</tr>
</table>

<?else:?>

<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td width="5"></td>
<td align="left"> 
 <a href="<?=$item['url']?>" class="more"><?=$this->lang->line('info');?></a>
</td>
<td align="right">

 <!--<a href="{$clip.disk}"><img src="data/img/cd.gif" width="13" height="13" align="absmiddle" border="0" alt="dvd compilation"></a> --> 
 
 <?if(count($item['res'])):?><a href="<?=$lang.'/cart/add/'.$item['type'].'/'.$item['id']?>.html"><img src="data/img/cart.gif" align="absmiddle" border="0"></a><?endif;?>
 <a href="<?=$lang.'/bin/add/'.$item['type'].'/'.$item['id']?>.html"><img src="data/img/bin.gif" align="absmiddle" border="0"></a>
  </td>
 <td width="5"></td>
 </tr>
 </table>
 
<?endif;?>

</td></tr>
</table> 
</div>  
<?endforeach;?>
<?endif;?>