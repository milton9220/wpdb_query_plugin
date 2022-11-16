; (function ($) {
    $(document).ready(function () {
        $(".action-button").on('click', function () {
            
            let task = $(this).data('task');
            
            $.ajax({
                type : "post",
                url : plugindata.ajax_url,
                data : {
                    "action": "display_result",
                    "nonce": plugindata.nonce,
                    "task": task
                },
                success    : function(data){
                    $("#plugin-demo-result").html("<pre>" + data + "</pre>").show();
                },
    
             });
        });
    });
})(jQuery);