<form action="" method="post" class="form-horizontal well">
    <fieldset>
        <table class="table table-striped">
            <tr>
            	<td width="60%">
            	For all Users
                &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="hdvideochoice" 
                <?php $result = $hdvideochoice == 'forallusers' ? "checked" : " "; echo $result; ?> value="forallusers"/>
            	&nbsp;&nbsp;&nbsp;&nbsp; For Selective Users
                &nbsp;&nbsp;&nbsp;&nbsp; <input type="radio" name="hdvideochoice" 
                <?php $result = $hdvideochoice == 'forselectiveusers' ? "checked" : " "; echo $result; ?>
                 value="forselectiveusers"/>
            	</td>
				<td width="30%">
				
			<input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>" name="save">
		
           </td>
            </tr>
        </table>
    </fieldset>
</form>
