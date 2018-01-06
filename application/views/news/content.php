<?if($new):?>

<?if($visual_mode):?>
    <div class="boxout" onmouseover="show_bar('<?=$new['news_id']?>','news','<?=$lang?>')" onmouseout="hide_bar('<?=$new['news_id']?>','news','<?=$lang?>')" id="news<?=$new['news_id']?>">
        <div class="typography">
            <?=$new['body']?>
        </div>
    </div>
    <?else:?>
    <div class="typography">
        <?=$new['body']?>
    </div>
    <?endif;?>


<?elseif($news): ?>

    <div class="typography">

    <?foreach ($news as $new):?>

  <?if($visual_mode):?>
    <div class="boxout" onmouseover="show_bar('<?=$new['id']?>','news','<?=$lang?>')" onmouseout="hide_bar('<?=$new['id']?>','news','<?=$lang?>')" id="news<?=$new['id']?>">
  <?endif;?>


        <h2><a href="<?=$lang.'/news/content/'.$new['id'].'.html'?>"><?=$new['title']?></a></h2>
        <p><?=$new['annotation']?></p>


    <?if($visual_mode):?>
   </div>
 <?endif;?>

    <?endforeach;?>
        </div>

<?endif;?>