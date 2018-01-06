<strong class="toolbar-item"<?php if ( $paging ) { ?> style="margin-top: 10px;"<?php } ?>>
    <?php echo $this->lang->line( 'action' ) ?>:
</strong>

<div class="btn-group toolbar-item">
<?php if ( $this->permissions[ 'formdata-shotrequest_delete' ] ) { ?>
    <a class="btn" href="javascript: if ( check_selected( document.formdata_shotrequest, 'id[]' ) ) change_action( document.formdata_shotrequest,'<?php echo $lang ?>/formdata/shotrequest/delete' )">
        <?php echo $this->lang->line( 'delete' ) ?>
    </a>
<?php } ?>
</div>

<?php if ( $paging ) { ?>
    <div class="pagination">
        <?php echo $paging ?>
    </div>
<?php } ?>

<form name="formdata_shotrequest" action="<?php echo $lang ?>/formdata/shotrequest" method="POST">
    <table class="table table-striped">
        <tr>
            <th width="30" align="center">
                <input type="checkbox" name="sample" onclick="javascript:select_all( document.formdata_shotrequest )">
            </th>
            <th>Date</th>
            <th>Name</th>
            <th>Company</th>
            <th>Contact</th>
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
                <?php if ( $item[ 'jobtitle' ] ) { echo ' (' . $item[ 'jobtitle' ] . ')'; } ?>
            </td>
            <td>
                <?php echo $item[ 'companyname' ] ?>
                <?php if ( $item[ 'companytype' ] ) { echo ' (' . $item[ 'companytype' ] . ')'; } ?>
            </td>
            <td>
                <?php if ( $item[ 'phone' ] ) { echo 'Phone: ' . $item[ 'phone' ] . '<br />'; } ?>
                <?php if ( $item[ 'email' ] ) { echo 'Email: ' . $item[ 'email' ] . '<br />'; } ?>
                <?php if ( $item[ 'website' ] ) { echo 'Site: ' . $item[ 'website' ]; } ?>
            </td>
            <td>
                <?php if ( $this->permissions[ 'formdata-shotrequest' ] ) {
                    get_actions(
                        array (
                            array (
                                'display' => $this->permissions[ 'formdata-shotrequest_delete' ],
                                'url' => $lang . '/formdata/shotrequest/delete/' . $item[ 'id' ],
                                'name' => $this->lang->line( 'delete' ),
                                'confirm' => $this->lang->line( 'delete_confirm' )
                            ),
                            array (
                                'display' => $this->permissions[ 'formdata-shotrequest_details' ],
                                'url' => $lang . '/formdata/shotrequest/details/' . $item[ 'id' ],
                                'name' => $this->lang->line( 'details' )
                            )
                        )
                    );
                } ?>
            </td>
        </tr>
    <?php } ?>
<?php } else { ?>
        <tr>
            <td colspan="6" style="text-align: center">
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