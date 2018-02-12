<?php
/**
 * @var $this View
 * @var $formType string
 * @var $label bool
 */

use kartik\form\ActiveForm;
use yii\helpers\Url;
use yii\web\View;

$fieldPrefix = !empty($fieldPrefix) ? '_' . $fieldPrefix : '';
$label = isset($label) ? $label : false;
$formType = isset($formType) ? $formType : ActiveForm::TYPE_VERTICAL;
?>

<?php \app\widgets\JavaScript::begin(['position' => View::POS_END]) ?>
<script>

    $(document).on('change', '.address-postcode', postcodeAjaxLookup);
    //$(document).on('change', '.address-city:input[type="text"]', postcodeAjaxLookup);
    $('.address-postcode').change();

    function postcodeAjaxLookup() {
        var $postal = $(this).val();
        
        var $address = $(this).closest('.address'),
            $postcode = $address.find('.address-postcode'),
            $city = $address.find('.address-city'),
            $state = $address.find('.address-state'),
            $country = $address.find('.address-country'),
            key = $postcode.attr('id').replace('Addresses_', '').replace('_postcode', ''),
            data = {
                postcode: $postcode.val(),
                city: $city.val(),
                state: $state.val(),
                country: $country.val(),
                _csrf: yii.getCsrfToken()
            };
         <?php if ($label) echo "key = '';"; ?>
        $.ajax({
            type: "GET",
            url: 'http://api.zippopotam.us/nz/' + $postal,
            data: data,
            success: function (response) {
                
                if(!$.trim(response)) {
                    alert("!!!!!!!!!!!!1");
                }
                // update postcode
                if (response.postcode) {
                    $postcode.val(response.postcode);
                }

                if (response.places[0]['place name']) {
                    
                    $city.val(response.places[0]['place name']);
                }

                if (response.places[0].state) {
                    
                    $state.val(response.places[0].state);
                }

                if (response.country) {
                    
                    $country.val(response.country);
                }
            },

            error: function (errorThrown){
                $.getJSON('http://maps.googleapis.com/maps/api/geocode/json?address='+$postal).success(function(response){
                    console.log(response);
                    var address_components = response.results[0].address_components;
                        $.each(address_components, function(index, component){
                          var types = component.types;
                          $.each(types, function(index, type){
                            if(type == 'locality') {
                              city = component.long_name;
                            }
                            if(type == 'administrative_area_level_1') {
                              state = component.short_name;
                            }
                            if(type == 'country') {
                              country = component.short_name;
                            }
                          });
                        });

                        //pre-fill the city and state
                        $city.val(city);
                        $state.val(state);
                        $country.val(country);

                })
            },
            dataType: 'json'
        });
    }
    function postcodeFindOption($el, value) {
        return $el.find('option').filter(function () {
            return this.value.toLowerCase() === value.toLowerCase();
        }).attr('value')
    }
</script>
<?php \app\widgets\JavaScript::end() ?>
