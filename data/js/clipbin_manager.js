(function($) {
    $(function () {
        $('.cliplog-tree-section').on('click', function(){
            var parentLi = $(this).parent();
            parentLi.toggleClass('expanded');
        });

        $('.clipbin-manager-filter li.selected').parents('li').addClass('expanded');
    })
})(jQuery);