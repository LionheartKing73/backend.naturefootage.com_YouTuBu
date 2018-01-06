<div class="pageNav">
    <?if($nav['first']):?>
        <a href="<?=$nav['first']?>" class="first"><?=$this->lang->line('first')?></a>
    <?else:?>
        <span class="first"><?=$this->lang->line('first')?></span>
    <?endif;?>

    <?if($nav['prev']):?>
        <a href="<?=$nav['prev']?>" class="next"><?=$this->lang->line('prev')?></a>
    <?else:?>
        <span class="prev"><?=$this->lang->line('prev')?></span>
    <?endif;?>

    <?if($from>0):?>
        <span class="andmore">...</span>
    <?endif;?>

    <?foreach($pages as $page):?>
        <?if($page['link']):?>
            <a href="<?=$page['link']?>"><?=$page['name']?></a>
        <?else:?>
            <span class="current"><?=$page['name']?></span>
        <?endif;?>
    <?endforeach;?>

    <?if($to<$pages_count):?>
        <span class="andmore">...</span>
    <?endif;?>

    <?if($nav['next']):?>
        <a href="<?=$nav['next']?>" class="next"><?=$this->lang->line('next')?></a>
    <?else:?>
        <span class="next"><?=$this->lang->line('next')?></span>
    <?endif;?>

    <?if($nav['last']):?>
        <a href="<?=$nav['last']?>" class="last"><?=$this->lang->line('last')?></a>
    <?else:?>
        <span class="last"><?=$this->lang->line('last')?></span>
    <?endif;?>
</div>