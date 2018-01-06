var host = 'http://'+location.host;
var path = host+'/data/img/'; 

var visual_win;
var stack = new Array;  

//---------------------------------------------------------------------------------------//

function get_from_stack(id){
  return (stack[id]) ? stack[id] : 0;
}

//---------------------------------------------------------------------------------------//

function show_bar(id, mode, lang){

  var bar;
  if(!mode) mode = 'page';
  
  obj = document.getElementById(mode+id); 
  obj.className = 'boxover';
    
  if(get_from_stack(mode+'_'+id)){
     bar = get_from_stack(mode+'_'+id);
     bar.style.display = 'block'; 
  }
  else{
    bar = document.createElement("div");
    bar.innerHTML = "<a href=javascript:show_window("+id+",'"+mode+"','"+lang+"');><img src='data/img/bar/edit.gif' width='20' height='20' border=''></a>"
    bar.className = 'bar';
    obj.appendChild(bar);
    stack[mode+'_'+id] = bar;
  }
}
  
//---------------------------------------------------------------------------------------//

function hide_bar(id, mode){
  if(!mode) mode = 'page';
  obj = document.getElementById(mode+id); 
  obj.className = 'boxout';
  
  bar = get_from_stack(mode+'_'+id);
  bar.style.display = 'none'; 
}

//---------------------------------------------------------------------------------------//

function show_window(id, mode, lang) {
  if (window.parent)
    window.parent.scrollTo(0, 0);

  var title, width, height;
  var d = new Date();
  var t = d.getTime();

  if(mode == 'page'){
    module_url = host+'/'+lang+'/publication/edit/'+id+'/visual/'+t;
    title = "Edit page";
    width = 900;
    height = 500;
  }

  if(mode=='block'){
    module_url = host+'/'+lang+'/blocks/edit/'+id+'/visual/'+t;
    title = "Edit block";
    width = 880;
    height = 400;
  }

  if(mode=='news'){
    module_url = host+'/'+lang+'/news/edit/'+id+'/visual/'+t;
    title = "Edit publication";
    width = 850;
    height = 500;
  }

  if(mode=='banner'){
    module_url = host+'/'+lang+'/banners/view/visual/'+t;
    title = "Edit banners";
    width = 900;
    height = 600;
  }

  if(mode=='production'){
    module_url = host+'/'+lang+'/production/edit/'+t;
    title = "Edit production";
    width = 900;
    height = 750;
  }

  $("#dlg_frame").attr("src", module_url);

  $("#dialog").dialog({
    title: title,
    width: width,
    height: height,
    position: ["center", 50],
    modal: true,
    close: function(event, ui) { window.location.reload(true); }
  });
}