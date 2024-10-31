(function ($) {

    var getCookie = function (name) {
        var cookie = " " + document.cookie,
            search = " " + name + "=",
            setStr = null,
            offset = 0,
            end = 0;

        if (cookie.length > 0) {
            offset = cookie.indexOf(search);

            if (offset !== -1) {
                offset += search.length;
                end = cookie.indexOf(";", offset);

                if (end === -1) {
                    end = cookie.length;
                }

                setStr = unescape(cookie.substring(offset, end));
            }
        }
        return (setStr);
    };


    var launchChecker = function () {
        var interval = setInterval(function () {
            checkAbandoned(function (result) {
                if (!result) {
                    clearInterval(interval);
                }
            });
        }, 5000);

        checkAbandoned(function (result) {
            if (!result) {
                clearInterval(interval);
            }
        });

        return interval;
    };

    var checkAbandoned = function (cbFunc) {
        cbFunc = cbFunc || function () {
            };

        $.ajax({
            type: 'post',
            url: pwAjax.ajaxurl,
            dataType: 'json',
            data: {
                action: 'check_abandoned'
            },
            success: function (resp) {
                if (!resp.success || resp.data.cart_empty) {
                    cbFunc(false);
                }
                cbFunc(true);
            },
            error: function (err) {
            }
        });
    };

    $(document).ready(function () {
        var launched = false;

        if (getCookie('woocommerce_items_in_cart') && getCookie('woocommerce_items_in_cart') > 0) {
            launched = launchChecker();
        } else {
            $.ajax({
                type: 'post',
                url: pwAjax.ajaxurl,
                dataType: 'json',
                data: {
                    action: 'pw_order_complete'
                }
            });
        }

        $('body').on('added_to_cart updated_wc_div', function () {
            if (!launched) {
                launched = launchChecker();
            }
        });
    });
})(jQuery);
