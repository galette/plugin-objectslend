{extends file="page.tpl"}
{block name="content"}
    <form id="filtre" method="POST" action="{path_for name="objectslend_filter_categories"}">
        <div id="listfilter">
            <label for="filter_str">{_T string="Search:"}&nbsp;</label>
            <input type="text" name="filter_str" id="filter_str" value="{$filters->filter_str}" type="search" placeholder="{_T string="Enter a value"}"/>&nbsp;
            {_T string="Active:" domain="objectslend"}
            <input type="radio" name="active_filter" id="filter_dc_active" value="{GaletteObjectsLend\Repository\Categories::ALL_CATEGORIES}"{if $filters->active_filter eq constant('GaletteObjectsLend\Repository\Categories::ALL_CATEGORIES')} checked="checked"{/if}>
            <label for="filter_dc_active" >{_T string="Don't care"}</label>
            <input type="radio" name="active_filter" id="filter_yes_active" value="{GaletteObjectsLend\Repository\Categories::ACTIVE_CATEGORIES}"{if $filters->active_filter eq constant('GaletteObjectsLend\Repository\Categories::ACTIVE_CATEGORIES')} checked="checked"{/if}>
            <label for="filter_yes_active" >{_T string="Yes"}</label>
            <input type="radio" name="active_filter" id="filter_no_active" value="{GaletteObjectsLend\Repository\Categories::INACTIVE_CATEGORIES}"{if $filters->active_filter eq constant('GaletteObjectsLend\Repository\Categories::INACTIVE_CATEGORIES')} checked="checked"{/if}>
            <label for="filter_no_active" >{_T string="No"}</label>
            <input type="submit" class="inline" value="{_T string="Filter"}"/>
            <input name="clear_filter" type="submit" value="{_T string="Clear filter"}">
        </div>
        <div class="infoline">
            {$nb_categories} {if $nb_categories != 1}{_T string="categories" domain="objectslend"}{else}{_T string="category" domain="objectslend"}{/if}
            <div class="fright">
                <label for="nbshow">{_T string="Records per page:"}</label>
                <select name="nbshow" id="nbshow">
                    {html_options options=$nbshow_options selected=$numrows}
                </select>
                <noscript> <span><input type="submit" value="{_T string="Change"}" /></span></noscript>
            </div>
        </div>
    </form>
<table class="listing">
    <thead>
        <tr>
            <th class="id_row">&nbsp;</th>
            <th>
                <a href="{path_for name="objectslend_categories" data=["option" => {_T string='order' domain="routes"}, "value" => "GaletteObjectsLend\Repository\Categories::ORDERBY_NAME"|constant]}">
                    {_T string="Name" domain="objectslend"}
                    {if $filters->orderby eq constant('GaletteObjectsLend\Repository\Categories::ORDERBY_NAME')}
                        {if $filters->ordered eq constant('GaletteObjectsLend\Filters\CategoriesList::ORDER_ASC')}
                    <img src="{base_url}/{$template_subdir}images/down.png" width="10" height="6" alt=""/>
                        {else}
                    <img src="{base_url}/{$template_subdir}images/up.png" width="10" height="6" alt=""/>
                        {/if}
                    {/if}
                </a>
            </th>
            <th class="id_row">
                {_T string="Active" domain="objectslend"}
            </th>
            <th class="actions_row">
                {_T string="Actions"}
            </th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <td colspan="4" class="center">
                {_T string="Pages:"}<br/>
                <ul class="pages">{$pagination}</ul>
            </td>
        </tr>
    </tfoot>
    <tbody>
        {foreach from=$categories item=categ}
            <tr class="{if $categ@index is odd}even{else}odd{/if}">
                <td>
                    {$categ->category_id}
                </td>
                <td>
    {if $olendsprefs->imagesInLists()}
                    <img src="{path_for name="objectslend_photo" data=["type" => {_T string="category" domain="objectslend_routes"}, "mode" => {_T string="thumbnail" domain="objectslend_routes"}, "id" => $categ->category_id]}"
                        class="picture"
                        width="{$categ->picture->getOptimalThumbWidth($olendsprefs)}"
                        height="{$categ->picture->getOptimalThumbHeight($olendsprefs)}"
                        alt=""/>
    {/if}
                    {$categ->name}
                </td>
                <td align="center">
                    {if $categ->is_active}
                        <img src="{base_url}/{$template_subdir}images/icon-on.png" alt="{_T string="Active"}" width="16" height="16"/>
                    {/if}
                </td>
                <td class="center nowrap">
                    <a href="{path_for name="objectslend_category" data=["action" => {_T string="edit" domain="routes"}, "id" => $categ->category_id]}">
                        <img src="{base_url}/{$template_subdir}images/icon-edit.png" alt="{_T string="[mod]"}" width="16" height="16" title="{_T string="Edit %category" pattern="/%category/" domain="objectslend" replace=$categ->name}"/>
                    </a>
                    <a class="delete" href="{path_for name="objectslend_remove_category" data=["id" => $categ->category_id]}"><img src="{base_url}/{$template_subdir}images/icon-trash.png" alt="{_T string="[del]"}" width="16" height="16" title="{_T string="Remove %category from database" domain="objectslend" pattern="/%category/" replace=$categ->name}"/></a>
                </td>
            </tr>
        {/foreach}
    </tbody>
</table>
{/block}

{block name="javascripts"}
    <script type="text/javascript">
        $(function(){
            {include file="js_removal.tpl"}
            $('#nbshow').change(function() {
                this.form.submit();
            });
        });
    </script>
{/block}
