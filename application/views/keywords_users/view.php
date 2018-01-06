<form name="filter" action="<?php echo $lang; ?>/keywords_users/view" id="form_filter" method="POST">
    <input type="hidden" name="filter[hidden]" value="1" />
    <table>
        <tr>
            <td>
                <input type="text" name="filter[keyword]" placeholder="Keyword" value="<?php if (isset($filter['keyword'])) echo $filter['keyword']; ?>" />
            </td>
<!--            <td>
                <input type="text" name="filter[collection]" placeholder="Collection" value="<?php if (isset($filter['collection'])) echo $filter['collection']; ?>" />
            </td>
            <td>
                <input type="text" name="filter[section]" placeholder="Section" value="<?php if (isset($filter['section'])) echo $filter['section']; ?>" />
            </td>-->
            <td>
                <a class="btn" onclick="filter.submit();">Aplly filter</a>
                <a class="btn" onclick="$('form[name=filter] input').each(function() {
                            $(this).attr('value', '');
                        });
                        filter.submit();">Clear</a>
            </td>
        </tr>
    </table>
</form>
<?php 
//print_r($_SESSION);die;
?>

<strong class="toolbar-item"<?php if ($paging) { ?> style="margin-top: 10px;"<?php } ?>>
    <?php echo $this->lang->line('action'); ?>:
</strong>

<div class="btn-group toolbar-item">
    <?php if ($this->permissions['keywords-delete']) { ?>
        <span class="btn ajaxify" id="add" data-action="en/keywords_users/add">Add</span>
        <a class="btn" onclick="javascript: if (check_selected(document.keywords, 'id[]'))
                        change_action(document.keywords, '<?= $lang ?>/keywords_users/delete');
                    keywords.submit();"><?php echo $this->lang->line('delete'); ?></a>
       <?php } ?>
</div>
<!--
<div class="btn-group toolbar-item">
<?php if ($this->permissions['keywords-save']) { ?>
                                                 <a class="btn" onclick="keywords.submit();">Save basic status</a>
<?php } ?>
</div>
-->

<?php if ($paging) { ?>
    <div class="pagination" style="float: right; width: auto">
        <?php echo $paging; ?>
    </div>
<?php } ?>

<br class="clr">

<form name="keywords" method="POST">
    <input type="hidden" id="form_action" name="" />
    <table class="table table-striped">
        <tr>
            <th width="30" align="center">
                <input type="checkbox" name="sample" onclick="javascript:select_all(document.keywords)">
            </th>
            <th>Keyword</th>
            <th>Front End</th>
            <!-- th>Provider</th -->
            <!--
            <th>
            <?php if ($this->permissions['keywords-save']) { ?>
                            <input type="checkbox" name="basic" id="basic_parent" onclick="javascript:select_all_basic()">
                                                                                                                                                    <script type="text/javascript">
                                                                                                                                                        function select_all_basic () {
                                                                                                                                                            var $status = $( '#basic_parent' ).prop( 'checked' );
                                                                                                                                                            $( '.basic_status' ).each(
                                                                                                                                                                function () {
                                                                                                                                                                    $( this ).attr( 'checked', $status );
                                                                                                                                                                }
                                                                                                                                                            );
                                                                                                                                                        }
                                                                                                                                                    </script>
            <?php } ?>
                Basic status
            </th>
            -->
            <th width="120" align="center">Action</th>
        </tr>

        <?php if ($keywords) { ?>
            <?php foreach ($keywords as $keyword) { ?>

                <tr>
                    <td>
                        <input type="checkbox" name="id[]" value="<?php echo $keyword['id']; ?>">
                    </td>
                    <td><?php echo $keyword['keyword']; ?></td>                   
                    <td><?php echo $keyword['name']; ?></td>
                    <!-- td><?php echo $keyword['provider_id']; ?></td -->
                    <!--
                    <td>
                    <?php if ($this->permissions['keywords-save']) { ?>
                                                                                                                                                        <input type="hidden" name="basic[<?php echo $keyword['id']; ?>]" value="0" />
                                                                                                                                                        <input type="checkbox" name="basic[<?php echo $keyword['id']; ?>]" class="basic_status" value="1" <?php if ($keyword['basic'] == 1) echo "checked"; ?> />
                    <?php } else { ?>
                        <?php if ($keyword['basic'] == 1) echo "yes"; ?>
                    <?php } ?>
                    </td>
                    -->
                    <td>
                        <?php
                        get_actions(
                                array(
                                    array(
                                        'display' => $this->permissions['keywords-delete'],
                                        'url' => $lang . '/keywords_users/delete/' . $keyword['id'],
                                        'name' => $this->lang->line('delete'),
                                        'confirm' => $this->lang->line('delete_confirm')
                                    ),
                                    array(
                                        'display' => $this->permissions['keywords-delete'],
                                        'url' => $lang . '/keywords_users/edit/' . $keyword['id'],
                                        'name' => $this->lang->line('edit'),
                                        'keyword' => $keyword['keyword'],
                                        'collection' => $keyword['collection_id'],
                                        'section' => $keyword['section_id'],
                                    )
                                )
                        );
                        ?>
                    </td>
                </tr>

            <?php } ?>

            </tr>

        <?php } else { ?>

            <tr>
                <td colspan="6" style="text-align: center">
                    <?php echo $this->lang->line('empty_list'); ?>
                </td>
            </tr>

        <?php } ?>

    </table>
