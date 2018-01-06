//var restServiceURL = '/index.php?ajax=true&action=node_api';

var jQueryuploadContainer = "allUploadTransfers";
var jQuerydownloadContainer = "allDownloadTransfers";
var currentPath = homePath;

var addToTable = function (container, name, transferSpec, connectSettings) {
    var subcontainer = document.createElement('tr');
    subcontainer.setAttribute('id', name);

    /********************************************************/
    var bar = document.createElement('div');
    bar.setAttribute('id','p_' + name);
    bar.setAttribute('class',"easyui-progressbar progressbar");
    bar.setAttribute('style','text-align:center; width: 100%; align:center;');

    var barTh = document.createElement('th');
    barTh.setAttribute('id','thp_' + name);
    //barTh.setAttribute('class',"nobg");

    /******************************************************/
    var text = document.createElement('div');
    text.setAttribute('id','file_name_' + name);

    var textTh = document.createElement('th');
    textTh.setAttribute('id','thfile_name_' + name);


    /*****************************************************/
    var pause = document.createElement('input');
    pause.type = 'button';
    pause.setAttribute('id','pause_' + name);
    pause.setAttribute('name','Pause');
    pause.setAttribute('value','Pause');
    pause.setAttribute('class','btn btn-primary');

    var remove = document.createElement('input');
    remove.type = 'button';
    remove.setAttribute('id','remove_' + name);
    remove.setAttribute('value','Remove');
    remove.setAttribute('class','btn btn-danger');

//    var tsjson = document.createElement('input');
//    tsjson.type = 'button';
//    tsjson.setAttribute('id','tsjson_' + name);
//    tsjson.setAttribute('value','Show TransferSpec');
//
//    var resjson = document.createElement('input');
//    resjson.type = 'button';
//    resjson.setAttribute('id','resjson_' + name);
//    resjson.setAttribute('value','Show Progress JSON');

    var buttonTh = document.createElement('th');
    buttonTh.setAttribute('id','thbutton_' + name);
    //buttonTh.setAttribute('class',"nobg");

    /********** update the DOM **********************/
    document.getElementById(container).appendChild(subcontainer);
    jQuery('#'+name).append(barTh);
    jQuery('#'+name).append(textTh);
    jQuery('#'+name).append(buttonTh);
    jQuery('#thp_'+name).append(bar);
    jQuery('#thfile_name_'+name).append(text);
    jQuery('#thbutton_'+name).append(pause);
    jQuery('#thbutton_'+name).append(remove);
    //jQuery('#thbutton_'+name).append(resjson);
    //jQuery('#thbutton_'+name).append(tsjson);

    /****************** start hidden *****************/
    var jsonTS = document.createElement('span');
    jsonTS.setAttribute('id','json_' + name);
    jsonTS.setAttribute('style','display:none');

    var jsonResult = document.createElement('span');
    jsonResult.setAttribute('id','jresult_' + name);
    jsonResult.setAttribute('style','display:none');

    var span = document.createElement('span');
    span.setAttribute('id','span_' + name);
    span.setAttribute('style','display:none');

    jQuery('#'+name).append(jsonTS);
    jQuery('#'+name).append(jsonResult);
    jQuery('#'+name).append(span);
    /****************** end hidden *****************/

    /***************** button functions ***********/
    jQuery("#pause_"+name).click(function(e) {
        if ( jQuery(this).val() === "Pause" ) {
            var res = xferControls.stopTransfer(jQuery('#span_'+name).text());
            if(res) {
                jQuery( this ).val('Resume');
            }
        } else {
            var res = xferControls.resumeTransfer(jQuery('#span_'+name).text());
            if(res) {
                jQuery( this ).val('Pause');
            }
        }
        e.preventDefault();
    });
    jQuery("#remove_"+name).click(function(e) {
        xferControls.cancelTransfer(name, jQuery('#span_'+name).text());
        e.preventDefault();
    });
//    jQuery("#tsjson_"+name).click(function(e) {
//        if( jQuery(this).val() === "Show TransferSpec" ) {
//            xferControls.showTransferSpecJSON(name);
//            jQuery( this ).val('Hide TransferSpec');
//        } else {
//            xferControls.hide(jQuery('#transfer_spec'));
//            jQuery( this ).val('Show TransferSpec');
//        }
//        e.preventDefault();
//    });
//    jQuery("#resjson_"+name).click(function(e) {
//        if( jQuery(this).val() === "Show Progress JSON" ) {
//            xferControls.showResultJSON(name);
//            jQuery( this ).val('Hide Progress JSON');
//        } else {
//            xferControls.hide(jQuery('#progress_json'));
//            jQuery( this ).val('Show Progress JSON');
//        }
//        e.preventDefault();
//    });

    jQuery("#p_" + name).progressbar({'value':0});
    jQuery("#json_" + transferSpec.cookie).text("transfer_spec " + JSON.stringify(transferSpec, null, 4) +
        " connect_settings " + JSON.stringify(connectSettings, null, 4));
};

