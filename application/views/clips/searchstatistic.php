<strong class="toolbar-item">Search statistic:</strong>
<div class="btn-group toolbar-item">
	<a href="<?= $lang ?>/clips/searchstatistic/overall" class="btn<? if ( $type == 'overall' ) echo ' active' ?>">Overall</a>
	<a href="<?= $lang ?>/clips/searchstatistic/request" class="btn<? if ( $type != 'overall' ) echo ' active' ?>">Requests</a>
</div>

<div style="float: right">
	<form id="provider-filter" method="POST">
		<input type="hidden" name="providerfilter" value="1" />
		<strong class="toolbar-item">Provider:</strong>
		<div class="btn-group toolbar-item">
			<select id="provider-select" name="provider">
				<option value="">all</option>
				<? if( $providers ) foreach ( $providers as $provider ) { ?>
					<option value="<?= $provider[ 'id' ] ?>"<? if ( $provider[ 'id' ] == $filter[ 'provider' ] ) echo ' selected="selected"'; ?>><?= $provider[ 'name' ] ?></option>
				<? } ?>
			</select>
		</div>
	</form>
</div>

<? if ( $type != 'overall' ) { ?>
	<div style="float: right">
		<form method="POST">
			<input type="hidden" name="userfilter" value="1" />
			<strong class="toolbar-item">User:</strong>
			<div class="btn-group toolbar-item">
				<input type="text" name="user" value="<?= $filter[ 'user' ] ?>" style="width: 140px; float: left;" />
				<input type="submit" value="Filter" class="btn" style="float: right;" />
			</div>
		</form>
	</div>
<? } ?>

<? if ( $type != 'overall' ) { ?>
	<form name="statistics" method="POST" style="float: right;">
		<input type="hidden" name="datefilter" value="1" />
		<div class="toolbar-item">
			<div class="controls-group">
				<label>Date, From:</label>
				<input type="text" name="datefrom" value="<?=$filter[ 'datefrom' ]?>" style="width: 80px">&nbsp;<img src="data/img/admin/calendar.gif" width=16 height=16 onClick="displayCalendar(document.statistics.datefrom,'dd.mm.yyyy',this);return false" align="absmiddle">
			</div>
			<div class="controls-group">
				<label>To:</label>
				<input type="text" name="dateto" value="<?=$filter[ 'dateto' ]?>" style="width: 80px">&nbsp;<img src="data/img/admin/calendar.gif" width=16 height=16 onClick="displayCalendar(document.statistics.dateto,'dd.mm.yyyy',this);return false" align="absmiddle">
			</div>
			<div class="controls-group">
				<input type="submit" value="Filter" class="btn">
			</div>
		</div>
	</form>
<? } ?>

<br class="clr">

<? if ( $type == 'overall' ) { ?>
	<table border="0" width="100%" cellpadding="1" cellspacing="1">
		<tr class="table_title" style="font-weight: bold; border-bottom: 1px dashed #ccc;">
			<td style="text-align: right; padding: 0 40px 0 0;">Phrase</td>
			<td>Provider</td>
			<td style="text-align: center; width: 120px;">Times</td>
		</tr>
		<? if ( $logs ) { ?>
		<? foreach ( $logs as $log ) { ?>
		<tr class="tdata1">
			<td style="text-align: right; padding: 0 40px 0 0; width: 70%;" onmouseover='light(this);' onmouseout='dark(this);'><?= $log[ 'phrase' ] ?></td>
			<td><?= $log[ 'provider' ] ?> (<?= $log[ 'provider_id' ] ?>)</td>
			<td style="text-align: center;"><?= $log[ 'times' ] ?></td>
		</tr>
		<? } ?>
		<? } else { ?>
		<tr class="tdata1">
			<td colspan="3" align="center" height="125">
				<?=$this->lang->line( 'empty_list' );?>
			</td>
		</tr>
		<? } ?>
		<tr class="tdata1"><td colspan="4" align="center" height="12">&nbsp;</td></tr>
	</table>
<? } else { ?>
	<table border="0" width="100%" cellpadding="1" cellspacing="1">
		<tr class="table_title" style="font-weight: bold; border-bottom: 1px dashed #ccc;">
			<td>User</td>
			<td>Phrase</td>
			<td>Provider</td>
			<td style="text-align: center; width: 140px;">Date</td>
		</tr>
		<? if ( $logs ) { ?>
			<? foreach ( $logs as $log ) { ?>
				<tr class="tdata1">
					<td style="padding: 3px 10px;" onmouseover='light(this);' onmouseout='dark(this);'><?= $log[ 'user_login' ] ?></td>
					<td style="padding: 0 10px;" onmouseover='light(this);' onmouseout='dark(this);'><?= $log[ 'phrase' ] ?></td>
					<td style="padding: 0 10px;" onmouseover='light(this);' onmouseout='dark(this);'><?= $log[ 'provider' ] ?> (<?= $log[ 'provider_id' ] ?>)</td>
					<td style="padding: 0 10px; text-align: center;"><?= $log[ 'ctime' ] ?></td>
				</tr>
			<? } ?>
		<? } else { ?>
			<tr class="tdata1">
				<td colspan="4" align="center" height="125">
					<?=$this->lang->line( 'empty_list' );?>
				</td>
			</tr>
		<? } ?>
		<tr class="tdata1"><td colspan="4" align="center" height="12">&nbsp;</td></tr>
	</table>
<? } ?>

<script type="text/javascript">
	$( '#provider-select' ).change( function () {
		$( '#provider-filter').submit();
	} );
</script>