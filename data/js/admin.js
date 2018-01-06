var host = 'http://' + location.host,
    stack = new Array,
    delay = 500,
    callbacks = {};

//------------------------------------------------------------------------------

function change_bg(type, id, ison){

    obj = document.getElementById(type+id);

    if(ison){
        if(type == 'head') obj.style.backgroundColor = '#f8f8f8';
        else obj.style.backgroundColor = '#cee3f7';
    }
    else{
        if(type == 'head') obj.style.backgroundColor = '#f0f0f1';
        else obj.style.backgroundColor = '#ffffff';
    }
}

//---------------------------------------------------------------------------------------//    

function light(cell)
{
    for(i=0;i<cell.parentNode.cells.length;i++){
        OldColors = cell.parentNode.cells[i].style.backgroundColor;
        cell.parentNode.cells[i].style.backgroundColor = '#e4f3fa';
    }
}

//---------------------------------------------------------------------------------------//    

function dark(cell)
{
    for(i=0;i<cell.parentNode.cells.length;i++){
        cell.parentNode.cells[i].style.backgroundColor = OldColors;
    }
}

//---------------------------------------------------------------------------------------//

function make_billing_as_license(){
    $("#bill_name" ).val($("#lic_name" ).val());
    $("#bill_company" ).val($("#lic_company" ).val());
    $("#bill_street1" ).val($("#lic_street1" ).val());
    $("#bill_street2" ).val($("#lic_street2" ).val());
    $("#bill_city" ).val($("#lic_city" ).val());
    $("#bill_state" ).val($("#lic_state" ).val());
    $("#bill_zip" ).val($("#lic_zip" ).val());
    $("#bill_country" ).val($("#lic_country" ).val());
    $("#bill_phone" ).val($("#lic_phone" ).val());
}


//---------------------------------------------------------------------------------------//
var delete_confirm = "The selected item(s) will be deleted.";

function change_action(myForm, action) {
    if(action) {
        if ((action.indexOf("delete") > 0) && !confirm(delete_confirm))
            return false;
        myForm.action = action;
    }
    myForm.submit();
    deSelectAllClips();
    return true;
}

//---------------------------------------------------------------------------------------//     

function select_all(myForm){
    var selection = true;
    var name = 'id[]';

    for (i = 0; i < myForm.elements.length; i++){
        if (myForm.elements[i].name==name){
            if(myForm.elements[i].checked == false)
                selection = myForm.elements[i].checked;
        }
    }

    for (i = 0; i < myForm.elements.length; i++){
        if (myForm.elements[i].name==name) myForm.elements[i].checked = !selection;
    }
    return false;
}

//---------------------------------------------------------------------------------------// 

function check_selected(myForm, element) {
    i = 0;
    flag = false;

    while (i<myForm.elements.length && !flag) {
        if (myForm.elements[i].name == element && myForm.elements[i].checked == true) flag=true;
        i++;
    }

    if (!flag) alert('No object was selected for action.');
    return flag;
}

//---------------------------------------------------------------------------------------// 

function display_childmenu(id) {
    var name = 'child'+id
    var elem = document.getElementsByTagName("div");
    var arr = new Array();

    for(i = 0,iarr = 0; i < elem.length; i++) {
        att = elem[i].getAttribute("name");
        if(att == name) elem[i].style.display = (elem[i].style.display == 'none' ) ? 'block' : 'none'; ;
    }
}

//---------------------------------------------------------------------------------------//

function update_selects(cat_active, subcat_active) {
    var first_select = document.getElementById("cats");
    var second_select = document.getElementById("subcats");

    if(!first_select.options.length){
        first_select.options[0] = new Option('', 0);

        for (var i=0; i<cats.length; i++){
            subs = cats[i].split(':');
            first_select.options[i+1] = new Option(subs[1], subs[0]);

            if(subs[0] == cat_active)
                first_select.options[i+1].selected = true;
        }
    }

    var choice = first_select.options[first_select.selectedIndex].value;
    var db = subcats[choice];

    second_select.options.length = 0;

    if (choice != "" && db){
        for (var i=0; i<db.length; i++) {
            second_select.options[i] = new Option(db[i], db[i]);
            if(subs[0] == subcat_active)
                second_select.options[i].selected = true;
        }
    }
}

//---------------------------------------------------------------------------------------//

function set_link(){
    obj = document.getElementById("urllink");
    subs = document.getElementById("subcats");
    choice = subs.options[subs.selectedIndex].value;
    obj.value = choice;
}

//---------------------------------------------------------------------------------------//

function menu_act(action, id){

    obj = document.getElementById('menu_'+id);
    subitem = document.getElementById('subitem_'+id);

    if(action){
        if(subitem) subitem.style.display = 'block';
        clearTimeout(get_from_stack(id));
    }
    else
        stack[id] = setTimeout('hide_layer('+id+')', delay);
}

//---------------------------------------------------------------------------------------//