requestToNode = function(servicePath, params, callbacks) {

    if (servicePath === null)
        return;

    var restRequestJsonParams = {};
    restRequestJsonParams.path = servicePath;
    if(params !== null)
        restRequestJsonParams.params = JSON.stringify(params);


    jQuery.ajax({
        type: 'POST',
        url: restServiceURL,
        dataType : "json",
        data : restRequestJsonParams,
        cache : false,
        success : function(data) {
            if(callbacks !== null && callbacks.success !== null)
                callbacks.success(data);
        },
        error : function(jqXHR, textStatus) {
            if(callbacks !== null && callbacks.error !== null)
                callbacks.error(textStatus);
        }
    });
};

function uploadNotification(paths, destination_root) {
    jQuery.ajax({
        type: 'POST',
        url: 'en/file_manager/upload_notification',
        dataType : "json",
        data : {paths: paths, destination_root: destination_root},
        cache : false
    });
}

addTableHeader = function (id) {
    var html = '<tr><th><input type="checkbox" onclick="changeSelection(this);"></th><th>Type</th><th>Base name</th><th>Size</th><th>Modification time</th></tr>';
    jQuery('#' + id).append(html);
}

addParentLink = function (id, parentPath) {
    if(parentPath){
        var html = '';
        html += '<tr onclick="browse(\'' + parentPath +'\')">';
        html += '<td colspan="4"><img src="/data/img/admin/trackback.png"></td>';
        html += '</tr>';
        jQuery('#' + id).append(html);
    }
}

addResultToTable = function(id, item){
    var itemHtml = '';
    if(item && item['basename'] != '.cache'){
        if(item['type'] == 'file'){
            itemHtml += '<tr data-type="' + item['type'] + '" data-path="' + item['path'] + '">';
            itemHtml += '<td><input type="checkbox" name="downloads_item"></td>';
            itemHtml += '<td><img src="/data/img/admin/file.png"></td>';
            itemHtml += '<td>' + item['basename'] + '</td>';
            itemHtml += '<td>' + item['size'] + '</td>';
        }
        else{
            itemHtml += '<tr data-type="' + item['type'] + '" data-path="' + item['path'] + '">';
            itemHtml += '<td><input type="checkbox" name="downloads_item"></td>';
            itemHtml += '<td><img class="browse_icon" src="/data/img/admin/folder.png" onclick="browse(\'' + item['path'] +'\')"></td>';
            itemHtml += '<td>' + item['basename'] + '</td>';
            itemHtml += '<td>&nbsp;</td>';
        }

        itemHtml += '<td>' + new Date(Date.parse(item['mtime'])) + '</td>';
        itemHtml += '</tr>';
    }
    jQuery('#' + id).append(itemHtml);
}

