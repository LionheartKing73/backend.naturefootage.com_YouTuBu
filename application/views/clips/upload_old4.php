<?php if($is_provider) {?>
<style type="text/css">
    @import url(/data/aspapi/application.css);
    @import url(/data/easyui/themes/bootstrap/easyui.css);
    @import url(/data/easyui/themes/icon.css);
</style>

<script type="text/javascript">
    var nodeApiPassword = '<?php echo $aspera_config['provider_password']; ?>';
    var homePath = '<?php echo $home_path; ?>';
</script>

<script type="text/javascript" src="/data/easyui/jquery.easyui.min.js"></script>
<script type="text/javascript" src="/data/aspapi/node_application.js"></script>

<script type="text/javascript" src="/data/aspapi/asperaplugininstaller.js"></script>

<div onload="initAsperaConnect()">

    <div id="connect_installer" style="display: none; align:center"></div>

    <input type="button" value="Upload files" id="upload_files_button" class="btn">
    <input type="button" value="Upload directory" id="upload_directory_button" class="btn"><br><br>

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
<?php } ?>