function get_from_stack(id){
    return (stack[id]) ? stack[id] : 0;
}

//---------------------------------------------------------------------------------------//

function hide_layer(id){
    if(get_from_stack(id)){
        obj = document.getElementById('menu_'+id);
        subitem = document.getElementById('subitem_'+id);

        if(subitem) subitem.style.display = 'none';

        clearTimeout(get_from_stack(id));
    }
}

//---------------------------------------------------------------------------------------//

function submenu_act(action, id){
    if(action) clearTimeout(get_from_stack(id));
    else stack[id] = setTimeout('hide_layer('+id+')', delay);
}

//---------------------------------------------------------------------------------------//

function auto_iframe(frameId){
    try{
        frame = document.getElementById(frameId);
        innerDoc = (frame.contentDocument) ? frame.contentDocument : frame.contentWindow.document;
        objToResize = (frame.style) ? frame.style : frame;

        new_size = innerDoc.body.scrollHeight + 10;
        objToResize.height = (new_size>500) ? new_size : 500;
    }
    catch(err){
        window.status = err.message;
    }
}

//------------------------------------------------------------------------------

function chkout(lang, message) {
    obj = document.getElementById("accept");
    if(obj.checked) {
        var url = host + "/" + lang + "/cart/checkout.html";
        document.forms.fcart.action = url;
        return true;
    }
    else {
        alert(message);
        return false;
    }
}

//------------------------------------------------------------------------------

function checkPhrase(adv) {
    var elemId = adv ? "words" : "phrase";
    var elem = document.getElementById(elemId);
    if (elem && elem.value && elem.value.replace(" ", "")) {
        return true;
    }
    else {
        if (elem) {
            elem.focus();
        }
        alert("Please enter keyword(s).");
        return false;
    }
}

//------------------------------------------------------------------------------

function showContentDialog(url, title, width, height) {
    $("#dlg_frame").attr("src", url);
    $("#dialog").dialog({
        title: title,
        width: width,
        height: height,
        position: ["center", "middle"],
        modal: true,
        buttons: {
            Close: function() {
                $(this).dialog("close");
            }
        }

    });
}

