<div id="resourcesBrowser" style="display: none"></div>

<script type="text/javascript">
    function openResourcesBrowser(field) {

        var div = document.getElementById('resourcesBrowser');

        window.KCFinder = {
            callBack: function(url) {
                window.KCFinder = null;
                field.value = url;
                $("#resourcesBrowser").dialog("close");
                div.innerHTML = '';
            }
        };
        div.innerHTML = '<iframe name="kcfinder_iframe" src="/vendors/kcfinder/browse.php?type=clip&lang=en&dir=clip/res" ' +
            'frameborder="0" width="100%" height="100%" marginwidth="0" marginheight="0" scrolling="no" />';

        $("#resourcesBrowser").dialog({
            title: "Resources Browser",
            width: 750,
            height: 490,
            position: ["center", "center"],
            modal: true,
            resizable: true,
            buttons: {
                "Close": function() {
                    $(this).dialog("close");
                    div.innerHTML = '';
                }
            }
        });
    }

</script>

<strong class="toolbar-item">
    <?=$this->lang->line('action')?>:
</strong>

<div class="btn-group toolbar-item">
    <?if($id && $this->permissions['clips-edit']){?>
    <a href="<?=$lang?>/clips/edit<?='/'.$id?>" class="btn">
        <?=$this->lang->line('edit')?>
    </a>
    <? } ?>
    <?if($id && $this->permissions['clips-cats']){?>
    <a href="<?=$lang?>/clips/cats<?='/'.$id?>" class="btn">
        Categories
    </a>
    <? } ?>
    <!--
    <?if($id && $this->permissions['clips-sequences']){?>
    <a href="<?=$lang?>/clips/sequences<?='/'.$id?>" class="btn">
        Sequences
    </a>
    <? } ?>
    <?if($id && $this->permissions['clips-bins']){?>
    <a href="<?=$lang?>/clips/bins<?='/'.$id?>" class="btn">
        Bins
    </a>
    <? } ?>
    <?if($id && $this->permissions['clips-galleries']){?>
    <a href="<?=$lang?>/clips/galleries<?='/'.$id?>" class="btn">
        Galleries
    </a>
    <? } ?>-->
    <?if($id && $this->permissions['clips-clipbins']){?>
    <a href="<?=$lang?>/clips/clipbins<?='/'.$id?>" class="btn">
        Clipbins
    </a>
    <? } ?>
    <?if($id && $this->permissions['clips-attachments']){?>
    <a href="<?=$lang?>/clips/attachments<?='/'.$id?>" class="btn">
        Attachments
    </a>
    <? } ?>
    <?if($id && $this->permissions['clips-statistics']){?>
    <a href="<?=$lang?>/clips/statistics<?='/'.$id?>" class="btn">
        Access statistics
    </a>
    <? } ?>
    <?if($id && $this->permissions['clips-derived']){?>
    <a href="<?=$lang?>/clips/derived<?='/'.$id?>" class="btn">
        Derived
    </a>
    <? } ?>
</div>

<br class="clr">

<form action="<?=$lang?>/clips/resources<?='/'.$id?>" method="post" enctype="multipart/form-data" class="form well">
    <fieldset>
        <legend>
            <?=$this->lang->line('resources')?>
            <? if ($code) { ?>
            <p>Clip code: <?=$code?></p>
            <? } ?>
        </legend>

        <table class="table table-striped" style="width: auto;">
            <tr>
                <th>Resource type</th>
                <th>File type</th>
                <th>Actions</th>
            </tr>

            <? foreach ($resources as $key=>$rsrc) { ?>

            <tr>
                <td><?=$rsrc['0']?></td>
                <td><?=$rsrc['1'] ? $rsrc['1'] : '&ndash;' ?></td>

                <? if ($rsrc['1']) { ?>
                <td>
                    <input type="submit" name="delete[<?=$key?>]" value="Delete" class="btn btn-danger"
                           onclick="return confirm('The item will be deleted.');">
                </td>
                <? } elseif ($key == 'hd') { ?>
                <td><input type="text" readonly="readonly" value="Click here to browse the server" onclick="openResourcesBrowser(this);" style="cursor:pointer" name="location" /> <input type="submit" name="save_location[<?=$key?>]" value="Save" class="btn"></td>
                <? } else { ?>
                <td>
                    <? if($key == 'img'){?>
                        <input type="submit" name="create[<?=$key?>]" value="Create" class="btn">
                    <?}else{?>
                        &nbsp;
                    <?}?>
                </td>
                <? } ?>
            </tr>
            <? } ?>
        </table>


    </fieldset>
</form>