{extends file="page.tpl"}
{block name="content"}
<form action="{path_for name="objectslend_status_action" data=["action" => $action, "id" => $status->status_id]}" method="post">
    <input type="hidden" name="status_id" value="{$status->status_id}">
    <div class="bigtable">
        <fieldset class="cssform">
            <legend class="ui-state-active ui-corner-top">{_T string="Status" domain="objectslend"}</legend>
            <div>
                <p>
                    <span class="bline">{_T string="Status:" domain="objectslend"}</span>
                    <input type="text" name="text" size="60" maxlength="100" value="{$status->status_text}" required="required">
                </p>
            </div>
            <div>
                <p>
                    <label for="in_stock" class="bline tooltip" title="{_T string="Is object at home or borrowed" domain="objectslend"}">
                        {_T string="At home:" domain="objectslend"}
                    </label>
                    <span class="tip">{_T string="Check if the object is available to be borrowed;<br/>uncheck if object is already borrowed and should be given back" domain="objectslend"}</span>
                    <input type="checkbox" name="in_stock" id="in_stock" value="true"{if $status->in_stock} checked="checked"{/if}>
                </p>
            </div>
            <div>
                <p>
                    <label for="is_active" class="bline">{_T string="Active:" domain="objectslend"}</label>
                    <input type="checkbox" name="is_active" id="is_active" value="true"{if $status->is_active} checked="checked"{/if}>
                </p>
            </div>
            <div>
                <p>
                    <label for="rent_day_number" class="bline tooltip" title="{_T string="Number of days to rent per default" domain="objectslend"}">
                        {_T string="Days for rent" domain="objectslend"}
                    </label>
                    <span class="tip">{_T string="Number of days to rent per default" domain="objectslend"}<br/>{_T string="used to compute return date" domain="objectslend"}</span>
                    <input type="text" name="rent_day_number" size="5" maxlength="6" value="{$status->rent_day_number}">
                </p>
            </div>
        </fieldset>
    </div>
    <div class="button-container">
        <button type="submit" name="save" class="action">
            <i class="fas fa-save"></i>
            {_T string="Save"}
        </button>
        <a href="{path_for name="objectslend_statuses"}" class="button">
            <i class="fas fa-th-list"></i>
            {_T string="Back to list" domain="objectslend"}
        </a>
    </div>
</form>
{/block}
