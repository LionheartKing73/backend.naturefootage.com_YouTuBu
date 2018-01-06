<div class="content_bg">
<div class="title_cont">
    <?if ($subcats) {?>
    <h1 class="page_title"><?=$header?></h1>
    <div class="back">
        <a href="<?=$lang?>/<?=$module?>.html"><?=$this->lang->line('back_to')?> <?=$back?> <?=$this->lang->line('list')?></a>
    </div>
    <?} else {?>
    <h1 class="page_title"><?=$header?></h1>
    <?}?>
    <div class="clear"></div>
</div>

<? if(!empty($cat_description)) {?>
<div class="result_description typography">
    <?=$cat_description?>
</div>
<? } ?>

<?
$num = 0;
if (count($cats)) {?>
<div class="results">
    <?
    foreach($cats as $cat) {
        ++$num;
        ?>
        <div class="result catresult<?if(!($num%4)){?> last<?}?>">
            <div class="top_bar">
                <a href="<?=$lang.'/search/'.$module.'/'.$cat['uri'].'.html'?>"><?=$cat['title']?></a>
            </div>
            <div>
                <a href="<?=$lang.'/search/'.$module.'/'.$cat['uri'].'.html'?>"><img src="<?=$cat['thumb']?>" width="200" alt="<?=$cat['alt'] ? $cat['alt'] : $cat['title']?>"></a>
            </div>
            <?if(($module == 'categories') && $cat['child']) {?>
            <div class="bottom_bar">
                <a href="<?=$lang?>/<?=$module?>/<?=$cat['uri']?>.html">Click here for more <b><?=$cat['title']?></b> <?=$type_name?></a>
            </div>
            <?} elseif (($module == 'collections') && ($cat['price'] > 0.00)) {?>
            <div class="bottom_bar">
                <div>PRICE:</div>
                <div><?=$cat['price']?></div>
            </div>
            <?}?>
        </div>

        <?if(!($num%4)) {?>
            <div class="clear"></div>
        <?}}?>

    <div class="clear"></div>
</div>
<?}?>

<div class="clear"></div>
</div>