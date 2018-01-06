<?
if($results) {
    $i = 0;
    $cols = $checks ? 3 : 4;
    $cols = 3;
    foreach($results as $item) { ++$i;
        ?>

    <div class="result <?if (!($i%$cols)){?>last<?}?>">

        <div class="top_bar">
            <div class="rights">
                <?if($checks) {?>
                <input type="checkbox" name="id[]" value="<?=$item['type']?>-<?=$item['id']?>" class="clipCheckbox">
                <?}?>
                <?=$item['rights']?>
            </div>
            <?if ($item['price'] != 0.00) {?><div class="price">$<?=$item['price']?></div><?}?>
            <div class="clear"></div>
        </div>
        <div class="clip_thumb">
            <div class="clipMask"></div>
            <input type="hidden" name="item-<?=$item['id']?>" value="<?=htmlspecialchars('{"id":"'.$item['id'].'","price":"'.$item['price'].'","title":"'.$item['title'].'","motion_thumb":"'.$item['motion_thumb'].'","url":"'.$lang.$item['url'].'"}')?>">
            <div class="imgWrapper">
                <div id="thumbPlayer<?=$item['id']?>"></div>
                <img src="<?=$item['thumb']?>" alt="" width="200" height="112">
            </div>
        </div>

        <div class="bottom_bar">
            <div class="clip_code">
                <a class="clip_title" href="<?=$lang?><?=$item['url']?>">
                    <?=$item['title']?>
                </a>
            </div>
            <div class="icons">
                <a href="<?=$lang?><?=$item['url']?>">
                    <img src="/data/img/info.png" alt=""
                         onmouseover="showClipInfo(event, <?=$item['id']?>, '<?=$lang?>');"
                         onmouseout="hideClipInfo();">
                </a>
                <?if (!$checks) {?>

                <span id="bin_<?=$item['id']?>">
                        <? if ($item['in_bin']) { ?>
                    <img src="/data/img/bin_exist.png" alt="" title="<?=$this->lang->line('already_in_bin')?>">
                    <? } else { ?>
                    <a title="<?=$this->lang->line('add_to_bin')?>" href="<?=$lang?>/bin/add/<?=$item['type']?>/<?=$item['id']?>" class="toBin">
                        <img src="/data/img/bin.png" alt="">
                    </a>
                    <? } ?>
                </span>

                <?}?>
                <a title="<?=$this->lang->line('add_to_basket')?>" href="<?=$lang?>/cart/add/<?=$item['type']?>/<?=$item['id']?>" class="toCart">
                    <img src="/data/img/cart.png" alt="">
                </a>
                <input type="hidden" name="item-<?=$item['id']?>" value="<?=$item['title']?>">
            </div>
            <div class="clear"></div>
        </div>

    </div>

    <?if (!($i%$cols)){?><div class="clear"></div><?}?>

<?}}?>
<div class="clear"></div>
