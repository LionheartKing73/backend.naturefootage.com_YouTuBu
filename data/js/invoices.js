var invoiceItems={
    init:function(){
        this.preDeleteItems();
        this.deleteItems();
    },
    preDeleteItems:function(){
        /*PREDelete invoices item with order*/
        $('.delete-icon').on('click',function(){
            var wrapper=$(this).closest('tr');
            var id=wrapper.closest('tr').attr('id').replace(/\D+/g,"");
            var license=$('input[name="items['+id+'][total_price]"]').val();
            var delivery=$('input[name="items['+id+'][delivery_price]"]').val();
            var total=$('input[name="items['+id+'][total]"]').val();

            var tLicense=$('input[name="sum"]').val();
            var tDelivery=$('input[name="delivery_cost"]').val();
            var tTotal=$('input[name="total"]').val();

            $('input[name="sum"]').val(tLicense-license);
            $('input[name="delivery_cost"]').val(tDelivery-delivery);
            $('input[name="total"]').val(tTotal-total);

            wrapper.hide("slow");
            if($('#del-clips').hasClass('hide')){
                $('#del-clips').removeClass('hide');
                $('#del-clips-ids').val(id);
            }else{
                $('#del-clips-ids').val($('#del-clips-ids').val()+','+id);
            }
        });
    },
    deleteItems:function(){
        /*Confirm Delete invoices item with order*/
        $('#del-clips').on('click',function(){
            var tLicense=$('input[name="sum"]').val();
            var tDelivery=$('input[name="delivery_cost"]').val();
            var tTotal=$('input[name="total"]').val();
            var dataSend={
                orderId:$(this).data('order-id'),itemsIds:$('#del-clips-ids').val(),
                sum:tLicense,delivery_cost:tDelivery,total:tTotal
            };
            $.ajax( {
                type : 'POST',
                url : 'en/invoices/deleteitems',
                dataType : 'json',
                data : dataSend,
                async : false,
                success : function ( data ) {
                    if(data.success)
                        $('#del-clips').addClass('hide');
                }
            });
        });
    }
};
invoiceItems.init();
