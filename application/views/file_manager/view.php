<style type="text/css">
    @import url(/data/aspapi/application.css);
    @import url(/data/easyui/themes/bootstrap/easyui.css);
    @import url(/data/easyui/themes/icon.css);
</style>

<script type="text/javascript">
    var remoteHost = '<?php echo $aspera_config['node_api_host']; ?>';
    var remoteUser = '<?php echo $aspera_config['usersstorage_user']; ?>';
    var remotePassword = '<?php echo $aspera_config['usersstorage_password']; ?>';
    var homePath = '<?php echo $home_path; ?>';
    var restServiceURL = 'en/file_manager/node_api';
</script>

<script type="text/javascript" src="/data/easyui/jquery.easyui.min.js"></script>
<script type="text/javascript" src="/data/aspapi/asperaplugininstaller.js"></script>
<script type="text/javascript" src="/data/aspapi/node_file_manager.js"></script>

<?php if($users) { ?>
<div class="toolbar-item">
    <form name="users" action="<?=$lang?>/file_manager/view">
        <label for="user">User:</label>
        <select name="user" id="user" style="width: auto" onchange="document.forms['users'].submit();">
            <option value="0">All</option>
            <? foreach($users as $user) { ?>
            <option value="<?=$user['id']?>" <?if($user['id'] == $selected_user) echo 'selected'?>><?=$user['fname'] . ' ' . $user['lname']?></option>
            <? } ?>
        </select>
    </form>
</div>
<?php } ?>

<div onload="initAsperaConnect()">

    <div id="connect_installer" style="display: none; align:center"></div>

    <input type="button" value="Upload files" id="upload_files_button" class="btn">
    <input type="button" value="Upload directory" id="upload_directory_button" class="btn">
    <input type="button" value="Download" id="download_file_button" class="btn"><br><br>

    <table id="dirList" class="table table-striped">
        <tr>
            <td>&nbsp;</td>
        </tr>
    </table>

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