getSelectedDownloads = function(){
    var checked = jQuery('input[name=downloads_item]:checked');
    var downloads = [];
    checked.each(function(){
        var checkedTr = jQuery(this).parents('tr');
        if(checkedTr.attr('data-type') && checkedTr.attr('data-path')){
            //if(checkedTr.attr('data-type') == 'file'){
                downloads.push({path: checkedTr.attr('data-path'), type: checkedTr.attr('data-type')})
            //}
        }
    });
    return downloads;
}

var browse = function(path) {
    var parentPath = '';
    if(path && path != homePath){
        var pathParts = path.split('/');
        parentPath = (pathParts[pathParts.length - 2] != undefined) ? '/' + pathParts[pathParts.length - 2] : '';
    }
    var params = {};
    params.path = path ? path : homePath;

    currentPath = params.path;
//    params.sort = "size_d";
//    params.filters={};
//    params.filters.mtime_min = "2012-03-15 15:09:00";
//    params.filters.types = [];
//    params.filters.types.push("file");
//    params.filters.types.push("directory");

    var callbacks = {
        error : function (status) {
            alert(status);
        },
        success : function (data) {
            var items = data.items;
            var i = 0;
            jQuery("#dirList").find("tr").remove();
            addTableHeader('dirList');
            addParentLink('dirList', parentPath);
            jQuery.each(items, function() {
                addResultToTable('dirList', this);
                i++;
            });
        }
    };
    requestToNode('/files/browse', params, callbacks);
}

var changeSelection = function(elem) {
    if(jQuery(elem).attr('checked'))
        jQuery('input[name=downloads_item]').attr('checked', true);
    else
        jQuery('input[name=downloads_item]').attr('checked', false);
}

fileControls = {};

fileControls.handleTransferEvents = function (event, obj) {
    switch (event) {
        case 'transfer':
            jQuery('#progressbar').progressbar({ value: Math.floor(obj.percentage * 100) });
            var jsonObj = eval(obj);
            //console.log(JSON.stringify(obj, null, "        "));

            var cookie = jsonObj.transfer_spec.cookie;
            jQuery('#p_'+cookie).progressbar('setValue', Math.floor(obj.percentage * 100));

            var info = obj.current_file;
            if(obj.status === "failed") {
                info = obj.title + ": " + obj.error_desc;
            } else if(obj.status === "completed") {
                jQuery("#pause_"+cookie).hide();
                info = obj.title;
                //console.log(obj);
                if(obj.transfer_spec.direction == 'send'){
                    uploadNotification(obj.transfer_spec.paths, obj.transfer_spec.destination_root);
                }
            }
            jQuery("#file_name_"+cookie).text(obj.transfer_spec.direction + " - " + info);

            jQuery("#jresult_"+cookie).text(JSON.stringify(obj, null, 4));
            jQuery('#span_'+cookie).text(obj.transfer_spec.tags.aspera.xfer_id);
            break;
    }
};

fileControls.transfer = function(transferSpec, connectSettings, token) {
    if (typeof token !== "undefined" && token !== "") {
        transferSpec.authentication="token";
        transferSpec.token=token;
    }

    asperaWeb.startTransfer(transferSpec, connectSettings,
        callbacks = {
            error : function(obj) {
                //console.log("Failed to start : " + JSON.stringify(obj, null, 4));
            },
//            success:function () {
////                var container;
////                var toggleG;
////                var download = true;
////                if(transferSpec.direction === "send") {
////                    download = false;
////                }
////
////                if(download) {
////                    container = jQuerydownloadContainer;
////                    toggleG = jQuery('#downloads_group');
////                } else {
////                    container = jQueryuploadContainer;
////                    toggleG = jQuery('#uploads_group');
////                }
////                //insert elements into table
////                addToTable(container, transferSpec.cookie, transferSpec, connectSettings);
////                toggleG.show();
//                //console.log("Started transfer : " + JSON.stringify(transferSpec, null, 4));
//            }

            success:function () {
                var container;
                var toggleG;
                var download = true;
                if(transferSpec.direction === "send") {
                    download = false;
                }

                if(download) {
                    container = jQuerydownloadContainer;
                    toggleG = jQuery('#downloads_group');
                } else {
                    container = jQueryuploadContainer;
                    toggleG = jQuery('#uploads_group');
                }
                //insert elements into table
                addToTable(container, transferSpec.cookie, transferSpec, connectSettings);
                toggleG.show();
                //console.log("Started transfer : " + JSON.stringify(transferSpec, null, 4));
            }
        });
};

