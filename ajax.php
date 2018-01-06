<?php
if (file_exists(dirname(__FILE__) . '/application/config/database-local.php')) {
    mysql_connect('master-aurora-new-cluster.cluster-ciayufran1ab.us-east-1.rds.amazonaws.com', 'fsmaster', 'FSdbm6512');
    mysql_select_db('fsmaster-nfstage');
} else {
    if ($_SERVER['environment'] == 'production') {
        mysql_connect('master-aurora-new-cluster.cluster-ciayufran1ab.us-east-1.rds.amazonaws.com', 'fsmaster', 'FSdbm6512');
        mysql_select_db('fsmaster-production');
    } elseif ($_SERVER['environment'] == 'staging') {
        mysql_connect('master-aurora-new-cluster.cluster-ciayufran1ab.us-east-1.rds.amazonaws.com', 'fsmaster', 'FSdbm6512');
        mysql_select_db('fsmaster-nfstage');
    }

}

$action = $_REQUEST['action'];

switch ($action) {
    case 'getUserKeywords':
        getUserKeywords($_REQUEST['sectionName'], $_REQUEST['userid'], $_REQUEST['currentVal']);
        break;
    case 'addUserKeywords':
        addUserKeywords($_REQUEST['sectionName'], $_REQUEST['userid'], $_REQUEST['keyword'], $_REQUEST['currentVal']);
        break;
    case 'deleteUserKeywords':
        deleteUserKeywords($_REQUEST['clipId']);
        break;
    case 'getSelectedKeywordsOrSet':
        getSelectedKeywordsOrSet($_REQUEST['setId']);
        break;
    case 'addUserKeywordsOldie':
        addUserKeywordsOldie($_REQUEST['sectionName'], $_REQUEST['userid'], $_REQUEST['keyword']);
        break;
    case 'addUserKeywordsOldieTemplate':
        addUserKeywordsOldieTemplate($_REQUEST['sectionName'], $_REQUEST['userid'], $_REQUEST['keyword'], $_REQUEST['templateId']);
        break;
    case 'deleteuserKeyword':
        deleteuserKeyword($_REQUEST['keywordId']);
        break;
    case 'deleteTempLoggingTemplate':
        deleteTempLoggingTemplate($_REQUEST['templateId'], $_REQUEST['cehckNot']);
        break;
    case 'keyword_search':
        getKeywordResult();
        break;
    case 'getPrevClipIds':
        getPrevClipData($_REQUEST['clipid']);
        break;
    case 'moveClipOnlineOffline':
        moveClipOnlineOffline($_REQUEST['clipid'], $_REQUEST['status']);
        break;
    case "like":
        $user_login = $_POST['user_login_id'];
        $getUserById = "select * from lib_users where id='" . $user_login . "'";
        $res = mysql_query($getUserById) or die(mysql_error());
        $run = mysql_fetch_array($res, MYSQL_ASSOC);

        $userID = $run['id'];
        $group_id = $run['group_id'];

        if ($group_id == 1) {
            $query = "insert into lib_clip_rating (user_id,name,description,code,weight) values('" . $userID . "','admin_rating','Rating by Admin','" . $_POST['id'] . "','" . get_setting('adminrating') . "')";
            $admin_rating = mysql_query($query);
            $data1 = mysql_insert_id();
            $count_likes_admin = "select * from lib_clip_rating where code='" . $_POST['id'] . "' and (name='user_rating' or name='admin_rating' or name='ip_rating' or name='contributer_rating')";
            $result = mysql_query($count_likes_admin) or die(mysql_error());
            $count_total_admin = mysql_num_rows($result);
            $data = $count_total_admin;
        } elseif ($group_id == 13) {
            $query = "insert into lib_clip_rating (user_id,name,description,code,weight) values('" . $userID . "','contributer_rating','Rating by Contributer','" . $_POST['id'] . "','" . get_setting('contributerRating') . "')";
            $admin_rating = mysql_query($query);
            $data1 = mysql_insert_id();
            $count_likes_admin = "select * from lib_clip_rating where code='" . $_POST['id'] . "' and (name='user_rating' or name='admin_rating' or name='ip_rating' or name='contributer_rating')";
            $result = mysql_query($count_likes_admin) or die(mysql_error());
            $count_total_admin = mysql_num_rows($result);
            $data = $count_total_admin;
        } else {
            $query_client = "insert into lib_clip_rating (user_id,name,description,code,weight) values('" . $userID . "','user_rating','Rating by Registered User','" . $_POST['id'] . "','" . get_setting('registeredUser') . "')";
            $user_rating = mysql_query($query_client) or die(mysql_error());
            $data1 = mysql_insert_id();
            $count_likes_user = "select * from lib_clip_rating where code='" . $_POST['id'] . "' and (name='user_rating' or name='admin_rating' or name='ip_rating' or name='contributer_rating')";
            $result = mysql_query($count_likes_user);
            $count_total_admin = mysql_num_rows($result);
            $data = $count_total_admin;
        }
        echo $data . "," . $data1;
        break;
    case "unlike":
        $clip_id = $_POST['clip_id'];
        $query = "DELETE FROM lib_clip_rating WHERE id = '" . $_POST["id"] . "'";
        $result = mysql_query($query);
        $count_likes_admin = "select * from lib_clip_rating where code='" . $clip_id . "' and (name='ip_rating' or name='user_rating' or name='admin_rating' or name='contributer_rating')";
        $count_run = mysql_query($count_likes_admin);
        $count_total_admin = mysql_num_rows($count_run);
        $data = $count_total_admin;
        echo $data;
        break;
    case 'getKeywordMust':
        getKeywordMust($_REQUEST['sectionName'], $_REQUEST['keywordName']);
        break;
    case 'getKeywordNotMust':
        getKeywordNotMust($_REQUEST['sectionName'], $_REQUEST['keywordName']);
        break;
    case 'disableKeyword':
        disableKeyword($_REQUEST['keywordId'], $_REQUEST['userid']);
        break;
    case 'enableKeyword':
        enableKeyword($_REQUEST['keywordId'], $_REQUEST['userid']);
        break;
    case 'disableKeywordLogging':
        disableKeywordLogging($_REQUEST['keywordId'], $_REQUEST['userid'], $_REQUEST['templateId']);
        break;
    case 'enableKeywordLogging':
        enableKeywordLogging($_REQUEST['keywordId'], $_REQUEST['userid'], $_REQUEST['templateId']);
        break;
    case 'getKeywordTemplateData':
        getKeywordTemplateData($_REQUEST['templateId']);
        break;
    case 'updateTemplateData':
        updateTemplateData($_REQUEST['templateId'], $_REQUEST['arrayUpdateList']);
        break;
    case 'getMetaDataDemplate':
        getMetaDataDemplate($_REQUEST['templateId']);
        break;
    case 'getMetaDataDemplateLatestUser':
        getMetaDataDemplateLatestUser($_REQUEST['userid']);
        break;
    case 'getClipDescription':
        getClipDescription($_REQUEST['clipId']);
        break;
    default:
        echo 'Invalid Request';
        break;
}

