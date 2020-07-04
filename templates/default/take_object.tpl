{if $ajax}
    {assign var="extend" value='ajax.tpl'}
{else}
    {assign var="extend" value='page.tpl'}
{/if}
{extends file=$extend}

{block name="content"}
<form action="{if $takeorgive eq 'take'}{path_for name="objectslend_object_dotake" data=["id" => $object->object_id]}{else}{path_for name="objectslend_object_doreturn" data=["id" => $object->object_id]}{/if}" method="post" id="form_take_object">
    <div class="bigtable">
        <fieldset class="galette_form">
            <legend class="ui-state-active ui-corner-top">{_T string="Object" domain="objectslend"}</legend>
            <div>
                <p>

                    <img src="{if $object->object_id}{path_for name="objectslend_photo" data=["type" => "object", "mode" => "thumbnail", "id" => $object->object_id]}{else}{path_for name="objectslend_photo" data=["type" => "object", "mode" => "thumbnail"]}{/if}?rand={$time}"
                        class="picture fright"
                        width="{$object->picture->getOptimalThumbWidth($olendsprefs)}"
                        height="{$object->picture->getOptimalThumbHeight($olendsprefs)}"
                        alt="{_T string="Object photo" domain="objectslend"}"/>
                    <span class="bline">{_T string="Name:" domain="objectslend"}</span>
                    {$object->name}
                </p>
            </div>
    {if $lendsprefs.VIEW_DESCRIPTION}
            <div>
                <p>
                    <span class="bline">{_T string="Description:" domain="objectslend"}</span>
                    {$object->description}
                </p>
            </div>
    {/if}
    {if $lendsprefs.VIEW_SERIAL}
            <div>
                <p>
                    <span class="bline">{_T string="Serial number:" domain="objectslend"}</span>
                    {$object->serial_number}
                </p>
            </div>
    {/if}
    {if $lendsprefs.VIEW_PRICE}
            <div>
                <p>
                    <span class="bline">{_T string="Price:" domain="objectslend"}</span>
                    {$object->price} {$object->currency}
                </p>
            </div>
    {/if}
            <div>
                <p>
                    <span class="bline">{_T string="Borrow price:" domain="objectslend"}</span>
                    {if $takeorgive == 'take' && ($login->isAdmin() || $login->isStaff())}
                        <input type="text" name="rent_price" id="rent_price" value="{$object->rent_price}" size="10" style="text-align: right"> {$object->currency}
                    {else}
                        <input type="hidden" name="rent_price" id="rent_price" value="{$object->rent_price}">
                        <span id="rent_price_label">{$object->rent_price}</span> {$object->currency}
                    {/if}

                    {if $object->price_per_day}
                        {_T string="(per day)" domain="objectslend"}
                    {else}
                        {_T string="(at once)" domain="objectslend"}
                    {/if}
                </p>
            </div>
    {if $lendsprefs.VIEW_DIMENSION}
            <div>
                <p>
                    <span class="bline">{_T string="Dimensions:" domain="objectslend"}</span>
        {if $object->dimension != ''}
                    {$object->dimension} {_T string="Cm" domain="objectslend"}
        {else}
                    -
        {/if}
                </p>
            </div>
    {/if}
    {if $lendsprefs.VIEW_WEIGHT}
            <div>
                <p>
                    <span class="bline">{_T string="Weight:" domain="objectslend"}</span>
        {if $object->weight != ''}
                    {$object->weight} {_T string="Kg" domain="objectslend"}
        {else}
                    -
        {/if}
                </p>
            </div>
    {/if}
    {if $takeorgive eq 'take'}
            {if $login->isAdmin() || $login->isStaff()}
                <div>
                    <p>
                        <label for="id_adh">{_T string="Member:" domain="objectslend"}</label>
                        <select name="id_adh" id="id_adh" class="nochosen"{if isset($disabled.id_adh)} {$disabled.id_adh}{/if}>
                            {if $adh_selected eq 0}
                            <option value="">{_T string="Search for name or ID and pick member"}</option>
                            {/if}
                            {foreach $members.list as $k=>$v}
                                <option value="{$k}"{if $login->id == $k} selected="selected"{/if}>{$v}</option>
                            {/foreach}
                        </select>
                    </p>
                </div>
            {/if}
            <div>
                <p>
                    <span class="bline">{_T string="Status:" domain="objectslend"}</span>
                    <select name="status" id="status">
                        <option value="null">{_T string="--- Select a status ---" domain="objectslend"}</option>
                        {foreach from=$statuses item=sta}
                            <option value="{$sta->status_id}" data-days="{$sta->rent_day_number}">
                                {$sta->status_text}
                                {if $sta->rent_day_number ne ''}
                                    ({_T string="%days days" domain="objectslend" pattern="/%days/" replace=$sta->rent_day_number})
                                {/if}
                            </option>
                        {/foreach}
                    </select>
                </p>
            </div>
            <div>
                <p>
                    <span class="bline">{_T string="Expected return:" domain="objectslend"}</span>
                    <input type="text" id="expected_return" name="expected_return" size="8" value="{$date_forecast}">
                </p>
            </div>
            {if $lendsprefs.AUTO_GENERATE_CONTRIBUTION}
                <div>
                    {* payment type *}
                    {assign var="ptype" value=$contribution->payment_type}
                    {include file="forms_types/payment_types.tpl" varname="payment_type"}
                </div>
            {/if}
    {else}
            <div>
                <p>
                    <span class="bline">{_T string="Status:" domain="objectslend"}</span>
                    <select name="status" id="status">
                        <option value="null">{_T string="--- Select a status ---" domain="objectslend"}</option>
                        {foreach from=$statuses item=sta}
                            <option value="{$sta->status_id}">{$sta->status_text}</option>
                        {/foreach}
                    </select>
                </p>
            </div>
            <div>
                <p>
                    <span class="bline">{_T string="Time:" domain="objectslend"}</span>
                    {_T string="From %begindate to %enddate" pattern=["/%begindate/", "/%enddate/"] replace=[$last_rent->date_begin, $smarty.now|date_format:{_T string="Y-m-d"}]}
                </p>
            </div>
            <div>
                <p>
                    <span class="bline">{_T string="Comments:" domain="objectslend"}</span>
                    <textarea name="comments" id="comments"></textarea>
                    <br/><span class="exemple"><span id="remaining">200</span>
                    {_T string="remaining characters" domain="objectslend"}</span>
                </p>
            </div>
    {/if}
        </fieldset>
    </div>
    {if $takeorgive eq 'take'}
    <div class="disclaimer center">
        <input type="checkbox" name="agreement" id="agreement" value="1" required="required"/>
        <label for="agreement">{_T string="I have read and I agree with terms and conditions" domain="objectslend"}</label>
        <span class="show_agreement" title="{_T string="Show terms and conditions" domain="objectslend"}">

        <img src="{base_url}/{$template_subdir}images/icon-down.png" alt="{_T string="Show terms and conditions" domain="objectslend"}"/></span>
        <div id="terms_conditions" class="left">{_T string="The items offered for rent are in good condition and verification rental contradictory to their status is at the time of withdrawal. No claims will be accepted after the release of the object. Writing by the store a list of reservation does not exempt the customer checking his retrait. The payment of rent entitles the purchaser to make normal use of the loaned object. If the object is rendered in a degraded state, the seller reserves the right to collect all or part of the security deposit. In case of deterioration of the rented beyond the standard object, a financial contribution will be required for additional cleaning caused. In case of damage, loss or theft of the rented property, the deposit will not be refunded automatically to 'the company as damages pursuant to Article 1152 of the Civil Code and without that it need for any other judicial or extra-judicial formality. In some other cases not listed above and at the discretion of the seller, the deposit check may also be collected in whole or party." domain="objectslend"}</div>
    </div>
    {/if}
    <div class="button-container" id="button_container">
        <input type="hidden" name="mode" value="{if $ajax}ajax{/if}"/>
        <input type="submit" id="btnsave" name="yes" value="{if $takeorgive eq 'take'}{_T string="Take away" domain="objectslend"}{else}{_T string="Return back" domain="objectslend"}{/if}">
        <a href="{path_for name="objectslend_objects"}" class="button" id="btncancel">{_T string="Cancel"}</a>
    </div>
