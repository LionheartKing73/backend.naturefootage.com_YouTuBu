<? if (!empty($tree)) { ?>
<nav id="top_nav">
    <ul>
    <?
        $count = count($tree);
        $i = 0;
        foreach ($tree as $key=>$val) {
            $i++;
        ?>
        <li <?if($i == $count){?>class="last"<?}?>><a href="<? if($val['link']){echo $lang.'/'.$val['link'];}?>"><?=$val['title']?></a></li>
    <?}?>
    </ul>
</nav>
<?}?>