function getUserKeywords($section, $userId, $currentVal = NULL)
{

    $query = "SELECT * FROM lib_keywords WHERE section='" . $section . "' AND provider_id = '" . $userId . "'";
    $result = mysql_query($query) or die(mysql_error());

    $checkArray = explode(',', $currentVal);
    while ($row = mysql_fetch_assoc($result)) {
        ?>


        <div class="item" data-keywordid="<?php echo $row["id"]; ?>"><label class="checkbox"
                                                                            title="<?php echo $row["keyword"]; ?>">
                <input type="hidden" name="<?php echo $row["id"]; ?>" value="Aerial"
                       class="cliplog_keyword_checkbox"><?php echo $row["keyword"]; ?></label>
            <div class="switch-cont">
                <div class="switch has-switch" data-animated="false" data-on-label="" data-off-label="">
                    <div class="switch-on"><input type="checkbox" checked="" value="69737"><span
                            class="switch-left"></span><label>&nbsp;</label><span class="switch-right"></span></div>
                </div>
            </div>
        </div>


        <!-- <div class="newCrossDel<?php echo $row["id"]; ?>" style="float: left">
            <input type="checkbox" value="<?php
        echo 'temp_' . $row["id"];
        ?>" name="userKeywords[]" class="checkBoxId" data-sect="<?php echo $section; ?>"
                   data-text="<?php echo $row["keyword"]; ?>"
                <?php if (in_array('temp_' . $row["id"], $checkArray)) { ?> checked="checked"   <?php } ?>>
            <div class="newCross" onclick="if (confirm('Want to delete?'))
                deleteData('<?php echo $row["id"]; ?>');">
                <div class="newCrossInner">
                    <?php
        echo $row["keyword"];
        ?>
                </div>
            </div>
        </div>
        !-->
        <?php
    }
    ?>
    <br clear="all">
    <?php
    // $query = "SELECT * FROM lib_keywords WHERE collection='Nature Footage'";
    // $result = mysql_query($query) or die(mysql_error());

    // $checkArray = explode(',', $currentVal);
    // while ($row = mysql_fetch_assoc($result)) {
    ?>
    <!--        <input type="checkbox" value="--><?php
    //        echo 'temp_' . $row["id"];
    //
    ?><!--" name="userKeywords[]" class="checkBoxId" data-sect="--><?php //echo $section;
    ?><!--"-->
    <!--               data-text="--><?php //echo $row["keyword"];
    ?><!--"-->
    <!--            --><?php //if (in_array('temp_' . $row["id"], $checkArray)) {
    ?><!-- checked="checked"   --><?php //}
    ?><!-->
    <?php
    //   echo $row["keyword"];
    // }
}

