<? if (!empty($tree)) {
    $i=0;
?>
<?foreach ($tree as $val) {
    $i++;
?>
    <div class="boxgrid caption">
        <a href="<? if($val['uri']){echo $lang.'/'.$val['uri'];}?>"><img src="<?=$val['thumb']?>" width="337" /></a>
        <div class="cover boxcaption">
            <h3><a href="<? if($val['uri']){echo $lang.'/'.$val['uri'];}?>"><?=$val['title']?></a></h3>
            <p><?=$val['description']?></p>
        </div>
    </div>
    <?if($i==1){?>
        <div class="boxgrid_clear">
            <div id="facebook_follow"></div>
            <div id="twitter_follow"></div>
        </div>
        <?}?>
    <?}?>
<?}?>
