<?php if($is_provider) {?>
<style type="text/css">
    @import url(/data/aspapi/application.css);
    @import url(/data/easyui/themes/bootstrap/easyui.css);
    @import url(/data/easyui/themes/icon.css);
</style>
<script>
    var asp_remote_host = '<?php echo $aspera_connect_server; ?>';
    var asp_remote_user = '<?php echo $provider_login; ?>';
    var asp_remote_password = '<?php echo $provider_password; ?>';
</script>
<script type="text/javascript" src="/data/easyui/jquery.easyui.min.js"></script>
<script type="text/javascript" src="/data/aspapi/asperaplugininstaller.js"></script>
<script type="text/javascript" src="/data/aspapi/application.js"></script>

<div onload="initAsperaConnect()">

    <div id="connect_installer" style="display: none; align:center"></div>

    <input type="submit" id="upload_files_button" name="upload" class="btn" value="Upload Files"  data-default="Upload Files" />
    <input type="submit" id="upload_directory_button" name="upload_directory" class="btn" value="Upload Directories"  data-default="Upload Directories" />
    <!--<span class="upload_auth_checkbox">
        <input type="checkbox" name="token_authorization" id="cb_token_authorization" value="token_authorization" /><label for="cb_token_authorization">Token Authorization</label>
    </span>-->

    <br><br>

    <div id="uploads_group" class="uploads_group" style="display: none">
        <table id="allUploadTransfers" class="table table-striped">
            <tr>
                <th>Transfer Progress</th>
                <th>File Name</th>
                <th>Control buttons</th>
            </tr>
        </table>
    </div>

    <div id="downloads_group" class="downloads_group" style="display: none">
        <table id="allDownloadTransfers" class="table table-striped">
            <tr>
                <th>Transfer Progress</th>
                <th>File Name</th>
                <th>Control buttons</th>
            </tr>
        </table>
    </div>
</div>
<?php } ?>