function addUserKeywords($section, $userId, $keyword, $currentVal = NULL)
{

    $query = "INSERT INTO lib_user_keywords SET section_id='" . $section . "', provider_id = '" . $userId . "',keyword ='" . $keyword . "'";
    $result = mysql_query($query) or die(mysql_error());
    $insertId = mysql_insert_id();

    $queryGet = "SELECT * FROM lib_user_keywords WHERE id='" . $insertId . "' ";
    $resultGet = mysql_query($queryGet) or die(mysql_error());

    $checkArray = explode(',', $currentVal);
    while ($rowGet = mysql_fetch_assoc($resultGet)) {
        ?>

        <div class="newCrossDel<?php echo $rowGet["id"]; ?>" style="float:left">
            <input type="checkbox" value="<?php
            echo 'temp_' . $rowGet["id"];
            ?>" name="userKeywords[]" class="checkBoxId" data-sect="<?php echo $section; ?>"
                   data-text="<?php echo $rowGet["keyword"]; ?>"
                <?php if (in_array('temp_' . $rowGet["id"], $checkArray)) { ?> checked="checked"   <?php } ?>>

            <div class="newCross" onclick="if (confirm('Want to delete?'))
                deleteData('<?php echo $rowGet["id"]; ?>');">
                <div class="newCrossInner ">
                    <?php
                    echo $rowGet["keyword"];
                    ?>
                </div>
            </div>
        </div>
        <?php
    }
}

function addUserKeywordsOldie($section, $userId, $keyword)
{
    $query = "INSERT INTO lib_keywords SET section='" . $section . "', provider_id = '" . $userId . "',keyword ='" . mysql_real_escape_string($keyword) . "', hidden='1'";
    $result = mysql_query($query) or die(mysql_error());
    $insertId = mysql_insert_id();
    return $insertId;
}

function addUserKeywordsOldieTemplate($section, $userId, $keyword, $templateId)
{
    $query = "INSERT INTO lib_keywords SET section='" . $section . "', provider_id = '" . $userId . "',keyword ='" . mysql_real_escape_string($keyword) . "', hidden='1'";
    $result = mysql_query($query) or die(mysql_error());
    $insertId = mysql_insert_id();

    $query = "INSERT INTO lib_cliplog_logging_keywords SET keywordId='" . $insertId . "', templateId = '" . $templateId . "',isActive ='1'";
    $result = mysql_query($query) or die(mysql_error());
    $insertId = mysql_insert_id();

    return $insertId;
}

function deleteUserKeywords($delUserId)
{
    $queryDel = "DELETE FROM lib_user_keywords WHERE id='" . $delUserId . "' ";
    mysql_query($queryDel) or die(mysql_error());
    return true;
}

