<div id="search_bg">
<div id="filters">
    <div id="filter_title">Filter these results</div>

    <? if ($clear) {?>
        <a href="<?=$lang?>/search.html" id="remove_filters" class="visible">Clear all filters</a>
    <? } ?>

    <?
    $opened_filters = array('collection', 'cat');
    foreach ($filters as $code=>$filter) {
        ?>
        <h3><?=$filter['title']?></h3>
        <div>
            <?
            foreach ($filter['options'] as $option) {
                if (isset($option['uri'])) {
                    ?>
                    <a<?if (!in_array($code ,$opened_filters)) {?> rel="nofollow"<?}?> href="<?=$lang?>/search<?=$option['uri'].$this->config->item('url_suffix')?>"<?if ($option['level']) echo ' style="margin-left: ', 15 * $option['level'], 'px"'?>>
                        <?=$option['name']?>
                    </a>
                    <?
                } else {
                    ?>
                    <p class="current"<?if ($option['level']) echo ' style="margin-left: ', 15 * $option['level'] - 8, 'px"'?>>
                        <?=$option['name']?>
                    </p>
                    <?
                }
            }
            ?>
        </div>
        <? } ?>

</div>

<div id="results_cont">

<div class="title_cont">

    <h1 class="page_title">
        <? if($title) {?>
            Your search for <span><?=$title?></span> returned <?=intval($all)?> results
        <? } else { ?>
            Browse content &ndash; <?=intval($all)?> items
        <?}?>
    </h1>

    <div class="clear"></div>

</div>

<?if($results) {?>

<div class="results_pagination">
    <div class="count">
        <?=$this->lang->line('displaying')?> <?=$displaying?> <?=$this->lang->line('from')?> <?=intval($all)?>
    </div>
    <div class="pages">
        <?=$page_navigation?>
    </div>
    <div class="perpage">
        <form method="post" name="perpage_form1">
            <?=$this->lang->line('results_per_page')?>:
            <select name="perpage" onchange="document.forms['perpage_form1'].submit();">
                <option value="4" <?if($perpage==4) echo "selected"?>>4</option>
                <option value="12" <?if($perpage==12) echo "selected"?>>12</option>
                <option value="20" <?if($perpage==20) echo "selected"?>>20</option>
            </select>
        </form>
    </div>
    <div class="clear"></div>
</div>

<div class="results">
    <?=$results?>
    <div id="addToCartForm" title="Add to cart" class="popup">
        <p class="message" style="display: none;"><?=$this->lang->line('required_fields_err')?>.</p>
        <form class="to_cart_form">
        </form>
    </div>
</div>

<div class="clear"></div>

<div class="results_pagination bottom_pagination">
    <div class="count">
        <?=$this->lang->line('displaying')?> <?=$perpage?> <?=$this->lang->line('from')?> <?=intval($all)?>
    </div>
    <div class="pages">
        <?=$page_navigation?>
    </div>
    <div class="perpage">
        <form method="post" name="perpage_form2">
            <?=$this->lang->line('results_per_page')?>:
            <select name="perpage" onchange="document.forms['perpage_form2'].submit();">
                <option value="4" <?if($perpage==4) echo "selected"?>>4</option>
                <option value="12" <?if($perpage==12) echo "selected"?>>12</option>
                <option value="20" <?if($perpage==20) echo "selected"?>>20</option>
            </select>
        </form>
    </div>
    <div class="clear"></div>
</div>


<?} else {?>
    <div class="clear"></div>
    <div class="content_padding typography"><p><?=$this->lang->line('no_results')?>.</p></div>
<?}?>

<div class="clear"></div>

</div>
<div class="clear"></div>

</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#filters p.current').click(function () {
            $(this).toggleClass('opened');
            $(this).siblings('a').toggleClass('visible');
        });
    });
</script>

<br class="clear">
