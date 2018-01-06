<strong class="toolbar-item"<?php if ( $paging ) { ?> style="margin-top: 10px;"<?php } ?>>
    <?php echo $this->lang->line( 'action' ) ?>:
</strong>

<div class="btn-group toolbar-item">
<?php if ( $this->permissions[ 'formdata-contactus_delete' ] ) { ?>
    <a class="btn" href="javascript: if ( check_selected( document.formdata_contactus, 'id[]' ) ) change_action( document.formdata_contactus,'<?php echo $lang ?>/formdata/contactus/delete' )">
        <?php echo $this->lang->line( 'delete' ) ?>
    </a>
<?php } ?>
</div>

<?php if ( $paging ) { ?>
    <div class="pagination">
        <?php echo $paging ?>
    </div>
<?php } ?>

<form name="formdata_contactus" action="<?php echo $lang ?>/formdata/contactus" method="POST">
    <table class="table table-striped">
        <tr>
            <th width="30" align="center">
                <input type="checkbox" name="sample" onclick="javascript:select_all( document.formdata_contactus )">
            </th>
            <th>Date</th>
            <th>Name</th>
            <th>Company</th>
            <th>Contact</th>
            <th>Message</th>
            <th width="80" align="center">Action</th>
        </tr>

<?php if ( $list ) { ?>
    <?php foreach ( $list as $item ) { ?>
        <tr style="font-size: 12px;">
            <td>
                <input type="checkbox" name="id[]" value="<?php echo $item[ 'id' ] ?>">
            </td>
            <td>
                <?php echo date( 'Y.m.d H:i', strtotime( $item[ 'timestamp' ] ) ) ?>
            </td>
            <td>
                <?php echo $item[ 'firstname' ] . ' ' . $item[ 'lastname' ] ?>
            </td>
            <td>
                <?php echo $item[ 'companyname' ] ?>
            </td>
            <td>
                <?php if ( $item[ 'phone' ] ) { echo 'Phone: ' . $item[ 'phone' ] . '<br />'; } ?>
                <?php if ( $item[ 'email' ] ) { echo 'Email: ' . $item[ 'email' ]; } ?>
            </td>
            <td>
                <i>
                    <?php echo $item[ 'inquiry' ] ?>
                </i>
            </td>
            <td>
                <?php if ( $this->permissions[ 'formdata-contactus' ] ) {
                    get_actions(
                        array (
                            array (
                                'display' => $this->permissions[ 'formdata-contactus_delete' ],
                                'url' => $lang . '/formdata/contactus/delete/' . $item[ 'id' ],
                                'name' => $this->lang->line( 'delete' ),
                                'confirm' => $this->lang->line( 'delete_confirm' )
                            )
                        )
                    );
                } ?>
            </td>
        </tr>
    <?php } ?>
<?php } else { ?>
        <tr>
            <td colspan="7" style="text-align: center">
                <?php echo $this->lang->line( 'empty_list' ) ?>
            </td>
        </tr>
<?php } ?>
    </table>
</form>

<?php if ( $paging ) { ?>
<div class="pagination">
    <?php echo $paging ?>
</div>
<?php } ?>

<script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>