function deleteuserKeyword($keywordId)
{
    $queryDel = "DELETE FROM lib_keywords WHERE id ='" . $keywordId . "'";
    mysql_query($queryDel) or die(mysql_error());
    return true;
}

function deleteTempLoggingTemplate($templateId, $cehckNot = NULL)
{

    $queryGet = "SELECT * FROM lib_cliplog_logging_templates WHERE owner_id ='" . $templateId . "' AND name='tempUserTemplate'";
    $resultGet = mysql_query($queryGet) or die(mysql_error());

    while ($rowGet = mysql_fetch_array($resultGet)) {
        if ($rowGet['id'] != $cehckNot) {
            $queryDel = "DELETE FROM lib_cliplog_logging_templates WHERE id ='" . $rowGet['id'] . "' ";
            mysql_query($queryDel) or die(mysql_error());

            $queryDel2 = "DELETE FROM lib_cliplog_logging_keywords WHERE templateId ='" . $rowGet['id'] . "' ";
            mysql_query($queryDel2) or die(mysql_error());
        }
    }
    return $templateId;
}

function getSelectedKeywordsOrSet($setId)
{
    $queryF = "SELECT * FROM lib_cliplog_metadata_templates WHERE id = '" . $setId . "'";
    $result = mysql_query($queryF) or die(mysql_error());
    $data = mysql_fetch_array($result);
    $new_arr = json_decode($data['json']);
    foreach ($new_arr->keywords_saved as $value) {
        $query = "SELECT * FROM lib_keywords WHERE id = '" . $value . "'";
        $result2 = mysql_query($query) or die(mysql_error());
        $data3 = mysql_fetch_array($result2);
        ?>
        <span class="<?php echo $data3['section']; ?>">
            <div class="item-wrapper ">
                <a class="item-cross"></a>
                <div class="item"><input type="hidden" checked="checked" value="<?php echo $data3['id']; ?>"
                                         name="keywords[<?php echo $data3['id']; ?>]"
                                         datavalue-text="<?php echo $data3['keyword']; ?>"><?php echo $data3['keyword']; ?>
                </div>
            </div>
        </span><br>
        <?php
    }
}

function getKeywordResult()
{
    $keyword = addslashes($_POST['keyword']);
    $clip_id = $_POST['clip_id'];
    $section_name = $_POST['section_search_name'];
    if (!empty($keyword)) {
        //and collection='Nature Footage'
        $queryS = "select * from lib_keywords_front where keyword like '%$keyword%'";
        $result = mysql_query($queryS) or die(mysql_error());
        //$data = mysql_fetch_array($result);
        while ($new_arr = mysql_fetch_array($result)) {
            $section_name_arr = str_replace($_POST['keyword'], '<b>' . $_POST['keyword'] . '</b>', $new_arr['keyword']);
            echo '<li onclick="set_item(\'' . $new_arr['keyword'] . '\',\'' . $section_name . '\')">' . $section_name_arr . '</li>';
        }
    }
}

function getPrevClipData($clipId)
{

    $finalArrReturn = array();
    $arrtoReturn = array();
    $i = 0;

    $queryS = "select * from lib_clips where id = " . $clipId . " ";
    $result = mysql_query($queryS) or die(mysql_error());
    $row = mysql_fetch_assoc($result);
    $finalArrReturn['clipData'] = $row;

    //Calculation for Film Month and Year START
    $arrayFilmDate = explode('-', $row['film_date']);
    $finalArrReturn['clipData']['film_year'] = $arrayFilmDate[0];
    if ($arrayFilmDate[1] >= 10) {
        $finalArrReturn['clipData']['film_month'] = $arrayFilmDate[1];
    } else {
        $finalArrReturn['clipData']['film_month'] = substr($arrayFilmDate[1],1);
    }
    //Calculation for Film Month and Year END


    $queryS = "select * from lib_clips_keywords where clip_id = " . $clipId . " ";
    $result = mysql_query($queryS) or die(mysql_error());

    while ($row = mysql_fetch_array($result)) {

        if ($row['section_id'] == 'category') {
            $finalArrReturn['clipData']['category'] = $row['keyword'];
        } elseif ($row['section_id'] == 'country') {
            $finalArrReturn['clipData']['country'] = $row['keyword'];
        } else {
            $arrtoReturn[$i]['keyword'] = $row['keyword'];
            $arrtoReturn[$i]['section_id'] = $row['section_id'];
            $i++;
        }


    }

    $finalArrReturn['keywords'] = $arrtoReturn;

    echo json_encode($finalArrReturn);
}

