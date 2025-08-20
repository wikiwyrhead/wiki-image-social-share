jQuery(document).ready(function($){

    // Select2 init
    function sti_init_select2() {
        $('select.sti-select2').select2({
            minimumResultsForSearch: 15
        });
    }

    sti_init_select2();


    var $addNumberBtn = $('[data-add-number-btn]');

    // Settings tabs
    var navigationTabs = $('.sti-nav-tab a');
    navigationTabs.on( 'click', function(e) {
        e.preventDefault();

        var isActive = $(this).hasClass('active');

        if ( ! isActive ) {

            var tabName = $(this).data('tab');
            $('.sti-settings .form-table').hide();
            $('.sti-settings .form-table[data-tab="'+tabName+'"]').show();
            navigationTabs.removeClass('active');
            $(this).addClass('active');

            const url = new URL( window.location );
            url.searchParams.set( "tab", tabName );
            window.history.pushState( {}, "", url );

            if ( tabName === 'display' ) {
                sti_init_select2();
            }

        }

    } );

    //Sortable for buttons
    $('.sti-sbt .sti-sbt-body').sortable({
        handle: ".sti-table-sort",
        items: ".sti-sbt-item",
        axis: "y"
    });


    // Display rules filter
    var stiUniqueID = function() {
        return Math.random().toString(36).substr(2, 11);
    };


    var stiGetRuleTemplate = function( groupID, ruleID) {

        var template = $(this).closest('.sti-rules').find('#stiRulesTemplate').html();

        if ( typeof groupID !== 'undefined' ) {
            template = template.replace( /\[group_(.+?)\]/gi, '[group_'+groupID+']' );
        }

        if ( typeof ruleID !== 'undefined' ) {
            template = template.replace( /\[rule_(.+?)\]/gi, '[rule_'+ruleID+']' );
            template = template.replace( /data-sti-rule="(.+?)"/gi, 'data-sti-rule="'+ruleID+'"' );
        }

        return template;

    };


    $(document).on( 'click', '[data-sti-remove-rule]', function(e) {
        e.preventDefault();
        var $table = $(this).closest('.sti-rules-table');
        $(this).closest('[data-sti-rule]').remove();

        if ( $table.find('[data-sti-rule]').length < 1 ) {
            $table.remove();
        }

    });


    $(document).on( 'click', '[data-sti-add-rule]', function(e) {
        e.preventDefault();

        var groupID = $(this).closest('.sti-rules-table').data('sti-group');
        var ruleID = stiUniqueID();
        var rulesTemplate = stiGetRuleTemplate.call(this, groupID, ruleID);

        $(this).closest('.sti-rules-table').find( '.sti-rule' ).last().after( rulesTemplate );

    });


    $(document).on( 'click', '[data-sti-add-group]', function(e) {
        e.preventDefault();

        var groupID = stiUniqueID();
        var rulesTemplate = stiGetRuleTemplate.call(this, groupID);

        rulesTemplate = '<table class="sti-rules-table" data-sti-group="' + groupID + '"><tbody>' + rulesTemplate + '</tbody></table>';
        $(this).closest('.sti-rules').find('.sti-rules-table').last().after( rulesTemplate );

    });


    $(document).on('change', '[data-sti-param]', function(evt, params) {

        var newParam = this.value;
        var ruleGroup = $(this).closest('[data-sti-rule]');

        var ruleOperator = ruleGroup.find('[data-sti-operator]');
        var ruleValues = ruleGroup.find('[data-sti-value]');
        var ruleParams = ruleGroup.find('[data-sti-param]');
        var ruleSuboptions = ruleGroup.find('[data-sti-suboption]');

        var ruleID = ruleGroup.data('sti-rule');
        var groupID = $(this).closest('[data-sti-group]').data('sti-group');

        ruleGroup.addClass('sti-pending');

        if ( ruleSuboptions.length ) {
            ruleSuboptions.remove();
            ruleGroup.find('.select2-container').remove();
        }

        $.ajax({
            type: 'POST',
            url: sti_ajax_object.ajaxurl,
            dataType: "json",
            data: {
                action: 'sti-getRuleGroup',
                name: newParam,
                ruleID: ruleID,
                groupID: groupID,
                _ajax_nonce: sti_ajax_object.ajax_nonce
            },
            success: function (response) {
                if ( response ) {

                    ruleGroup.removeClass('adv');

                    if ( typeof response.data.aoperators !== 'undefined' ) {
                        ruleOperator.html( response.data.aoperators );
                    }

                    if ( typeof response.data.avalues !== 'undefined' ) {
                        ruleValues.html( response.data.avalues );
                    }

                    if ( typeof response.data.asuboptions !== 'undefined' ) {
                        ruleParams.after( response.data.asuboptions );
                        ruleGroup.addClass('adv');
                    }

                    ruleGroup.removeClass('sti-pending');

                    sti_init_select2();

                }
            }
        });

    });

    $(document).on('change', '[data-sti-suboption]', function(evt, params) {

        var suboptionParam = this.value;
        var ruleGroup = $(this).closest('[data-sti-rule]');
        var ruleParam = ruleGroup.find('[data-sti-param] option:selected').val();
        var ruleValues = ruleGroup.find('[data-sti-value]');

        var ruleID = ruleGroup.data('sti-rule');
        var groupID = $(this).closest('[data-sti-group]').data('sti-group');

        ruleGroup.addClass('sti-pending');

        $.ajax({
            type: 'POST',
            url: sti_ajax_object.ajaxurl,
            dataType: "json",
            data: {
                action: 'sti-getSuboptionValues',
                param: ruleParam,
                suboption: suboptionParam,
                ruleID: ruleID,
                groupID: groupID,
                _ajax_nonce: sti_ajax_object.ajax_nonce
            },
            success: function (response) {
                if ( response ) {
                    ruleValues.html( response.data );
                    ruleGroup.removeClass('sti-pending');
                    sti_init_select2();
                }
            }
        });

    });


    /* Add Number */
    $addNumberBtn.on( 'click', function(e){
        e.preventDefault();

        var $container = $(this).closest('[data-container]');

        var addNumberName = $container.find('[data-add-number-name]');
        var addNumberNameValue = addNumberName.val();

        var currentAddNumber = $container.find('[data-add-number-val]');
        var currentAddNumberValue = currentAddNumber.val();
        var currentAddNumberValueObj = currentAddNumberValue ? JSON.parse( currentAddNumberValue ) : {};

        var addNumberList = $container.find('[data-add-number-list]');

        if ( addNumberNameValue ) {
            currentAddNumberValueObj[addNumberNameValue] = addNumberNameValue;

            currentAddNumber.val( JSON.stringify( currentAddNumberValueObj ) );

            addNumberList.append('<li class="item"><span data-name="' + addNumberNameValue + '" class="name">' + addNumberNameValue + '</span><a data-remove-number-btn class="close">x</a></li>');

            addNumberName.val('');

        }

    } );


    /* Remove number */
    $(document).on( 'click', '[data-remove-number-btn]', function(e){
        e.preventDefault();

        if (! window.confirm("Are you sure?")) {
            return;
        }

        var $container = $(this).closest('[data-container]');

        var $removedAddNumber = $(this).closest('li');
        var addNumberName = $removedAddNumber.find('[data-name]').text();

        var currentAddNumber = $container.find('[data-add-number-val]');
        var currentAddNumberValue = currentAddNumber.val();
        var currentAddNumberValueObj = currentAddNumberValue ? JSON.parse( currentAddNumberValue ) : {};

        $removedAddNumber.remove();

        if ( currentAddNumberValue ) {
            if ( currentAddNumberValueObj[addNumberName] ) {
                delete currentAddNumberValueObj[addNumberName];
                currentAddNumber.val( JSON.stringify( currentAddNumberValueObj ) );
            }
        }

    } );


    /* Admin notices */
    $(document).on( 'click', '[data-sti-notice] button.notice-dismiss', function(e){
        e.preventDefault();

        var noticeName = $(this).closest('[data-sti-notice]').data('sti-notice');

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'sti-dismissNotice',
                notice: noticeName,
                _ajax_nonce: sti_ajax_object.ajax_nonce
            },
            dataType: "json",
            success: function (data) {
                console.log('Notice dismissed!');
            }
        });

    });

    // Dismiss welcome notice
    $( '.sti-welcome-notice.is-dismissible' ).on('click', '.notice-dismiss', function ( event ) {

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'sti-hideWelcomeNotice',
                _ajax_nonce: sti_ajax_object.ajax_nonce
            },
            dataType: "json",
            success: function (data) {
            }
        });

    });

    $('.additional-info').on('click', function(e) {
        e.preventDefault();
        if ( ! $(e.target).closest( '.info-spoiler' ).length ) {
            $(this).find('.info-spoiler').toggleClass('show');
        }
    });

    $('.additional-info .info-spoiler a').on('click', function(e) {
        e.stopPropagation();
    });

});