</form>

<?php if ($paging) { ?>
    <div class="pagination">
        <?php echo $paging; ?>
    </div>
<?php } ?>

<script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>
<script type="text/javascript">
                    $(document).ready(function() {
                        $("#add-dialog").dialog({
                            autoOpen: false,
                            show: {effect: "blind", duration: 100},
                            hide: {effect: "explode", duration: 100},
                            buttons: {
                                Cancel: function() {
                                    $(this).dialog("close");
                                },
                                "OK": function() {
                                    $('#add-dialog form').submit();
                                    $(this).dialog("close");
                                }
                            }
                        });

                        $(".edit").on('click', function(e) {
                            e.stopPropagation();
                            e.preventDefault();
                            $('#add-dialog form').attr("action", $(this).data('action'));
                            $('#add-dialog form input[name="keyword"]').val($(this).data('k'));
                            $('#add-dialog form select[name="collection"]').val($(this).data('c'));
                            $('#add-dialog form select[name="section"]').val($(this).data('s'));
                            $("#add-dialog").dialog("open");
                        });
                        $('#add').on('click', function() {
                            $('#add-dialog form').attr("action", $(this).data('action'));
                            $('#add-dialog form input[name="keyword"]').val('');
                            $('#add-dialog form input[name="collection"]').val('');
                            $('#add-dialog form select[name="section"]').val('');
                            $('#add-dialog form select[name="collection"]').val('');
                            $("#add-dialog").dialog("open");
                        });

                    });
</script>

<style type="text/css">
    .table th, .table td {
        padding: 4px !important;
        line-height: 18px;
        text-align: left;
        vertical-align: middle !important;
        border-top: 1px solid #dddddd;
    }
    #form_filter {
        border-top: 1px solid #ccc;
        border-bottom: 1px solid #ccc;
        padding: 8px 0;
    }
    #form_filter input[type="text"] {
        margin: 0px 10px;
    }
    .dropdown-menu span{display: block;}
    .dropdown-menu span:hover{color: #ffffff; background-color: #0088cc;}

</style>
<!--Add form-->

<?php
//echo '<pre>';
//print_r($sections);
//echo '</pre>';
?>

<div id="add-dialog">
    <form method="post" >
        <span>Keyword:</span><input type="text" name="keyword" class="to" style="width: 260px;" value=""><br>
<!--        <span>Collection:</span><input type="text" name="collection" class="to" style="width: 260px;" value=""><br>-->
        <span>Front End:</span>
        <?php
//        echo '<pre>'.'asdsad';
//        print_r($collection);
        ?>

        <select name="collection" style="width: 260px;">
            <option selected="">Select Collection</option>
            <?php
            foreach ($collection as $v) {
                echo '<option value="' . $v['id'] . '">' . $v['name'] . '</option>';
            }
            ?>
        </select>
        <br>

        <span>Section:</span>
        <select name="section" style="width: 260px;">
            <option selected="">Select Section</option>
            <?php
            foreach ($sections as $k => $v) {
                echo '<option value="' . $k . '">' . $k . '</option>';
            }
            ?>
        </select>
        <input type="hidden" value="<?= $_SESSION['uid'] ?>" name="user_id"> 
        <br>
    </form>
</div>