<div id="content">
<b>HELP</b><br><br>

<?if($topics): $i=0;$k=0;?>

<table cellpadding="10" cellspacing="1" border="0" class="form_table">

<?foreach($topics as $topic): $i++;?> 

<tr class="tdata1"><td>  
 <?=$i.'. '.$topic['title']?> 
  (
   <?if($topic['pdf']):?><a href="<?=$path.$topic['id'].'.'.$topic['pdf']?>">PDF document</a><?endif;?>
   <?if($topic['video']):?> &nbsp; <a href="<?=$path.$topic['id'].'.'.$topic['video']?>">Video file</a><?endif;?> 
  ) 
  
  <br>
 <?if($topic['annotation']):?><?=$topic['annotation']?><br><?endif;?>
 
 </td></tr>
 
 <?if($topic['child']):?>
 
   <?foreach($topic['child'] as $item): $k++?>
   <tr><td class="tdata2"> 
      &nbsp;&nbsp;&nbsp;&nbsp;<a href=""><?=$i.'.'.$k.'. '.$item['title']?></a>
      
      (
       <?if($item['pdf']):?><a href="<?=$path.$item['id'].'.'.$item['pdf']?>">PDF document</a><?endif;?>
       <?if($item['video']):?> &nbsp; <a href="<?=$path.$item['id'].'.'.$item['video']?>">Video file</a><?endif;?> 
      ) 
      
      <br>
      <?if($item['annotation']):?>&nbsp;&nbsp;&nbsp;&nbsp;<?=$item['annotation']?><br><?endif;?>   
      
   </td></tr>   
   <?endforeach;?>
   
 <?endif;?>
<?endforeach;?>

 <?endif;?>  
</div>