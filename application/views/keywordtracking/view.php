<script type="text/javascript" src="data/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/s/dt/dt-1.10.10/datatables.min.css"/>

<script>
    $(document).ready(function() {
        $('#example').DataTable({
            "pageLength": 20

                    // "aoColumnDefs": [
                    //  {'bSortable': false, 'aTargets': [0, 4]}
                    // ]
        });

    });
</script>
<form name="filter" action="<?php echo $lang; ?>/keywordtracking/view" id="form_filter" method="POST">
    <input type="hidden" name="filter[hidden]" value="1" />
    <table>
        <tr>
            <td>
                <input type="text" name="filter" placeholder="Keyword" value="<?php
                if (isset($result['keyword'])) {
                    echo $result['keyword'];
                }
                ?>" />
            </td>
            <td>
                <input type="text" name="dateFrom" class="datePicker" placeholder="Date From" value="" />
            </td>
            <td>
                <input type="text" name="dateTo" class="datePicker" placeholder="Date To" value="<?php echo date("Y-m-d"); ?>"  />
            </td>
<!--            <td>
                <input type="text" name="filter[section]" placeholder="Section" value="<?php //if (isset($filter['section'])) echo $filter['section'];                                                           ?>" />
            </td>-->
            <td>
                <!--onclick="filter.submit();"-->
                <input type="submit" class="btn" name="submit" value="Apply filter">
                <!--                <a class="btn" onclick="$('input[name=filter]').each(function() {
                                            $(this).attr('value', '');
                                        });
                                        filter.submit();">Clear</a>-->
            </td>
        </tr>
    </table>
</form>




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
    <table <?php if (sizeof($result) > 0) { ?>id="example"<?php } ?> class="table table-striped">
        <thead>
            <tr>

                <th>Keyword</th>
                <th>Section</th>
    <!--            <th width="120" align="center">Time Created</th>-->
            </tr>
        </thead>
        <tbody>
            <?php
            // echo '<pre>';
            //print_r($result);
            // echo '</pre>';
            $resultArr = array();


            if (!empty($result)) {
                foreach ($result as $row) {
                    $varARR = explode(",", $row['primary_subject']);
                    foreach ($varARR as $value) {
                        if (!in_array($value, $resultArr) && $value != '') {
                            array_push($resultArr, $value);
                        }
                    }
                }
            }


            if (!empty($resultArr)) {
                $resultArr = array_unique($resultArr);

                foreach ($resultArr as $row) {
                    //if ($row['section_id'] == 'primary_subject') {
                    ?>
                    <tr>
                        <td><?php echo $row; ?></td>
                        <td><?php echo 'Primary Subject'; //echo $row['section_id'];                                  ?></td>
            <!--                    <td><?php echo $row ?></td>-->
                    </tr>
                    <?php
                    //  }
                }
            } else {
                ?>
                <tr>
                    <td colspan="2"><?php echo "No Record Found!" ?></td>
                </tr>
            <?php }
            ?>


            </tr>
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
<style>

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
<script>
    $('.datePicker').datepicker({
        format: 'yyyy-mm-dd'
    })
</script>