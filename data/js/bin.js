function selectAll() {
  $(".itemTitle input").attr("checked", "checked");
  return false;
}

function toCartSelected() {
  $("form#lb").attr("action", lang + "/cart/add").attr("target", "cart").submit();
  return false;
}