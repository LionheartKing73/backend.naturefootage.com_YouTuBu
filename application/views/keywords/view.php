<!--<script type="text/javascript" src="//code.jquery.com/jquery-1.11.3.min.js"></script>-->
<script type="text/javascript" src="data/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#example').DataTable({
            "aoColumnDefs": [
          { 'bSortable': false, 'aTargets': [ 0,4 ] }
       ]
        });

    });
</script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/s/dt/dt-1.10.10/datatables.min.css"/>
<!--<table id="example" class="display" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Name</th>
                <th>Position</th>
                <th>Office</th>
                <th>Age</th>
                <th>Start date</th>
                <th>Salary</th>
            </tr>
        </thead>
 
       
        <tbody>
            <tr>
                <td>Tiger Nixon</td>
                <td>System Architect</td>
                <td>Edinburgh</td>
                <td>61</td>
                <td>2011/04/25</td>
                <td>$320,800</td>
            </tr>
        </tbody>
    </table>-->
<form name="filter" action="<?php echo $lang; ?>/keywords/view" id="form_filter" method="POST">
    <input type="hidden" name="filter[hidden]" value="1" />
    <table>
        <tr>
            <td>
                <input type="text" name="filter[keyword]" placeholder="Keyword" value="<?php if (isset($filter['keyword'])) echo $filter['keyword']; ?>" />
            </td>
            <td>
                <input type="text" name="filter[collection]" placeholder="Collection" value="<?php if (isset($filter['collection'])) echo $filter['collection']; ?>" />
            </td>
            <td>
                <input type="text" name="filter[section]" placeholder="Section" value="<?php if (isset($filter['section'])) echo $filter['section']; ?>" />
            </td>
            <td>
                <a class="btn" onclick="filter.submit();">Apply filter</a>
                <a class="btn" onclick="$('form[name=filter] input').each(function() {
                            $(this).attr('value', '');
                        });
                        filter.submit();">Clear</a>
            </td>
        </tr>
    </table>
</form>

<strong class="toolbar-item"<?php if ($paging) { ?> style="margin-top: 25px;"<?php } ?>>
    <?php echo $this->lang->line('action'); ?>:
</strong>

<div class="btn-group toolbar-item">
    <?php if ($this->permissions['keywords-delete']) { ?>
        <span class="btn ajaxify" id="add" data-action="en/keywords/add">Add</span>
        <a class="btn" onclick="javascript: if (check_selected(document.keywords, 'id[]'))
                    change_action(document.keywords, '<?= $lang ?>/keywords/delete');
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
        <?php //echo $paging; ?>
    </div>
<?php } ?>

<br class="clr">

<form name="keywords" method="POST">
    <input type="hidden" id="form_action" name="" />
    <table <?php if (sizeof($keywords) > 0) { ?>id="example"<?php } ?> class="table table-striped display" cellspacing="0" width="100%" data-page-length='40'>
        <thead>
            <tr>
                <th width="30" align="center">
                    <input type="checkbox" name="sample" onclick="javascript:select_all(document.keywords)">
                </th>
                <th>Keyword</th>
                <th>Collection</th>
                <th>Section</th>
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
        </thead>
        <tbody>
            <?php if ($keywords) { ?>
                <?php foreach ($keywords as $keyword) { ?>

                    <tr>
                        <td>
                            <input type="checkbox" name="id[]" value="<?php echo $keyword['id']; ?>">
                        </td>
                        <td><?php echo $keyword['keyword']; ?></td>
                        <td><?php echo $keyword['collection']; ?></td>
                        <td><?php echo $keyword['section']; ?></td>
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
                                            'url' => $lang . '/keywords/delete/' . $keyword['id'],
                                            'name' => $this->lang->line('delete'),
                                            'confirm' => $this->lang->line('delete_confirm')
                                        ),
                                        array(
                                            'display' => $this->permissions['keywords-delete'],
                                            'url' => $lang . '/keywords/edit/' . $keyword['id'],
                                            'name' => $this->lang->line('edit'),
                                            'keyword' => $keyword['keyword'],
                                            'collection' => $keyword['collection'],
                                            'section' => $keyword['section'],
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
        </tbody>
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
                                $('#add-dialog form input[name="collection"]').val($(this).data('c'));
                                $('#add-dialog form select[name="section"]').val($(this).data('s'));
                                $("#add-dialog").dialog("open");
                            });
                            $('#add').on('click', function() {
                                $('#add-dialog form').attr("action", $(this).data('action'));
                                $('#add-dialog form input[name="keyword"]').val('');
                                $('#add-dialog form input[name="collection"]').val('');
                                $('#add-dialog form select[name="section"]').val('');
                                $("#add-dialog").dialog("open");
                            });

                        });
</script>

<style type="text/css">
/*    .table th, .table td {
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

    #example_length{
        display: none;
    }

    #example_filter{
        display :none;
    }

    #example_info{
        display: none;
    }

    #example_paginate{
        display: none;
    }*/
  #example_length{
        display: none;
    }
   #example_filter{
        display :none;
    }

    #example_info{
        display: none;
    }

</style>
<!--Add form-->

<?php
//echo '<pre>';
//print_r($sections);
//echo '</pre>';
//
?>

<div id="add-dialog">
    <form method="post" >
        <span>Keyword:</span><input type="text" name="keyword" class="to" style="width: 260px;" value=""><br>
<!--        <span>Collection:</span><input type="text" name="collection" class="to" style="width: 260px;" value=""><br>-->

        <span>Collection:</span>
        <select name="collection" style="width: 260px;">
            <?php
            foreach ($collection as $v) {
                echo '<option value="' . $v['name'] . '">' . $v['name'] . '</option>';
            }
            ?>
        </select>

        <span>Section:</span>
        <select name="section" style="width: 260px;">
            <?php
            foreach ($sections as $k => $v) {
                echo '<option value="' . $k . '">' . $k . '</option>';
            }
            ?>
        </select>
        <br></form>
</div>
