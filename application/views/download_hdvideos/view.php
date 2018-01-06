<form action="" method="post" class="form-horizontal well">
    <fieldset>
        <table class="table table-striped">
            <tr>
            	<td width="60%">
            	For all Users
                &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" id="forallusers" name="hdvideochoice" 
                <?php $result = $hdvideochoice == 'forallusers' ? "checked" : " "; echo $result; ?> value="forallusers"/>
            	&nbsp;&nbsp;&nbsp;&nbsp; For Selective Users
                &nbsp;&nbsp;&nbsp;&nbsp; <input type="radio" id="forselectiveusers" name="hdvideochoice" 
                <?php $result = $hdvideochoice == 'forselectiveusers' ? "checked" : " "; echo $result; ?>
                 value="forselectiveusers"/>
            	</td>
				<td width="30%"><a href="/en/download_hdvideos/edit/1">EDIT</a></td>
            </tr>
        </table>
    </fieldset>
</form>

<script type="application/javascript">
<?php if($hdvideochoice=='forallusers') { ?>	
document.getElementById("forselectiveusers").disabled = true;
<?php } ?>
<?php if($hdvideochoice=='forselectiveusers') { ?>	
document.getElementById("forallusers").disabled = true;
<?php } ?>
</script>
