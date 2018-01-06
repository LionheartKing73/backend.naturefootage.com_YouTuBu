<?if($news):?>

<h1 class="section_title"><?=$this->lang->line('our_news')?></h1>

<?foreach ($news as $new):?>

<?if($visual_mode):?>
    <div class="boxout" onmouseover="show_bar('<?=$new['id']?>','news','<?=$lang?>')" onmouseout="hide_bar('<?=$new['id']?>','news','<?=$lang?>')" id="news<?=$new['id']?>">
<?endif;?>

<div class="last_news">
    <div class="last_news_date">
        <span class="day"><?=$new['news_day']?></span><br>
        <span class="month"><?=$this->lang->line(strtolower($new['news_month']) . '_short')?></span>
    </div>
    <div class="last_news_content">
        <article>
            <header>
                <h2 class="last_news_title"><a href="<?=$lang.'/news/content/'.$new['id'].'.html'?>"><?=$new['title']?></a></h2>
            </header>
            <? if($new['thumb']){?>
                <div class="last_news_thumb">
                    <a href="<?=$lang.'/news/content/'.$new['id'].'.html'?>"><img src="<?=$new['thumb'];?>?date=<?=$new['mtime'];?>" width="106"></a>
                </div>
            <?}?>
            <p class="last_news_annotation"><?=$new['annotation']?></p>
            <article>
    </div>
    <div class="clear"></div>
</div>

<?if($visual_mode):?>
    </div>
<?endif;?>

<?endforeach;?>

<a href="<?=$lang.'/news.html'?>" class="last_news_all"><?=$this->lang->line('all_news')?></a>

<?endif;?>
