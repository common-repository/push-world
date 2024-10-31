(function ($) {
    $(document).ready(function () {

        $('.b-input__wrap').each(function () {
            floatingLabel($(this));
        });


        $('.b-input--textarea').each(function () {
            textareaAutosize($(this))
        });


        $('.js-tabs').each(function () {
            tabs($(this));
        });

        $('.js-push-test').on('click', function () {
            testPush($(this));
        });

    });


    function floatingLabel(wrap) {
        var input = wrap.find('.b-input'),
            iType = input.attr('type');

        if (!input.length || iType === 'checkbox' || iType === 'radio') return;


        input.val() ? wrap.removeClass('is-empty') : wrap.addClass('is-empty');

        input.on('change input', function () {
            input.val() ? wrap.removeClass('is-empty') : wrap.addClass('is-empty');
        });

        input.on('focus', function () {
            wrap.addClass('is-focused');
        });

        input.on('blur', function () {
            wrap.removeClass('is-focused');
        });
    }

    function textareaAutosize(textarea) {

        function resize() {
            textarea[0].style.height = 'auto';
            textarea[0].style.height = textarea[0].scrollHeight + 'px';
        }

        function delayedResize() {
            setTimeout(resize, 0);
        }

        textarea.on('change input', delayedResize);
        $('.nav-tab').on('click', delayedResize);

        delayedResize();
    }

    function tabs(wrap) {
        var labels = wrap.find('.b-tabs__label'),
            panes = $('.b-tabs__pane');


        labels.each(function () {
            var $this = $(this);

            $this.on('click', function () {
                tabsHandler($this, labels, panes);
            });

            //init
            if ($this.hasClass('is-active')) {
                tabsHandler($this, labels, panes);
            }
        })
    }

    function tabsHandler($this, labels, panes) {
        var targetPane = $this.data('for');

        labels.removeClass('is-active');
        panes.removeClass('is-active');
        $this.addClass('is-active');
        $this.addClass('is-active');
        $(targetPane).addClass('is-active');
    }

    function testPush($that) {
        if ($that.hasClass('b-button--disabled')) return;

        $.ajax({
            type: 'post',
            url: pwPushTestAjax.ajaxurl,
            dataType: 'json',
            data: {
                action: 'push_test'
            }
        });

        $that.addClass('b-button--disabled');

        setTimeout(function () {
            $that.removeClass('b-button--disabled')
        }, 10000);

    }

})(jQuery);