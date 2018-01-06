(function($orderForm$){
    var $orderForm;
    var $licenseSubtotalField;
    var $deliverySubtotalField;
    var $grandTotalField;
    var $applyRMDiscount;
    var $applyRFDiscount;
    var $rmDiscountField;
    var $rfDiscountField;
    var $userNameField;
    var $userIdField;
    var orderModifier = {
        init: function() {
            var that = this;
            $orderForm = $('#order_form');
            $licenseSubtotalField = $('input[name=sum]');
            $deliverySubtotalField = $('input[name=delivery_cost]');
            $grandTotalField = $('input[name=total]');
            $applyRMDiscount = $('.js-apply-rm');
            $applyRFDiscount = $('.js-apply-rf');
            $rmDiscountField = $('input[name=rm_discount]');
            $rfDiscountField = $('input[name=rf_discount]');
            $userNameField = $('input[name=client_name]');
            $userIdField = $('input[name=client_id]');
            // Events
            $('.js-editable').on('change', this.recalcOrder);
            $applyRMDiscount.on('click', function(e) {
                e.preventDefault();
                that.applyDiscount('rm');
            });
            $applyRFDiscount.on('click', function(e) {
                e.preventDefault();
                that.applyDiscount('rf');
            });

            $userIdField.on('change', function(){
                that.loadUserInfo($(this).val());
            });

            $userNameField.autocomplete({
                source: 'en/invoices/get_clients',
                minLength: 2,
                select: function( event, ui ) {
                    $userIdField.val(ui.item.client_id);
                    $userIdField.trigger('change');
                },
                search: function() {
                    $userIdField.val('');
                }
            });
        },
        recalcOrder: function() {
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '/en/invoices/update',
                data: $orderForm.serialize(),
                cache: false
            })
            .done(function(data) {
                if (data.total) {
                    $licenseSubtotalField.val(data.total.license_total);
                    $deliverySubtotalField.val(data.total.delivery_total);
                    $grandTotalField.val(data.total.total);
                }
                if (data.items) {
                    $.each(data.items, function(index, value) {
                        $('input[name="items[' + index + '][total_price]"]').val(value.total_price);
                        $('input[name="items[' + index + '][total]"]').val(value.total);
                    });
                }
            });
        },
        applyDiscount: function(type) {
            var discount;
            if (type == 'rf') {
                discount = $rfDiscountField.val();
                $('.item-license-1').each(function(){
                    var id = $(this).attr('id');
                    var idArr = id.split('-');
                    $('input[name="items[' + idArr[1] + '][discount]"]').val(discount);
                });
            }
            else {
                discount = $rmDiscountField.val();
                $('.item-license-2').each(function(){
                    var id = $(this).attr('id');
                    var idArr = id.split('-');
                    $('input[name="items[' + idArr[1] + '][discount]"]').val(discount);
                });
            }
            this.recalcOrder();
        },
        loadUserInfo: function(userId) {
            $.ajax({
                dataType: 'json',
                url: '/en/users/get_user/' + userId,
                cache: false
            }).done(function(data) {
                if (data.meta) {
                    $.each(data.meta, function(key, value) {
                        $('.' + key).val(value);
                    });
                }
            });
        }
    };
    $(document ).ready(function() {
        orderModifier.init();
    });
})(jQuery);