function get_setting($name)
{
    $query = "SELECT value FROM lib_settings WHERE name = '" . $name . "'";
    $result = mysql_query($query) or die(mysql_error());

    if (mysql_num_rows($result) > 0) {
        $row = mysql_fetch_array($result);
        $result = $row['value'];
    }

    return $result;
}

function getKeywordMust($section, $keyword)
{
    $query = "SELECT keyword2 FROM lib_logging_rules WHERE section_id = '" . $section . "' AND keyword1 = '" . $keyword . "' AND rule = 1";
    $result = mysql_query($query) or die(mysql_error());
    $arrtoReturn = array();
    while ($row = mysql_fetch_array($result)) {
        array_push($arrtoReturn, $row['keyword2']);
    }

    echo json_encode($arrtoReturn);
}

function getKeywordNotMust($section, $keyword)
{
    $query = "SELECT keyword2 FROM lib_logging_rules WHERE section_id = '" . $section . "' AND keyword1 = '" . $keyword . "' AND rule = 0";
    $result = mysql_query($query) or die(mysql_error());
    $arrtoReturn = array();
    while ($row = mysql_fetch_array($result)) {
        array_push($arrtoReturn, $row['keyword2']);
    }

    echo json_encode($arrtoReturn);
}

function moveClipOnlineOffline($clip_id, $status)
{

    $hiddenProducts = explode(',', $clip_id);

    if (is_array($hiddenProducts)) {
        foreach ($hiddenProducts as $cliPId) {
            $queryUpdateClipStatus = "UPDATE lib_clips SET active = '" . $status . "' WHERE id ='" . $cliPId . "' ";
            mysql_query($queryUpdateClipStatus) or die(mysql_error());
        }
    } else {

        $queryUpdateClipStatus = "UPDATE lib_clips SET active = '" . $status . "' WHERE id ='" . $clip_id . "' ";
        mysql_query($queryUpdateClipStatus) or die(mysql_error());
    }

    return true;
}

function disableKeyword($keywordId, $userid)
{
    $query = "SELECT * FROM lib_keywords WHERE id='" . $keywordId . "' ";
    $result = mysql_query($query) or die(mysql_error());
    $row = mysql_fetch_array($result);

    if ($userid == $row['provider_id']) {

        $queryUpdateKeywordStatus = "UPDATE lib_keywords SET hidden = '0' WHERE id ='" . $keywordId . "' ";
        mysql_query($queryUpdateKeywordStatus) or die(mysql_error());

    } else {
        $checkqueryUpdateKeywordStatus = "SELECT * FROM lib_keywords_notvisible WHERE keyword_id = '" . $keywordId . "' AND user_id ='" . $userid . "' ";
        $result = mysql_query($checkqueryUpdateKeywordStatus) or die(mysql_error());

        if (mysql_num_rows($result) <= 0) {
            $queryUpdateKeywordStatus = "INSERT INTO  lib_keywords_notvisible SET keyword_id = '" . $keywordId . "' , user_id ='" . $userid . "' ";
            mysql_query($queryUpdateKeywordStatus) or die(mysql_error());
        }
    }
}

function enableKeyword($keywordId, $userid)
{
    $query = "SELECT * FROM lib_keywords WHERE id='" . $keywordId . "' ";
    $result = mysql_query($query) or die(mysql_error());
    $row = mysql_fetch_array($result);

    if ($userid == $row['provider_id']) {
        $queryUpdateKeywordStatus = "UPDATE lib_keywords SET hidden = '1' WHERE id ='" . $keywordId . "' ";
        mysql_query($queryUpdateKeywordStatus) or die(mysql_error());
    } else {
        $checkqueryUpdateKeywordStatus = "DELETE FROM lib_keywords_notvisible WHERE keyword_id = '" . $keywordId . "' AND user_id ='" . $userid . "' ";
        $result = mysql_query($checkqueryUpdateKeywordStatus) or die(mysql_error());


    }
}

