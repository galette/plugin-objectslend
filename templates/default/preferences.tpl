{extends file="page.tpl"}
{block name="content"}
<form action="{path_for name="store_objectlend_preferences"}" method="post">
    <div id="prefs_tabs">
        <fieldset class="cssform" id="objectslend">
            <legend class="ui-state-active ui-corner-top">{_T string="Plugin preferences" domain="objectslend"}</legend>
            <p>
                <span class="bline tooltip" title="{_T string="Allow a member (not staff neither admin) to borrow an object. If set to 'No', only admin and staff members can access the 'Take object' page" domain="objectslend"}">{_T string="Members can borrow:" domain="objectslend"}</span>
                <span class="tip">{_T string="Allow a member (not staff neither admin) to borrow an object. If set to 'No', only admin and staff members can access the 'Take object' page" domain="objectslend"}</span>
                <input type="radio" name="ENABLE_MEMBER_RENT_OBJECT" id="yes_memberborrow" value="1" {if $lendsprefs.ENABLE_MEMBER_RENT_OBJECT eq '1'}checked="checked"{/if}/><label for="yes_memberborrow">{_T string="Yes"}</label>
                <input type="radio" name="ENABLE_MEMBER_RENT_OBJECT" id="no_memberborrow" value="0" {if $lendsprefs.ENABLE_MEMBER_RENT_OBJECT eq '0'}checked="checked"{/if}/><label for="no_memberborrow">{_T string="No"}</label>
            </p>
            {* TODO: hide this one if ENABLE_MEMBER_RENT_OBJECT is off *}
            <p>
                <span class="bline tooltip" title="{_T string="Automatically generate a contribution for the member of the amount of the rental price of the object" domain="objectslend"}">{_T string="Generate contribution:" domain="objectslend"}</span>
                <span class="tip">{_T string="Automatically generate a contribution for the member of the amount of the rental price of the object" domain="objectslend"}</span>
                <input type="radio" name="AUTO_GENERATE_CONTRIBUTION" id="yes_contrib" value="1" {if $lendsprefs.AUTO_GENERATE_CONTRIBUTION eq '1'}checked="checked"{/if}/><label for="yes_contrib">{_T string="Yes"}</label>
                <input type="radio" name="AUTO_GENERATE_CONTRIBUTION" id="no_contrib" value="0" {if $lendsprefs.AUTO_GENERATE_CONTRIBUTION eq '0'}checked="checked"{/if}/><label for="no_contrib">{_T string="No"}</label>
            </p>
            {* TODO: hide this one if AUTO_GENERATE_CONTRIBUTION is off *}
            <p>
                <label for="contribution_type" class="bline">{_T string="Contribution type:" domain="objectslend"}</label>
                <select name="GENERATED_CONTRIBUTION_TYPE_ID" id="contribution_type">
                    <option value="0">{_T string="Choose a contribution type" domain="objectslend"}</option>
    {foreach from=$ctypes item=ctype key=id}
                    <option value="{$id}"{if $lendsprefs.GENERATED_CONTRIBUTION_TYPE_ID eq $id} selected="selected"{/if}>{$ctype}</option>
    {/foreach}
                </select>
            </p>
            {* TODO: hide this one if AUTO_GENERATE_CONTRIBUTION is off *}
            <p>
                <label for="contrib_text" class="bline tooltip" title="{_T string="Comment text to add on generated contribution" domain="objectslend"}">{_T string="Contribution text:" domain="objectslend"}</label>
                <span class="tip">{_T string="Comment text to add on generated contribution. Automatically replaced values (put into curly brackets): <br/>- NAME: Name<br/>- DESCRIPTION: Description<br/>- SERIAL_NUMBER: Serial number<br/>- PRICE: Price<br/>- RENT_PRICE: Borrow price<br/>- WEIGHT: Weight<br/>- DIMENSION: Dimensions" domain="objectslend"}</span>
                <input type="text" size="100" name="GENERATED_CONTRIB_INFO_TEXT" id="contrib_text" value="{$lendsprefs.GENERATED_CONTRIB_INFO_TEXT}"/>
            </p>

        </fieldset>
        <fieldset class="cssform" id="objectslendimages">
            <legend class="ui-state-active ui-corner-top">{_T string="Images related" domain="objectslend"}</legend>
            <p>
                <label for="max_thumb_height" class="bline">{_T string="Max thumb height (in px)" domain="objectslend"}</label>
                <input type="text" name="THUMB_MAX_HEIGHT" id="max_thumb_height" value="{$lendsprefs.THUMB_MAX_HEIGHT}"/>
            </p>
            <p>
                <label for="max_thumb_width" class="bline">{_T string="Max thumb width (in px)" domain="objectslend"}</label>
                <input type="text" name="THUMB_MAX_WIDTH" id="max_thumb_width" value="{$lendsprefs.THUMB_MAX_WIDTH}"/>
            </p>
            <p>
                <span class="bline tooltip" title="{_T string="Display images in objects and categories lists"}">{_T string="Images in lists:" domain="objectslend"}</span>
                <span class="tip">{_T string="Display images in objects and categories lists" domain="objectslend"}</span>
                <input type="radio" name="VIEW_THUMBNAIL" id="yes_view_thumb" value="1" {if $lendsprefs.VIEW_THUMBNAIL eq '1'}checked="checked"{/if}/><label for="yes_view_thumb">{_T string="Yes"}</label>
                <input type="radio" name="VIEW_THUMBNAIL" id="no_view_thumb" value="0" {if $lendsprefs.VIEW_THUMBNAIL eq '0'}checked="checked"{/if}/><label for="no_view_thumb">{_T string="No"}</label>
            </p>
            <p>
                <span class="bline tooltip" title="{_T string="Show fullsize image or just thumbs" domain="objectslend"}">{_T string="Enable fullsize:" domain="objectslend"}</span>
                <span class="tip">{_T string="Will permit to see fullsize image clicking on thumbnails.<br/>If disabled, only thumbnails will be displayed, but full images are still kept." domain="objectslend"}</span>
                <input type="radio" name="VIEW_FULLSIZE" id="yes_view_fullsize" value="1" {if $lendsprefs.VIEW_FULLSIZE eq '1'}checked="checked"{/if}/><label for="yes_view_fullsize">{_T string="Yes"}</label>
                <input type="radio" name="VIEW_FULLSIZE" id="no_view_fullsize" value="0" {if $lendsprefs.VIEW_FULLSIZE eq '0'}checked="checked"{/if}/><label for="no_view_fullsize">{_T string="No"}</label>
            </p>
        </fieldset>
        <fieldset class="cssform" id="objectslend">
            <legend class="ui-state-active ui-corner-top">{_T string="Display preferences" domain="objectslend"}</legend>
            <p>
                <span class="bline">{_T string="View category:" domain="objectslend"}</span>
                <input type="radio" name="VIEW_CATEGORY" id="yes_view_category" value="1" {if $lendsprefs.VIEW_CATEGORY eq '1'}checked="checked"{/if}/><label for="yes_view_category">{_T string="Yes"}</label>
                <input type="radio" name="VIEW_CATEGORY" id="no_view_category" value="0" {if $lendsprefs.VIEW_CATEGORY eq '0'}checked="checked"{/if}/><label for="no_view_category">{_T string="No"}</label>
            </p>
            <p>
                <span class="bline">{_T string="View forecast return date:" domain="objectslend"}</span>
                <input type="radio" name="VIEW_DATE_FORECAST" id="yes_view_dateforecast" value="1" {if $lendsprefs.VIEW_DATE_FORECAST eq '1'}checked="checked"{/if}/><label for="yes_view_dateforecast">{_T string="Yes"}</label>
                <input type="radio" name="VIEW_DATE_FORECAST" id="no_view_dateforecats" value="0" {if $lendsprefs.VIEW_DATE_FORECAST eq '0'}checked="checked"{/if}/><label for="no_view_dateforecats">{_T string="No"}</label>
            </p>
            <p>
                <span class="bline">{_T string="View description" domain="objectslend"}</span>
                <input type="radio" name="VIEW_DESCRIPTION" id="yes_view_description" value="1" {if $lendsprefs.VIEW_DESCRIPTION eq '1'}checked="checked"{/if}/><label for="yes_view_description">{_T string="Yes"}</label>
                <input type="radio" name="VIEW_DESCRIPTION" id="no_view_description" value="0" {if $lendsprefs.VIEW_DESCRIPTION eq '0'}checked="checked"{/if}/><label for="no_view_description">{_T string="No"}</label>
            </p>
            <p>
                <span class="bline">{_T string="View dimensions:" domain="objectslend"}</span>
                <input type="radio" name="VIEW_DIMENSION" id="yes_view_dimension" value="1" {if $lendsprefs.VIEW_DIMENSION eq '1'}checked="checked"{/if}/><label for="yes_view_dimension">{_T string="Yes"}</label>
                <input type="radio" name="VIEW_DIMENSION" id="no_view_dimension" value="0" {if $lendsprefs.VIEW_DIMENSION eq '0'}checked="checked"{/if}/><label for="no_view_dimension">{_T string="No"}</label>
            </p>
            <p>
                <span class="bline">{_T string="View borrow price:" domain="objectslend"}</span>
                <input type="radio" name="VIEW_LEND_PRICE" id="yes_view_lendprice" value="1" {if $lendsprefs.VIEW_LEND_PRICE eq '1'}checked="checked"{/if}/><label for="yes_view_lendprice">{_T string="Yes"}</label>
                <input type="radio" name="VIEW_LEND_PRICE" id="no_view_lendprice" value="0" {if $lendsprefs.VIEW_LEND_PRICE eq '0'}checked="checked"{/if}/><label for="no_view_lendprice">{_T string="No"}</label>
            </p>
            <p>
                <span class="bline tooltip" title="{_T string="View the objects buy price sum on the list under the category" domain="objectslend"}">{_T string="View price sum:" domain="objectslend"}</span>
                <span class="tip">{_T string="View the objects buy price sum on the list under the category" domain="objectslend"}</span>
                <input type="radio" name="VIEW_LIST_PRICE_SUM" id="yes_view_pricesum" value="1" {if $lendsprefs.VIEW_LIST_PRICE_SUM eq '1'}checked="checked"{/if}/><label for="yes_view_pricesum">{_T string="Yes"}</label>
                <input type="radio" name="VIEW_LIST_PRICE_SUM" id="no_view_pricesum" value="0" {if $lendsprefs.VIEW_LIST_PRICE_SUM eq '0'}checked="checked"{/if}/><label for="no_view_pricesum">{_T string="No"}</label>
            </p>
            <p>
                <span class="bline">{_T string="View buy price:" domain="objectslend"}</span>
                <input type="radio" name="VIEW_PRICE" id="yes_view_price" value="1" {if $lendsprefs.VIEW_PRICE eq '1'}checked="checked"{/if}/><label for="yes_view_price">{_T string="Yes"}</label>
                <input type="radio" name="VIEW_PRICE" id="no_view_price" value="0" {if $lendsprefs.VIEW_PRICE eq '0'}checked="checked"{/if}/><label for="no_view_price">{_T string="No"}</label>
            </p>
            <p>
                <span class="bline">{_T string="View serial number:" domain="objectslend"}</span>
                <input type="radio" name="VIEW_SERIAL" id="yes_view_serial" value="1" {if $lendsprefs.VIEW_SERIAL eq '1'}checked="checked"{/if}/><label for="yes_view_serial">{_T string="Yes"}</label>
                <input type="radio" name="VIEW_SERIAL" id="no_view_serial" value="0" {if $lendsprefs.VIEW_SERIAL eq '0'}checked="checked"{/if}/><label for="no_view_serial">{_T string="No"}</label>
            </p>
            <p>
                <span class="bline">{_T string="View weight" domain="objectslend"}</span>
                <input type="radio" name="VIEW_WEIGHT" id="yes_view_weight" value="1" {if $lendsprefs.VIEW_WEIGHT eq '1'}checked="checked"{/if}/><label for="yes_view_weight">{_T string="Yes"}</label>
                <input type="radio" name="VIEW_WEIGHT" id="no_view_weight" value="0" {if $lendsprefs.VIEW_WEIGHT eq '0'}checked="checked"{/if}/><label for="no_view_weight">{_T string="No"}</label>
            </p>
        </fieldset>
    </div>
    <div class="button-container">
        <button type="submit" name="saveprefs" class="action">
            <i class="fas fa-save"></i>
            {_T string="Save"}
        </button>
    </div>
</form>
{/block}
