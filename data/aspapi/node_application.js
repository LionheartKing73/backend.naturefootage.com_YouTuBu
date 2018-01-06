var restServiceURL = 'en/clips/node_api';

var $uploadContainer = "allUploadTransfers";
var $downloadContainer = "allDownloadTransfers";


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
    $('#'+name).append(barTh);
    $('#'+name).append(textTh);
    $('#'+name).append(buttonTh);
    $('#thp_'+name).append(bar);
    $('#thfile_name_'+name).append(text);
    $('#thbutton_'+name).append(pause);
    $('#thbutton_'+name).append(remove);
    //$('#thbutton_'+name).append(resjson);
    //$('#thbutton_'+name).append(tsjson);

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

    $('#'+name).append(jsonTS);
    $('#'+name).append(jsonResult);
    $('#'+name).append(span);
    /****************** end hidden *****************/

    /***************** button functions ***********/
    $("#pause_"+name).click(function(e) {
        if ( $(this).val() === "Pause" ) {
            var res = xferControls.stopTransfer($('#span_'+name).text());
            if(res) {
                $( this ).val('Resume');
            }
        } else {
            var res = xferControls.resumeTransfer($('#span_'+name).text());
            if(res) {
                $( this ).val('Pause');
            }
        }
        e.preventDefault();
    });
    $("#remove_"+name).click(function(e) {
        xferControls.cancelTransfer(name, $('#span_'+name).text());
        e.preventDefault();
    });
//    $("#tsjson_"+name).click(function(e) {
//        if( $(this).val() === "Show TransferSpec" ) {
//            xferControls.showTransferSpecJSON(name);
//            $( this ).val('Hide TransferSpec');
//        } else {
//            xferControls.hide($('#transfer_spec'));
//            $( this ).val('Show TransferSpec');
//        }
//        e.preventDefault();
//    });
//    $("#resjson_"+name).click(function(e) {
//        if( $(this).val() === "Show Progress JSON" ) {
//            xferControls.showResultJSON(name);
//            $( this ).val('Hide Progress JSON');
//        } else {
//            xferControls.hide($('#progress_json'));
//            $( this ).val('Show Progress JSON');
//        }
//        e.preventDefault();
//    });

    $("#p_" + name).progressbar({'value':0});
    $("#json_" + transferSpec.cookie).text("transfer_spec " + JSON.stringify(transferSpec, null, 4) +
        " connect_settings " + JSON.stringify(connectSettings, null, 4));
};

