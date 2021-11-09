{extends file="page.tpl"}
{block name="content"}
<form action="{if $category->category_id}{path_for name="objectslend_category_action_edit" data=["id" => $category->category_id]}{else}{path_for name="objectslend_category_action_add"}{/if}" method="post" enctype="multipart/form-data">
    <input type="hidden" name="category_id" value="{$category->category_id}">
    <div class="bigtable">
        <fieldset class="cssform">
            <legend class="ui-state-active ui-corner-top">{_T string="Category" domain="objectslend"}</legend>
            <div>
                <p>
                    <span class="bline">{_T string="Name" domain="objectslend"}</span>
                    <input type="text" name="name" size="60" maxlength="100" value="{$category->name}" required>
                </p>
            </div>
            <div>
                <p>
                    <label for="is_active" class="bline">{_T string="Is active" domain="objectslend"}</label>
                    <input type="checkbox" name="is_active" id="is_active" value="true"{if $category->is_active} checked="checked"{/if}>
                </p>
            </div>
            <div>
                <p>
                </p>
            </div>
            <div>
                <p>
                    <span class="bline">{_T string="Picture:" domain="objectslend"}</span>
                    <img src="{if $category->category_id}{path_for name="objectslend_photo" data=["type" => "category", "mode" => "thumbnail", "id" => $category->category_id]}{else}{path_for name="objectslend_photo" data=["type" => "category", "mode" => "thumbnail"]}{/if}?rand={$time}"
                        class="picture"
                        width="{$category->picture->getOptimalThumbWidth($olendsprefs)}"
                        height="{$category->picture->getOptimalThumbHeight($olendsprefs)}"
                        alt="{_T string="Category photo" domain="objectslend"}"/><br/>
    {if $category->picture->hasPicture()}
                    <input type="checkbox" name="del_picture" id="del_picture" value="1"/><span class="labelalign"><label for="del_picture">{_T string="Delete image" domain="objectslend"}</label></span><br/>
    {/if}

                    <input class="labelalign" type="file" name="picture"/>
                </p>
            </div>
        </fieldset>
    </div>
    <div class="button-container">
        <button type="submit" name="save" class="action">
            <i class="fas fa-save"></i>
            {_T string="Save"}
        </button>
        <a href="{path_for name="objectslend_categories"}" class="button">
            <i class="fas fa-th-list"></i> {_T string="Back to list" domain="objectslend"}
        </a>
        {include file="forms_types/csrf.tpl"}
    </div>
</form>
{/block}