</form>
{/block}

{block name="javascripts"}
<script type="text/javascript">
    {include file="js_chosen_adh.tpl"}

{if $takeorgive eq 'take'}
    var _init_takeobject_js = function() {
        $('#btnsave').button('disable');

        $('#expected_return').datepicker({
            changeMonth: true,
            changeYear: true,
            showOn: 'button',
            buttonText: '<i class="far fa-calendar-alt"></i> <span class="sr-only">{_T string="Select a date" escape="js"}</span>',
            //minDate: 0,
            selectOtherMonths: true,
            showOtherMonths: false,
            showWeek: true,
        });

    {if $olendsprefs->showFullsize()}
        _init_fullimage();
    {/if}

        $('#id_adh, #status, #payment_type').on('change',function() {
            validStatus()
        });

        $('#btnsave').on('click', function(e) {
            if (!$('#agreement').is(':checked')) {
                e.preventDefault();
                alert('{_T string="You must agree with terms and conditions in order to take." domain="objectslend" escape="js"}');
            }
        });

        $('#terms_conditions').hide();
        $('.show_agreement').on('click', function() {
            $('#terms_conditions').toggle();
        });
    }

    {if not $ajax}
    $(function () {
        _init_takeobject_js();
    });
    {/if}

    function completeZero(n) {
        return n < 10 ? '0' + n : n;
    }

    function validStatus() {
        var _disabled = false;
        if ($('#status').val() === 'null') {
            _disabled = true;
        }
        if ($('#id_adh').val() === 'null') {
            _disabled = true;
        }
        if ($('#payment_type').val() === 'null') {
            _disabled = true;
        }

        var _lyes = $('#btnsave');
        if (_disabled) {
            _lyes.button('disable');
        } else {
            _lyes.button('enable');
        }

        var days = $('#status option:selected').data('days');
        if (typeof days == "undefined" || days == 'null') {
            return;
        }

        if (days.length === 0) {
            var text = "{$object->rent_price}";
            $('#rent_price').val(text);
            $('#rent_price_label').html(text);
            return;
        }

        /*var tomorrow = new Date({$year}, {$month} - 1, {$day} + parseInt(days));
        $('#expected_return').val(completeZero(tomorrow.getDate()) + '/' + completeZero(tomorrow.getMonth() + 1) + '/' + tomorrow.getFullYear());*/

        if ('1' === '{$object->price_per_day}') {
            var price_per_day = {$rent_price} * parseInt(days);
            var text = price_per_day.toFixed(2).replace(".", ",");
            $('#rent_price').val(text);
            $('#rent_price_label').html(text);
        }
    }
{else}
    var _init_giveobject_js = function() {
        $('#btnsave').button('disable');

        $('#comments').keyup(function() {
            if ($('#comments').val().length > 200) {
                $('#comments').val($('#comments').val().substr(0, 200));
            }
            $('#remaining').text(200 - $('#comments').val().length);
        });

        $('#status').on('change',function() {
            validStatus()
        });
    }

    {if not $ajax}
    $(function () {
        _init_giveobject_js();
    });
    {/if}

    function validStatus() {
        var _lyes = $('#btnsave');
        if ($('#status').val() === 'null') {
            _lyes.button('disable');
        } else {
            _lyes.button('enable');
        }
    }

{/if}
</script>
{/block}