fileControls.uploadFiles = function (pathsArray) {

    var params = {};
    params.transfer_requests = [];
    var transfer_request = {};
    transfer_request.paths = [];

    for (var i = 0, length = pathsArray.length; i < length; i +=1) {
        transfer_request.paths.push(
            {
                "source": pathsArray[i],
                "destination": ""
            }
        );
    }
    transfer_request.http_fallback = true;
    transfer_request.https_fallback = false;
    transfer_request.http_fallback_port = "8080";
    transfer_request.https_fallback_port = "8443";
    transfer_request.cookie = "u-"+new Date().getTime();
    transfer_request.aspera_connect_settings = {"xfer_retries":"100"};
    //transfer_request.destination_root = "/";
    transfer_request.destination_root = currentPath;

    params.transfer_requests.push({"transfer_request":transfer_request});

    var callbacks = {
        error : function (status) {
            //console.log(status);
        },
        success : function (data) {
            var transferSpec = data.transfer_specs[0].transfer_spec;
            transferSpec.remote_password = remotePassword;
//            //Use this if the server requires token authorization
//            transferSpec.authentication="token";
//            //console.log(JSON.stringify(transferSpec));
            var connectSettings = {"allow_dialogs": "no"};
            fileControls.transfer(transferSpec, connectSettings);
        }
    };
    requestToNode('/files/upload_setup', params, callbacks);
};

fileControls.selectFolder = function (downloads) {

    asperaWeb.showSelectFolderDialog(
        callbacks = {
            error : function(obj) {
                //console.log("Destination folder selection cancelled. Download cancelled.");
            },
            success:function (pathArray) {
                var destPath = null;
                if (!(pathArray == null || typeof pathArray === "undefined" || pathArray.length === 0)) {
                    destPath = pathArray[0];
                    //console.log("Destination folder for download: " + destPath);
                    fileControls.downloadFile(downloads, destPath);
                }
            }
        },
        //disable the multiple selection.
        options = {
            allowMultipleSelection : false,
            title : "Select Destination Folder"
        });
};

//fileControls.downloadFile = function (sourcePath, destPath, destBaseName) {
//    transferSpec = {
//        "paths": [{"source":sourcePath, "destination":destBaseName}],
//        "target": destPath,
//        "remote_host": "",
//        "remote_user": "",
//        "remote_password": "",
//        "direction": "receive",
//        "target_rate_kbps" : 5000,
//        "allow_dialogs" : true,
//        "resume" : "sparse_checksum"
//    };
//
//    connectSettings = {
//        "allow_dialogs": "no"
//    };
//
//    ///document.getElementById('transfer_spec').innerHTML =    JSON.stringify(transferSpec, null, "    ");
//
//    response = asperaWeb.startTransfer(transferSpec, connectSettings);
//};


