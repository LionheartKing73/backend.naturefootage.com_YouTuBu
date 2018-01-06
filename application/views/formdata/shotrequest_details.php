<strong class="toolbar-item"<?php if ( $paging ) { ?> style="margin-top: 10px;"<?php } ?>>
    <?php echo $this->lang->line( 'action' ) ?>:
</strong>

<div class="btn-group toolbar-item">
<?php if ( $this->permissions[ 'formdata-shotrequest' ] ) { ?>
    <a class="btn" href="<?php echo $lang ?>/formdata/shotrequest">
        Show all
    </a>
<?php } ?>
<?php if ( $this->permissions[ 'formdata-shotrequest_delete' ] ) { ?>
    <a class="btn" href="<?php echo $lang ?>/formdata/shotrequest/delete/<?php echo $id ?>">
        <?php echo $this->lang->line( 'delete' ) ?>
    </a>
<?php } ?>
</div>

<form name="formdata_shotrequest" action="<?php echo $lang ?>/formdata/shotrequest" method="POST" style="max-width: 800px;">
    <span style="font-style: italic; display: inline-block; float: right;">
        Created: <?php echo $timestamp ?>
    </span>
    <table class="table table-striped">
        <tr>
            <td style="width: 160px;">
                <strong>First Name:</strong>
            </td>
            <td>
                <?php echo $firstname ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong>Last Name:</strong>
            </td>
            <td>
                <?php echo $lastname ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong>Company Name:</strong>
            </td>
            <td>
                <?php echo $companyname ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong>Company type:</strong>
            </td>
            <td>
                <?php echo $companytype ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong>Job title:</strong>
            </td>
            <td>
                <?php echo $jobtitle ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong>Country:</strong>
            </td>
            <td>
                <?php echo $country ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong>State:</strong>
            </td>
            <td>
                <?php echo $state ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong>Phone:</strong>
            </td>
            <td>
                <?php echo $phone ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong>Website:</strong>
            </td>
            <td>
                <?php echo $website ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong>Email:</strong>
            </td>
            <td>
                <?php echo $email ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong>Production description:</strong>
            </td>
            <td>
                <?php echo $production_description ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong>Footage details:</strong>
            </td>
            <td>
                <?php echo $footage_details ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong>Format:</strong>
            </td>
            <td>
                <?php echo $format ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong>Preview deadline:</strong>
            </td>
            <td>
                <?php echo $preview_deadline ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong>Master deadline:</strong>
            </td>
            <td>
                <?php echo $master_deadline ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong>License:</strong>
            </td>
            <td>
                <?php echo $license ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong>Budget:</strong>
            </td>
            <td>
                <?php echo $budget ?>
            </td>
        </tr>
    </table>
</form>

<strong class="toolbar-item"<?php if ( $paging ) { ?> style="margin-top: 10px;"<?php } ?>>
    <?php echo $this->lang->line( 'action' ) ?>:
</strong>

<div class="btn-group toolbar-item">
    <?php if ( $this->permissions[ 'formdata-shotrequest' ] ) { ?>
        <a class="btn" href="<?php echo $lang ?>/formdata/shotrequest">
            Show all
        </a>
    <?php } ?>
    <?php if ( $this->permissions[ 'formdata-shotrequest_delete' ] ) { ?>
        <a class="btn" href="<?php echo $lang ?>/formdata/shotrequest/delete/<?php echo $id ?>">
            <?php echo $this->lang->line( 'delete' ) ?>
        </a>
    <?php } ?>
</div>

<script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>