<br>
<table cellspacing="1" cellpadding="4" border="0" bgcolor="#c7c7c7">
<tr bgcolor="#ffffff">
  <td valign="top">
  
   <table cellspacing="1" cellpadding="0" border="0">
     <tr height="33" bgcolor="#f3b900">
       <td><span class="white">&nbsp;&nbsp;<b><?=$this->lang->line('license_'.$image['rights']);?></b></span></td>
     </tr>

     <tr><td><img src="<?=$preview?>"></td></tr>
     
     <tr><td height="40">
       <b><?=$image['title']?></b><br>
       <?=$image['description']?>
     </td></tr>
   </table>
    
  </td>
  <td valign="top">
  
    <table cellspacing="0" cellpadding="2" border="0" width="250">
      <tr height="30" bgcolor="#949494">
        <td width="5"></td>
        <td width="110"><span class="white"><b><?=$this->lang->line('image_details');?></b></span></td>
        <td width="110"></td>
        <td width="5"></td>
      </tr>
      
      <tr height="25">
       <td></td>
       <td><?=$this->lang->line('code');?></td>
       <td align="right"><?=$image['code']?></td>
       <td></td>
      </tr>
      
      <tr><td colspan="4"><img src="data/img/delim.gif" border="0" width="250" height="3"></td></tr>
      
      <tr height="20">
       <td></td>
       <td><?=$this->lang->line('color');?></td>
       <td align="right"><?=$image['color']?></td>
       <td></td>
      </tr>
      
      <tr><td colspan="4"><img src="data/img/delim.gif" border="0" width="250" height="3"></td></tr>
      
      <tr height="20">
       <td></td>
       <td><?=$this->lang->line('size');?></td>
       <td align="right"> <?=$image['width'].' x '.$image['height']?></td>
       <td></td>
      </tr>
      
      <tr><td colspan="4"><img src="data/img/delim.gif" border="0" width="250" height="3"></td></tr>
      
      <?if($image['license']==1):?>
      <tr height="20">
       <td></td>
       <td><?=$this->lang->line('price');?></td>
       <td align="right"><?=$image['price']?> <?=$currency?></td>
       <td></td>
      </tr>
      
      <tr><td colspan="4"><img src="data/img/delim.gif" border="0" width="250" height="3"></td></tr> 
      <?endif;?>
      
      <tr height="20">
       <td></td>
       <td><?=$this->lang->line('owner');?></td>
       <td align="right"> <a href="<?=$lang.'/editors/profile/'.$image['owner']['id'].'.html'?>"><?=$image['owner']['login']?></a></td>
       <td></td>
      </tr>
      
      <tr><td colspan="4"><img src="data/img/delim.gif" border="0" width="250" height="3"></td></tr>
      
      <tr height="20">
       <td></td>
       <td><b><?=$this->lang->line('keys');?></td>
       <td></td>
       <td></td>
      </tr>
      
      <tr>
       <td></td>
       <td colspan="2"><?=$image['keywords']?></td>
       <td></td>
      </tr>
    
    </table>
    
  </td>
</tr>
<tr bgcolor="#ffffff" height="30">
  <td align="center">
    <a href="">disk compilation</a> |
    <a href="<?=$continue?>">back to search results</a>
  </td>
  <td class="padleft">
    <a href="<?=$lang.'bin/add/1/'.$image['id'].'.html'?>">add to bin</a><?if(count($image['res'])):?> | <a href="<?=$lang.'cart/add/1/'.$image['id'].'.html'?>">add to cart</a><?endif;?>
  </td>
</tr>
</table>

<br><br>

