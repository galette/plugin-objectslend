{extends file="page.tpl"}
{block name="content"}
<form action="{if $object->object_id}{path_for name="objectslend_object_action_edit" data=["id" => $object->object_id]}{else}{path_for name="objectslend_object_action_add"}{/if}" method="post" enctype="multipart/form-data">
    <input type="hidden" name="object_id" value="{$object->object_id}">
    <div class="bigtable">
        <fieldset class="cssform">
            <legend class="ui-state-active ui-corner-top">{_T string="Object" domain="objectslend"}</legend>
            <p>
                <label for="name" class="bline">{_T string="Name:" domain="objectslend"}</label>
                <input type="text" name="name" id="name" maxlength="100" size="60" value="{$object->name}" required="required">
            </p>
            <p>
                <label for="description" class="bline">{_T string="Description:" domain="objectslend"}</label>
                <input type="text" name="description" id="description" maxlength="500" size="80" value="{$object->description}" required>
            </p>
            {if $lendsprefs.VIEW_CATEGORY}
            <p>
                <label for="category_id" class="bline">{_T string="Category:" domain="objectslend"}</label>
                <select name="category_id" id="category_id">
                    <option value="">{_T string="--- Select a category ---" domain="objectslend"}</option>
                    {foreach from=$categories item=categ}
                        <option value="{$categ->category_id}"{if $object->category_id eq $categ->category_id} selected="selected"{/if}>
                            {$categ->name}
                            ({$categ->objects_nb})
                        </option>
                    {/foreach}
                </select>
            </p>
            {/if}
            <p>
                <label for="serial" class="bline">{_T string="Serial number:" domain="objectslend"}</label>
                <input type="text" name="serial" id="serial" maxlength="30" size="20" value="{$object->serial_number}">
            </p>
            <p>
                <label for="price" class="bline">{_T string="Price:" domain="objectslend"}</label>
                <input type="text" name="price" id="price" size="10" style="text-align: right" value="{$object->price}">
            </p>
            <p>
                <span class="bline">{_T string="Borrow price:" domain="objectslend"}</span>
                <input type="text" name="rent_price" size="10" style="text-align: right" value="{$object->rent_price}">&nbsp;{$object->getCurrency()}
            </p>
            <p>
                <label class="bline tooltip" for="price_per_day" title="{_T string="The price applies on each rental day if checked, only once otherwise" domain="objectslend"}">
                    {_T string="Price per rental day:" domain="objectslend"}
                </label>
                <input type="checkbox" name="price_per_day" id="price_per_day" value="true"{if $object->price_per_day} checked="checked"{/if}>
            </p>
            <p>
                <label for="dimension" class="bline">{_T string="Dimensions:" domain="objectslend"}</label>
                <input type="text" name="dimension" id="dimension" maxlength="100" size="60" value="{$object->dimension}">&nbsp;{_T string="Cm"}
            </p>
            <p>
                <label for="weight" class="bline">{_T string="Weight:" domain="objectslend"}</label>
                <input type="text" name="weight" id="weight" size="10" style="text-align: right" value="{$object->weight}">&nbsp;{_T string="Kg"}
            </p>
            <p>
                <label class="bline" for="is_active">{_T string="Active:" domain="objectslend"}</label>
                <input type="checkbox" id="is_active" name="is_active" value="true"{if $object->is_active} checked="checked"{/if}>
            </p>
            {if !$object->object_id}
            <p>
                <label for="1st_status" class="bline">{_T string="Where is the object?" domain="objectslend"}</label>
                <select name="1st_status" id="1st_status">
                    {foreach from=$statuses item=sta}
                        <option value="{$sta->status_id}">{$sta->status_text}{if $sta->in_stock} ({_T string="in stock" domain="objectslend"}){/if}</option>
                    {/foreach}
                </select>
            </p>
            {/if}
        </fieldset>
        <fieldset>
            <legend class="ui-state-active ui-corner-top">{_T string="Object's photo" domain="objectslend"}</legend>
                <p>
                    <div class="exemple">{_T string="The file must be smaller than 2 Mb and its name should not contains whitespace!"}</div>
                    <img
                        src="{if $object->object_id}{path_for name="objectslend_photo" data=["type" => "object", "mode" => "thumbnail", "id" => $object->object_id]}{else}{path_for name="objectslend_photo" data=["type" => "object", "mode" => "thumbnail"]}{/if}?rand={$time}"
                        class="picture"
                        width="{$object->picture->getOptimalThumbWidth($olendsprefs)}"
                        height="{$object->picture->getOptimalThumbHeight($olendsprefs)}"
                        alt="{_T string="Object photo" domain="objectslend"}"
                    /><br/>
                    <input type="checkbox" name="del_picture" id="del_picture" value="1"/>
                    <span class="labelalign"><label for="del_picture">{_T string="Delete image" domain="objectslend"}</label></span><br/>
                    <input type="file" name="picture" id="object_picture">
                </p>
        </fieldset>
    </div>
    <div class="button-container">
        <button type="submit" name="save" class="action">
            <i class="fas fa-save fa-fw"></i> {_T string="Save"}
        </button>
        {if $object->object_id ne ''}
            <a href="{path_for name="objectslend_object_clone" data=["id" => $object->object_id]}" class="button">
                <i class="fas fa-clone"></i> {_T string="Duplicate" domain="objectslend"}
            </a>
            <a href="{path_for name="objectslend_object_print" data=["id" => $object->object_id]}" class="button">
                <i class="fas fa-print"></i> {_T string="Print object" domain="objectslend"}
            </a>
        {/if}
        <p>
            <a href="{path_for name="objectslend_objects"}" class="button">
                <i class="fas fa-th-list"></i> {_T string="Back to list" domain="objectslend"}
            </a>
        </p>
    </div>
</form>

{if $object->object_id}
    <h4 class="center">{_T string="History" domain="objectslend"}</h4>
    {include file="list_lent_object.tpl" rents=$object->rents ajax=true}
{* FIXME: should not be here, create a specific page. *}
<form action="{if $object->object_id}{path_for name="objectslend_object_action_edit" data=["id" => $object->object_id]}{else}{path_for name="objectslend_object_action_add"}{/if}" method="post" class="cssform">
    <fieldset class="cssform">
        <legend class="ui-state-active ui-corner-top">{_T string="Change status" domain="objectslend"}</legend>
        <p>
            <label class="bline" for="new_status">{_T string="Status"}</label>
            <input type="hidden" name="object_id" value="{$object->object_id}">
            <select name="new_status">
                {foreach from=$statuses item=sta}
                    <option value="{$sta->status_id}"{if $sta->in_stock} selected="selected"{/if}>{$sta->status_text}{if $sta->in_stock} ({_T string="In stock"}){/if}</option>
                {/foreach}
            </select>
        </p>
        <p>
            <label for="new_comment" class="bline">{_T string="Comments" domain="objectslend"}</label>
            <input type="text" name="new_comment" maxlength="200" size="60"/>
        </p>
        <p>
            <label class="bline" for="new_adh">{_T string="Member"}</label>
            <select name="new_adh" class="nochosen">
                <option value="">{_T string="No member" domain="objectslend"}</option>
                {foreach $members.list as $k=>$v}
                    <option value="{$k}"{if $login->id == $k} selected="selected"{/if}>{$v}</option>
                {/foreach}
            </select>
        </p>
    </fieldset>
    <div class="button-container">
        <button type="submit" name="status" class="action">
            <i class="fas fa-save fa-fw"></i> {_T string="Change status" domain="objectslend"}
        </button>
    </div>
</form>
{/if}
{/block}
