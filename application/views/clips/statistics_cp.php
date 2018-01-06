<div class="front-margin">
<?php if ($access) { ?>
    <a href="<?=$lang?>/clips/statistics/all" style="display: block;font-size: 1.5em;margin-bottom: 10px;"><= Back</a>

    <strong class="toolbar-item <?php echo ($access>1)?'hide':''?>">Clips statistic:</strong>
    <div class="btn-group toolbar-item <?php echo ($access>1)?'hide':''?>">
        <a href="<?= $lang ?>/clips/statistics/all" class="btn<? if ( $type != 'raw' ) echo ' active' ?>">All</a>
        <a href="<?= $lang ?>/clips/statistics/raw" class="btn<? if ( $type == 'raw' ) echo ' active' ?>">Raw</a>
    </div>
<?php } ?>
<?php
    function sortByArrow($field){
        if(!empty($_REQUEST['order']) && $_REQUEST['order'] == $field){
            if($_REQUEST['by'] == 'asc'){
                return '<div class="arrow-bottom"></div>';
            }else{
                return '<div class="arrow-top"></div>';
            }
        }
        return '';
    }
?>
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

    <?if($id && $this->permissions['clips-clipbins']){?>
    <a href="<?=$lang?>/clips/clipbins<?='/'.$id?>" class="btn">
        Clipbins
    </a>
    <? } ?>
    <?if($id && $this->permissions['clips-resources']){?>
    <a href="<?=$lang?>/clips/resources<?='/'.$id?>" class="btn">
        Resources
    </a>
    <? } ?>
    <?if($id && $this->permissions['clips-attachments']){?>
    <a href="<?=$lang?>/clips/attachments<?='/'.$id?>" class="btn">
        Attachments
    </a>
    <? } ?>
    <?if($id && $this->permissions['clips-derived']){?>
    <a href="<?=$lang?>/clips/derived<?='/'.$id?>" class="btn">
        Derived
    </a>
    <? } ?>
</div>
<br class="clr">
<form name="statistics" id="statistics" action="<?=$_SERVER['REQUEST_URI']?>" method="post" style="float: left; display: inline-block;">

    <input type="hidden" name="filter" value="1">

    <div class="toolbar-item">
        <div class="controls-group">
            <label><?=$this->lang->line('perpage')?>:</label>
            <select name="perpage" id="perpage" style="width: 60px">
                <option value="50">50</option>
                <option value="100" <?=($perpage=='100')?'selected':''?>>100</option>
                <option value="250" <?=($perpage=='250')?'selected':''?>>250</option>
                <option value="500" <?=($perpage=='500')?'selected':''?>>500</option>
            </select>
        </div>
        <div class="controls-group hide">
            <label><?=$this->lang->line('action')?>:</label>
            <select name="action_type" id="action_type" style="width: 100px">
                <option value="all">ALL</option>
                <?php foreach($actions_types as $action){ ?>
                    <option value="<?=$action['type']?>" <?=($_REQUEST['action_type']==$action['type'])?'selected':''?>><?=$action['name']?></option>
                <?php } ?>
            </select>
        </div>
        <div class="controls-group hide">
            <label><?=$this->lang->line('from')?>:</label>
            <input type="text" name="datefrom" id="datefrom" value="<?=$filter['datefrom']?>" style="width: 80px">&nbsp;<img src="data/img/admin/calendar.gif" width=16 height=16 onClick="displayCalendar(document.statistics.datefrom,'dd.mm.yyyy',this);return false" align="absmiddle">
        </div>
        <div class="controls-group hide">
            <label><?=$this->lang->line('to')?>:</label>
            <input type="text" name="dateto" id="dateto" value="<?=$filter['dateto']?>" style="width: 80px">&nbsp;<img src="data/img/admin/calendar.gif" width=16 height=16 onClick="displayCalendar(document.statistics.dateto,'dd.mm.yyyy',this);return false" align="absmiddle">
        </div>
        <div class="controls-group <?php if (!$access) echo 'hide';?>">
            <label><?=$this->lang->line('period')?>:</label>
            <select name="period" id="period" style="width: 100px">
                <option value="today" <?=($_REQUEST['period']=='today')?'selected':''?>>Today</option>
                <option value="week" <?=($_REQUEST['period']=='week')?'selected':''?>>This Week</option>
                <option value="month" <?=($_REQUEST['period']=='month')?'selected':''?>>This Month</option>
                <option value="3months" <?=($_REQUEST['period']=='3months')?'selected':''?>>3 Months</option>
                <option value="6months" <?=($_REQUEST['period']=='6months')?'selected':''?>>6 Months</option>
                <option value="year" <?=($_REQUEST['period']=='year')?'selected':''?>>1 Year</option>
                <option value="all" <?=($_REQUEST['period']=='all')?'selected':''?>>ALL</option>
            </select>
        </div>
        <div class="controls-group">
            <input type="submit" name="view" value="Filter" class="btn find">
        </div>
        <div class="controls-group">
            <input type="submit" name="export" value="Download" class="btn find">
        </div>
    </div>
</form>