requestToNode = function(servicePath, params, callbacks) {

    if (servicePath === null)
        return;

    var restRequestJsonParams = {};
    restRequestJsonParams.path = servicePath;
    if(params !== null)
        restRequestJsonParams.params = JSON.stringify(params);


    $.ajax({
        type: 'POST',
        url: restServiceURL + servicePath,
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

addTableHeader = function (id) {
    var html = '<tr><th>Type</th><th>Base name</th><th>Size</th><th>Modification time</th></tr>';
    jQuery('#' + id).append(html);
}

addParentLink = function (id, parentPath) {
    if(parentPath){
        var html = '';
        html += '<tr onclick="browse(\'' + parentPath +'\')">';
        html += '<td colspan="4"><img src="/data/img/admin/trackback.png"></td>';
        html += '</tr>';
        $('#' + id).append(html);
    }
}

addResultToTable = function(id, item){
    var itemHtml = '';
    if(item && item['basename'] != '.cache'){
        if(item['type'] == 'file'){
            itemHtml += '<tr>';
            itemHtml += '<td><img src="/data/img/admin/file.png"></td>';
            itemHtml += '<td>' + item['basename'] + '</td>';
            itemHtml += '<td>' + item['size'] + '</td>';
        }
        else{
            itemHtml += '<tr onclick="browse(\'' + item['path'] +'\')">';
            itemHtml += '<td><img src="/data/img/admin/folder.png"></td>';
            itemHtml += '<td>' + item['basename'] + '</td>';
            itemHtml += '<td>&nbsp;</td>';
        }

        itemHtml += '<td>' + new Date(Date.parse(item['mtime'])) + '</td>';
        itemHtml += '</tr>';
    }
    $('#' + id).append(itemHtml);
}

var browse = function(path) {
    var parentPath = '';
    if(path && path != homePath){
        var pathParts = path.split('/');
        parentPath = (pathParts[pathParts.length - 2] != undefined) ? '/' + pathParts[pathParts.length - 2] : '';
    }
    var params = {};
    params.path = path ? path : homePath;
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
            $("#dirList").find("tr").remove();
            addTableHeader('dirList');
            addParentLink('dirList', parentPath);
            $.each(items, function() {
                addResultToTable('dirList', this);
                i++;
            });
        }
    };
    requestToNode('/files/browse', params, callbacks);
}

fileControls = {};

fileControls.handleTransferEvents = function (event, obj) {
    switch (event) {
        case 'transfer':
            $('#progressbar').progressbar({ value: Math.floor(obj.percentage * 100) });
            var jsonObj = eval(obj);
            console.log(JSON.stringify(obj, null, "        "));

            var cookie = jsonObj.transfer_spec.cookie;
            $('#p_'+cookie).progressbar('setValue', Math.floor(obj.percentage * 100));

            var info = obj.current_file;
            if(obj.status === "failed") {
                info = obj.title + ": " + obj.error_desc;
            } else if(obj.status === "completed") {
                $("#pause_"+cookie).hide();
                info = obj.title;
            }
            $("#file_name_"+cookie).text(obj.transfer_spec.direction + " - " + info);

            $("#jresult_"+cookie).text(JSON.stringify(obj, null, 4));
            $('#span_'+cookie).text(obj.transfer_spec.tags.aspera.xfer_id);
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
                console.log("Failed to start : " + JSON.stringify(obj, null, 4));
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
////                    container = $downloadContainer;
////                    toggleG = $('#downloads_group');
////                } else {
////                    container = $uploadContainer;
////                    toggleG = $('#uploads_group');
////                }
////                //insert elements into table
////                addToTable(container, transferSpec.cookie, transferSpec, connectSettings);
////                toggleG.show();
//                console.log("Started transfer : " + JSON.stringify(transferSpec, null, 4));
//            }

            success:function () {
                var container;
                var toggleG;
                var download = true;
                if(transferSpec.direction === "send") {
                    download = false;
                }

                if(download) {
                    container = $downloadContainer;
                    toggleG = $('#downloads_group');
                } else {
                    container = $uploadContainer;
                    toggleG = $('#uploads_group');
                }
                //insert elements into table
                addToTable(container, transferSpec.cookie, transferSpec, connectSettings);
                toggleG.show();
                console.log("Started transfer : " + JSON.stringify(transferSpec, null, 4));
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
    transfer_request.destination_root = homePath;

    params.transfer_requests.push({"transfer_request":transfer_request});

    var callbacks = {
        error : function (status) {
            console.log(status);
        },
        success : function (data) {
            var transferSpec = data.transfer_specs[0].transfer_spec;
            transferSpec.remote_password = nodeApiPassword;
//            //Use this if the server requires token authorization
//            transferSpec.authentication="token";
//            console.log(JSON.stringify(transferSpec));
            var connectSettings = {"allow_dialogs": "no"};
            fileControls.transfer(transferSpec, connectSettings);
        }
    };
    //console.log(params);
    requestToNode('/files/upload_setup', params, callbacks);
};


var setup  = function () {
    this.asperaWeb = new AW.Connect({id:'aspera_web_transfers'});

    $("#upload_files_button").click(function(e) {
        asperaWeb.showSelectFileDialog({success:fileControls.uploadFiles});
        e.preventDefault();
    });

    $("#upload_directory_button").click(function(e) {
        asperaWeb.showSelectFolderDialog({success:fileControls.uploadFiles});
        e.preventDefault();
    });

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
        console.log("Paused transfer id : " + transferId + " " + JSON.stringify(result, null, 4));
        return (result == null || typeof result.error === 'undefined' || result.error === null );
    }
    return false;
};

xferControls.resumeTransfer = function (transferId) {
    if(!(typeof transferId === 'undefined' || transferId == null)) {
        var result = asperaWeb.resumeTransfer(transferId);
        console.log("Resume transfer id : " + transferId + " " + JSON.stringify(result, null, 4));
        return (typeof result.error === 'undefined' || result.error === null );
    }
    return false;
};

xferControls.cancelTransfer = function (cookie, transferId) {
    if(!(typeof transferId === 'undefined' || transferId == null)) {
        var result = asperaWeb.removeTransfer(transferId);
        $("#"+cookie).remove();
        console.log("Removed transfer id : " + transferId + " " + JSON.stringify(result, null, 4));
    }
};

xferControls.showResultJSON = function (cookie) {
    document.getElementById('progress_json').innerHTML = $("#jresult_"+cookie).text();
    $('#progress_json').show();
};

xferControls.showTransferSpecJSON = function (cookie) {
    document.getElementById('transfer_spec').innerHTML = $("#json_" + cookie).text();
    $('#transfer_spec').show();
};

xferControls.hide = function (element) {
    element.hide();
};