function disableKeywordLogging($keywordId, $userid, $templateId)
{


    $queryUpdateLogging = "UPDATE lib_cliplog_logging_keywords SET isActive=0 WHERE keywordId='" . $keywordId . "' AND templateId ='" . $templateId . "'";
    mysql_query($queryUpdateLogging) or die(mysql_error());


    $query = "SELECT * FROM lib_keywords WHERE id='" . $keywordId . "' ";
    $result = mysql_query($query) or die(mysql_error());
    $row = mysql_fetch_array($result);

    if ($userid != $row['provider_id']) {

        $checkqueryUpdateKeywordStatus = "SELECT * FROM lib_keywords_notvisible WHERE keyword_id = '" . $keywordId . "' AND user_id ='" . $userid . "' ";
        $result = mysql_query($checkqueryUpdateKeywordStatus) or die(mysql_error());

        if (mysql_num_rows($result) <= 0) {
            $queryUpdateKeywordStatus = "INSERT INTO  lib_keywords_notvisible SET keyword_id = '" . $keywordId . "' , user_id ='" . $userid . "' ";
            mysql_query($queryUpdateKeywordStatus) or die(mysql_error());
        }
    }
}

function enableKeywordLogging($keywordId, $userid, $templateId)
{
    $queryUpdateLogging = "UPDATE lib_cliplog_logging_keywords SET isActive=1 WHERE keywordId='" . $keywordId . "'  AND templateId ='" . $templateId . "'";
    mysql_query($queryUpdateLogging) or die(mysql_error());

    $query = "SELECT * FROM lib_keywords WHERE id = '" . $keywordId . "' ";
    $result = mysql_query($query) or die(mysql_error());
    $row = mysql_fetch_array($result);

    if ($userid != $row['provider_id']) {
        $checkqueryUpdateKeywordStatus = "DELETE FROM lib_keywords_notvisible WHERE keyword_id = '" . $keywordId . "' AND user_id = '" . $userid . "' ";
        $result = mysql_query($checkqueryUpdateKeywordStatus) or die(mysql_error());


    }
}

function getKeywordTemplateData($templateId)
{

    $q = "SELECT * FROM lib_cliplog_logging_templates WHERE id ='" . $templateId . "'";
    $result = mysql_query($q) or die(mysql_error());
    $row = mysql_fetch_array($result);
    echo $row['json'];
}

function updateTemplateData($templateId, $dataLists)
{

    $q = "SELECT * FROM lib_cliplog_logging_templates WHERE id ='" . $templateId . "'";
    $result = mysql_query($q) or die(mysql_error());
    $row = mysql_fetch_array($result);
    $data = json_decode($row['json']);
    $data->keywords_sections_hide_lists = implode(',', $dataLists);

    $dataInput = json_encode($data);

    $q = "UPDATE lib_cliplog_logging_templates SET json ='" . $dataInput . "'  WHERE id ='" . $templateId . "'";
    $result = mysql_query($q) or die(mysql_error());
}

function getMetaDataDemplate($templateId)
{

    $q = "SELECT * FROM lib_cliplog_metadata_templates WHERE id ='" . $templateId . "'";
    $result = mysql_query($q) or die(mysql_error());
    $row = mysql_fetch_array($result);

    $data = json_decode($row['json']);
    echo $row['json'];
    // echo json_decode( $data->sections_values);


}

function getMetaDataDemplateLatestUser($userid)
{

    $q = "SELECT * FROM lib_cliplog_metadata_templates WHERE owner_id ='" . $userid . "' ORDER BY id DESC LIMIT 1";
    $result = mysql_query($q) or die(mysql_error());
    $row = mysql_fetch_array($result);

    $data = json_decode($row['json']);
    echo $row['json'];
    // echo json_decode( $data->sections_values);


}

function getClipDescription($clipId)
{

    $q = "SELECT description FROM lib_clips WHERE id ='" . $clipId . "' ";
    $result = mysql_query($q) or die(mysql_error());
    $row = mysql_fetch_array($result);
    echo utf8_encode($row['description']);
    // echo json_decode( $data->sections_values);


}

