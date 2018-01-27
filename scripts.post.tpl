<script src="//maps.googleapis.com/maps/api/js?key=AIzaSyAC7WkXrEfoGKEX6vHRinXiXksZrO9kOME&libraries=places&language={$smarty.const.CART_LANGUAGE|fn_cp_cities_google_langs}" type="text/javascript"></script>

/*
    @airoo
    https://developers.google.com/maps/documentation/javascript/places-autocomplete?hl=ru
    
    Добавляем google-autocomplete в input для популярной CMS CS-Cart. Также данный автокомплит выводит результаты городов по регионам, а не по стране (чего гугл пока не умеет).
    Также дополнительной особенностью этого автокомплита является то, что он заносит новые города, выбранные через автокомплит в базу данных mysql через php.
    
    Add google-autocomplete in input for popular CMS CS-Cart. Also, this auto-completion displays the results of cities by region, not country (which Google does not).
    An additional feature of this autocomplete is that it brought new cities, selected via auto-completion in mysql database using php.

*/

<script type="text/javascript"  class="cm-ajax-force">
        Tygh.$("[name='user_data[city]']").autocomplete({$ldelim}
            source: function( request, response ) {$ldelim}
                var state_google, state_short;
                var check_country;
                var check_state;
                check_country = $("[name='user_data[country]']").val();
                check_state = $("[name='user_data[state]']").val();
                if (request.term.length < 1) {$ldelim}
                $.ceAjax('request', fn_url('cp_city.autocomplete_city?q=' + request.term + '&check_state=' + check_state + '&check_country=' + check_country), {$ldelim}
                    callback: function(data) {$ldelim}
                        response(data.autocomplete);
                    {$rdelim}
                {$rdelim});
                {$rdelim} else {$ldelim}
                    var _this = this;
                    this.input = $('#elm_11');
                    this.service = new google.maps.places.AutocompleteService();

                    this.input.on('input', function() {
                    return _this.service.getPlacePredictions({
                        input: _this.input.val(),
                        types: ['(cities)'],
                        componentRestrictions: {
                            country: check_country
                        }
                    }, _this.callback);
                    });
                    
                    $.ceAjax('request', fn_url('cp_city.get_state?state=' + check_state), {$ldelim}
                        callback: function(data) {$ldelim}
                                state_google = data.state_google[0];
                                state_short = data.state_short;
                            {$rdelim}
                        {$rdelim});
                    this.callback = function(predictions, status) {
                        var i, prediction, _results;
                        if (status !== google.maps.places.PlacesServiceStatus.OK) {
                            return;
                        }
                        
                        _results = [];
                        for(var i = 0; i < predictions.length; i++) {
                            if (predictions[i].terms[1].value == state_google || predictions[i].terms[1].value == state_short) {
                                _results.push({
                                    label: predictions[i].terms[0].value,
                                    value: predictions[i].terms[0].value,
                                    longlat: i
                                });
                            }
                        }
                        response(_results);
                    };
                {$rdelim}
            {$rdelim},
            minLength: 0,
            select: function( event, ui ) {$ldelim}
                var check_country;
                var check_state;
                check_country = $("[name='user_data[country]']").val();
                check_state = $("[name='user_data[state]']").val();
                $.ceAjax('request', fn_url('cp_city.save_city?adr_from_google=' + ui.item.value + '&check_state=' + check_state + '&check_country=' + check_country), {$ldelim}
                    callback: function(data) {$ldelim}

                    {$rdelim}
                {$rdelim});
            {$rdelim}
        {$rdelim});
        $("[name='user_data[city]']").focus(function() {$ldelim}
            $(this).autocomplete("search", $(this).val());
        {$rdelim});
</script>