$(document).ready(function () {

    $('.color-box').colpick({
        colorScheme:'dark',
        layout:'rgbhex',
        color:'ff8800',
        onSubmit:function(hsb,hex,rgb,el) {
            console.log($('#' + $(el).data('target-id')));
            $('#' + $(el).data('target-id')).val('#'+hex);
            $(el).css('background-color', '#'+hex);
            $(el).colpickHide();
        }
    });

    $(document).on('click', '.scroll-link', function(){
        var target = $('#' + $(this).data('target'));
        if(target.length > 0){
            $('html, body').animate({
                scrollTop: target.offset().top
            }, 500);
        }
        return false;
    });

    $(document).on('submit', 'form.ajaxify', function(){
        var $form = $(this);
        $.post(
            $form.attr('action'),
            $form.serialize(),
            function(data){
                if(data.message){
                    alert(data.message);
                }
            }
        );
        return false;
    });

    $('a.ajaxify').click(function(e){
        e.preventDefault();
        var el = this;
        if($(this).data('confirm')){
            if(confirm($(this).data('confirm'))){
                $.get($(this).attr('href'), function(data){
                    if(data.message)
                        alert(data.message);
                }, 'json')
                    .fail(function() { alert('error'); });
            }
        }
        if($(this).hasClass('template-change')){
            $.post($(this).attr('href'), {"get_template": "true"}, function(data){
                if(data.success && data.template){
                    emailTemplate(data.template, el);
                }
                else if(data.error){
                    alert(data.error);
                }
                else{
                    alert('Cannot send email.');
                }
            }, 'json');

        }
        else{
            $.get($(this).attr('href'), function(data){
                if(data.message)
                    alert(data.message);
            }, 'json')
                .fail(function() { alert('error'); });
        }
        return false;
    });

    $('select.ajaxify').change(function(e){
        var data = {},
            idArr = $(this).attr('id').split('-'),
            el = this;
        data[$(this).attr('name')] = $(this).val();
        if(idArr[1] !== undefined)
            data['id'] = idArr[1];
        $.post(
            window.location.href,
            data,
            function(data){
                if(data.message)
                    alert(data.message);
                if(data.callback){
                    if(typeof(callbacks[data.callback]) == 'function'){
                        callbacks[data.callback](el, data);
                    }
                }
            },
            'json'
        )
    });

    callbacks.test = function(text){
        alert(text);
    }

    callbacks.updateDescription = function(el, data){
        var description = $(el).parents('tr').find('.option-value');
        if(data.color){
            description.css({'background-color': data.color})
        }
        else{
            description.removeAttr('style');
        }
        description.html(data.description);
    }

    callbacks.downloadStatusPost = function(el, data){
        $(el).siblings('p').text(data.description);
    }

    callbacks.changeRepPost = function(el, data){
        var description = $(el).siblings('.sales-rep');
        description.text(data.description);
        if(data.color){
            description.css({'background-color': data.color});
        }
        else{
            description.removeAttr('style');
        }
    }

    callbacks.setExpirationDate = function(el, data){
        if(data.expiration_date){
            $('.expiration-date').text(data.expiration_date);
        }
    }

    $('a.ajaxify-data').on('click', function(){
        var data = $(this).data('data'),
            href = $(this).attr('href'),
            el = this;
        $.post(
            href,
            data,
            function(data){
                //data = JSON.parse(data);
                if(data.message){
                    alert(data.message);
                }
                if(data.callback){
                    if(typeof(callbacks[data.callback]) == 'function'){
                        callbacks[data.callback](el, data);
                    }
                }
            },
            'json'
        );
        return false;
    });

    $('select.ajaxify-data').on('change', function(){
        var data = $(this).data('data'),
            id = $(this).attr('id'),
            that = this,
            href = $(this).data('href') ? $(this).data('href') : window.location.href;
        if($(this).attr('name')){
            data[$(this).attr('name')] = $(this).val();
        }
        else{
            data[data.name] = $(this).val();
        }
        console.log(data);
        $.post(
            href,
            data,
            function(data){
                data = JSON.parse(data);
                if(data.success){
                    switch(data.type){
                        case 'sales_rep':
                            changeRep(that, data);
                            break;
                        default:
                            break;
                    }
                }
                if(data.callback){
                    if(typeof(callbacks[data.callback]) == 'function'){
                        callbacks[data.callback](that, data);
                    }
                }
                if(data.color){
                    $(that).parents('td').css({"background-color":data.color});
                }
                else{
                    $(that).parents('td').removeAttr('style');
                }
            }
        );
    });

    $(document).on('submit', 'form.ajaxify', function(){
        var $form = $(this);
        $.post(
            $form.attr('action'),
            $form.serialize(),
            function(data){
                if(data.message){
                    alert(data.message);
                }
            }
        );
        return false;
    });

    $('textarea.notes.ajaxify').on('focusout', function(){
        var data = {
            'notes': $(this).val()
        };
        $.post(
            $(this).data('action'),
            data,
            function(data){
                if(data.message){
                    alert(data.message);
                }
            }
        );
    });

    var count = 0;
    var oFCKeditor = {};

    function emailTemplate(template, el){

        if (!template.cc){
            template.cc = '';
        }

        $('' +
            '<div style="width:400px;">' +
            '<span>To:</span><input type="text" class="to" style="width: 335px;" value="' + template.to + '">' +
            '<br>' +
            '<span>Cc:</span><input type="text" class="cc" style="width: 335px;" value="' + template.cc + '">' +
            '<textarea id="body' + count + '" style="width: 360px; resize: none; overflow-y: auto; height:300px;">' + template.body + '</textarea>' +
            '</div>' +
            '').dialog({
            width: '400',
            dialogClass: 'email-dialog',
            modal: true,
            draggable: true,
            buttons: [
                { text: "Cancel", click: function () { $(this).dialog("close"); } ,class:"btn" },
                { text: "Send", click: function () {
                    data = {
                        'modified': 'true',
                        'to': $('input.to', $(this)).val(),
                        'cc': $('input.cc', $(this)).val(),
                        'body': FCKeditorAPI.GetInstance('body' + (count - 1)).GetHTML(true)
                    };

                    console.log(data.body);


                    $.post($(el).attr('href'), data, function(data){
                        if(data.message){
                            jQuery('<div>' + data.message + '</div>').dialog({
                                buttons: [
                                    { text:"Ok", click: function(){$(this).dialog('close');}, class:"btn" }
                                ]
                            });
                            //alert(data.message);

                        }
                        if(data.send_status){
                            $(el).siblings('span').text(data.send_status);
                            $(el).parents('tr').find('.option-value').css({'background-color': data.color}).text(data.send_status);
                        }
                    }, 'json')
                        .fail(function() { alert('error'); });
                    $(this).dialog("close");
                },class:"btn-primary" }
            ],
            create: function(event, ui){
                $('.ui-dialog-titlebar').hide();
            }
        });

        oFCKeditor[count] = new FCKeditor("body" + count);
        oFCKeditor[count].ToolbarSet="Custom";
        oFCKeditor[count].Width=350;
        oFCKeditor[count].Height=300;
        oFCKeditor[count].BasePath="/vendors/fck/";
        oFCKeditor[count].Config['ToolbarStartExpanded'] = 'false';
        oFCKeditor[count].ReplaceTextarea();

        count++;

    }

    function changeRep(el, data){
        $(el).siblings('.sales-rep').remove();
        $(el).parent().prepend(data.salesRep);
    }
    // html/text form send
    $('input[name="is_html"]' ).on('change',function(){
        //var val=($(this).val())?0:1;
        $('input[name="emailtype"]').val(1);

        $('form.form-horizontal' ).submit();
    });

});