<div class="clearfix"></div>
<?php if ($access) echo '<h1 class="admin-title">USER: '.$statistic[0][ 'user_login' ].'</h1>';?>
<table border="0" cellpadding="1" cellspacing="1" >
    <thead>
	<tr class="table_title" style="font-weight: bold; border-bottom: 4px double #ccc;">
        <?php if ($access){ ?>
            <th style="padding: 5px 10px;width: 140px;" data-order="stat.time"><?=$this->lang->line('clip_date');?> <?php echo sortByArrow('stat.time'); ?></th>
            <th style="padding: 5px 10px;width: 180px;" data-order="c.code">Clip ID (Preview Downloads) <?php echo sortByArrow('c.code'); ?></th>
        <?php }else{ ?>
            <th style="padding: 5px 10px;width: 140px;" data-order="stat.code">Clip ID <?php echo sortByArrow('stat.code'); ?></th>
            <th style="padding: 5px 10px;width: 140px;" data-order="stat.viewed">Views <?php echo sortByArrow('stat.viewed'); ?></th>
            <th style="padding: 5px 10px;width: 140px;" data-order="stat.downloaded">Preview Downloads <?php echo sortByArrow('stat.downloaded'); ?></th>
        <?php } ?>
	</tr>
    </thead>
    <tbody>
    <?php
    $totalV=$totalD=0;
    if ($access){ ?>
        <?php if( $statistic ): foreach( $statistic as $log ): ?>
            <tr class="tdata1">
                <td onmouseover='light(this);' onmouseout='dark(this);' ><?= $log[ 'time' ] ?></td>
                <td style="padding:3px;" onmouseover='light(this);' onmouseout='dark(this);' ><?= $log[ 'code' ] ?></td>
            </tr>
        <?php endforeach; else: ?>
            <tr class="tdata1"><td colspan="4" align="center" height="125"><?=$this->lang->line('empty_list');?></td></tr>
        <?php endif; ?>
        <tr class="tdata1" style="font-weight: bold;"><td colspan="4" align="left">Total: <?=$all;?></td></tr>
    <?php }else{ ?>
        <?php if( $statistic ): foreach( $statistic as $log ):
            $totalV=$totalV+$log[ 'viewed' ];
            $totalD=$totalD+$log[ 'downloaded' ];
            ?>
            <tr class="tdata1 ">
                <td style="padding:3px;" onmouseover='light(this);' onmouseout='dark(this);' ><?= $log[ 'code' ] ?></td>
                <td style="padding:3px;" ><?= $log[ 'viewed' ] ?></td>
                <td onmouseover='light(this);' onmouseout='dark(this);' ><?= $log[ 'downloaded' ] ?></td>
            </tr>
        <?php endforeach; else: ?>
            <tr class="tdata1"><td colspan="4" align="center" height="125"><?=$this->lang->line('empty_list');?></td></tr>
        <?php endif; ?>
        <tr class="tdata1" style="font-weight: bold;">
            <td style="padding:3px;" >Page Subtotal:</td>
            <td style="padding:3px;" ><?=$totalV; ?></td>
            <td onmouseover='light(this);' onmouseout='dark(this);' ><?=$totalD; ?></td>
        </tr>
        <tr class="tdata1" style="font-weight: bold;background: #ddd;">
            <td style="padding:3px;" >Grand Total:</td>
            <td style="padding:3px;" ><?=$allStat[0]['allV'];//$totalV; ?></td>
            <td onmouseover='light(this);' onmouseout='dark(this);' ><?=$allStat[0]['allD'];//$totalD; ?></td>
        </tr>
    <?php } ?>
    </tbody>
</table>
<form name="orderby" id="orderby" action="<?=$_SERVER['REQUEST_URI']?>" method="post" style="visibility: hidden;" >
    <input type="hidden" name="order" id="order" value="<?=$_REQUEST['order']; ?>">
    <input type="hidden" name="by" id="by" value="<?=$_REQUEST['by']; ?>">
    <input type="submit" name="orderby" value="Filter" class="btn find">
</form>

<script type="text/javascript">
    $('table th').on('click', function(){
        $('input#order').val($(this).data('order'));
        if($(this).find('.arrow-top').length != 0){ // ASC
            $('input#by').val('asc');
        }else{ // DESC
            $('input#by').val('desc');
        }
        $('#orderby').submit();
    });
    $('#period').on('change',function(){
        var $from=$('#datefrom');
        var $to=$('#dateto');
        switch ($(this).val()){
            case "today":
                $from.val(Date.parse('today').toString("dd.MM.yyyy"));
                $to.val(Date.parse('today').toString("dd.MM.yyyy"));
                break;
            case "week":
                $from.val(Date.parse('-7').toString("dd.MM.yyyy"));
                $to.val(Date.parse('today').toString("dd.MM.yyyy"));
                break;
            case "month":
                $from.val(Date.parse('-1m').toString("dd.MM.yyyy"));
                $to.val(Date.parse('today').toString("dd.MM.yyyy"));
                break;
            case "3months":
                $from.val(Date.parse('-3m').toString("dd.MM.yyyy"));
                $to.val(Date.parse('today').toString("dd.MM.yyyy"));
                break;
            case "6months":
                $from.val(Date.parse('-6m').toString("dd.MM.yyyy"));
                $to.val(Date.parse('today').toString("dd.MM.yyyy"));
                break;
            case "year":
                $from.val(Date.parse('-1y').toString("dd.MM.yyyy"));
                $to.val(Date.parse('today').toString("dd.MM.yyyy"));
                break;
            case "all":
                $from.val('');
                $to.val('');
                break;
        }
    });
    $('#action_type').on('change',function(){
        $('form#statistics').submit();
    });
</script></div>