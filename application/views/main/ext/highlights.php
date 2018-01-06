<?if(count($highlights)){?>
    <div id="highlights">
        <h2 class="section_title"><?=$this->lang->line('new_arrivals')?></h2>
        <?
        $i = 1;
        foreach($highlights as $item){?>
            <div class="highlight_item<?if($i % 3 == 0){echo ' last';}?>">
                <h3>
                    <?if($item['link']){?>
                        <a href="<?=$lang?>/<?=$item['link']?>"><?=$item['title']?></a>
                    <?}else{?>
                        <?=$item['title']?>
                    <?}?>
                </h3>
                <?if($item['resource_video']){?>
                <div id="highlightPlayer_<?=$item['id']?>"></div>
                <script type="text/javascript">
                    $(document).ready(function(){
                        createPlayer('<?=$this->config->item('base_url').$item['resource_file']?>', 215, 120, "highlightPlayer_<?=$item['id']?>", true, false);
                    });
                </script>
                <?}else{
                    if($item['link']){?>
                        <a href="<?=$lang?>/<?=$item['link']?>"><img src="<?=$item['resource_file'];?>?date=<?=$item['mtime'];?>" width="215" height="120"></a>
                    <?}else{?>
                        <img src="<?=$item['resource_file'];?>?date=<?=$item['mtime'];?>" width="215" height="120">
                    <?}
                }?>
            </div>
            <?
            $i++;
        }?>
        <div class="clear"></div>
    </div>
<?}?>