fileControls.downloadFile = function (source, destinationPath) {
    transferSpec = {
        "paths": [],
        "remote_host": remoteHost,
        "remote_user": remoteUser,
        "remote_password": remotePassword,
        "direction": "receive",
        "target_rate" : 5000,
        "allow_dialogs" : true,
        "cookie" : "d-"+new Date().getTime(),
        "destination_root": destinationPath
    };

    if(Object.prototype.toString.call(source) == '[object String]'){
        var path;
        path = {"source":source};
        transferSpec.paths.push(path);
    }
    else{
        jQuery.each(source, function(key, item){
            transferSpec.paths.push({"source":item.path});
        });
    }

    connectSettings = {
        "allow_dialogs": false,
        "use_absolute_destination_path": true
    };


//    var tokenAuth = $('input[name=token_authorization]').is(':checked');
//    consoleLog("Need authorization token for download: " + tokenAuth);
//    if(tokenAuth) {
//        fileControls.getTokenBeforeTransfer(transferSpec, connectSettings, transferSpec.paths[0].source, true);
//    } else {
//        fileControls.transfer(transferSpec, connectSettings, "");
//    }

    //response = asperaWeb.startTransfer(transferSpec, connectSettings);

    fileControls.transfer(transferSpec, connectSettings, "");
};


var setup  = function () {
    this.asperaWeb = new AW.Connect({id:'aspera_web_transfers'});

    jQuery("#upload_files_button").click(function(e) {
        asperaWeb.showSelectFileDialog({success:fileControls.uploadFiles});
        e.preventDefault();
    });

    jQuery("#upload_directory_button").click(function(e) {
        asperaWeb.showSelectFolderDialog({success:fileControls.uploadFiles});
        e.preventDefault();
    });

    jQuery("#download_file_button").click(function(e) {
        //fileControls.downloadFile(this);
        var downloads = getSelectedDownloads();
        if(downloads.length > 0){
            fileControls.selectFolder(downloads);
        }
        e.preventDefault();
    });

    jQuery(document).on('click', 'input[name=downloads_item]', function(e) {
        jQuery(this).parents('tr').toggleClass('selected');
    });

//    jQuery(document).on('click', '#dirList tr', function(e) {
//        jQuery(this).toggleClass('selected');
//        if(jQuery(this).hasClass('selected'))
//            jQuery(this).find('input[name=downloads_item]').attr('checked', true);
//        else
//            jQuery(this).find('input[name=downloads_item]').attr('checked', false);
//    });

    this.asperaWeb.initSession("SimpleCombined" + new Date().getTime());
    this.asperaWeb.addEventListener('transfer', fileControls.handleTransferEvents);

    //to start, get all transfers and display them
    //getTransferEvents();

    browse();
};

xferControls = {};

xferControls.stopTransfer = function (transferId) {
    if(!(typeof transferId === 'undefined' || transferId == null)) {
        var result = asperaWeb.stopTransfer(transferId);
        //console.log("Paused transfer id : " + transferId + " " + JSON.stringify(result, null, 4));
        return (result == null || typeof result.error === 'undefined' || result.error === null );
    }
    return false;
};

xferControls.resumeTransfer = function (transferId) {
    if(!(typeof transferId === 'undefined' || transferId == null)) {
        var result = asperaWeb.resumeTransfer(transferId);
        //console.log("Resume transfer id : " + transferId + " " + JSON.stringify(result, null, 4));
        return (typeof result.error === 'undefined' || result.error === null );
    }
    return false;
};

xferControls.cancelTransfer = function (cookie, transferId) {
    if(!(typeof transferId === 'undefined' || transferId == null)) {
        var result = asperaWeb.removeTransfer(transferId);
        jQuery("#"+cookie).remove();
        //console.log("Removed transfer id : " + transferId + " " + JSON.stringify(result, null, 4));
    }
};

xferControls.showResultJSON = function (cookie) {
    document.getElementById('progress_json').innerHTML = jQuery("#jresult_"+cookie).text();
    jQuery('#progress_json').show();
};

xferControls.showTransferSpecJSON = function (cookie) {
    document.getElementById('transfer_spec').innerHTML = jQuery("#json_" + cookie).text();
    jQuery('#transfer_spec').show();
};

xferControls.hide = function (element) {
    element.hide();
};