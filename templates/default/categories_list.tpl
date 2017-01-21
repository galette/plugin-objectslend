{if $msg_saved}
    <div id="infobox">
        <h1>{_T string="CATEGORIES LIST.SAVED"}</h1>
    </div>
{/if}
{if $msg_canceled}
    <div id="warningbox">
        <h1>{_T string="CATEGORIES LIST.CANCELED"}</h1>
    </div>
{/if}
{if $msg_deleted}
    <div id="errorbox">
        <h1>{_T string="CATEGORIES LIST.DELETED"}</h1>
    </div>
{/if}
<p>
    {$nb_categories} {_T string="CATEGORIES LIST.NB RESULT"}
</p>
<table class="listing">
    <thead>
        <tr>
            <th><a href="?tri=category_id&direction={if $tri eq 'category_id' && $direction eq 'asc'}desc{else}asc{/if}">#</a>{if $tri eq 'category_id' && $direction eq 'asc'} <img src="{$template_subdir}images/down.png">{elseif $tri eq 'category_id' && $direction eq 'desc'} <img src="{$template_subdir}images/up.png">{/if}</th>
            <th>{_T string="CATEGORIES LIST.IMAGE"}</th>
            <th><a href="?tri=name&direction={if $tri eq 'name' && $direction eq 'asc'}desc{else}asc{/if}">{_T string="CATEGORIES LIST.TEXT"}</a>{if $tri eq 'name' && $direction eq 'asc'} <img src="{$template_subdir}images/down.png">{elseif $tri eq 'name' && $direction eq 'desc'} <img src="{$template_subdir}images/up.png">{/if}</th>
            <th><a href="?tri=is_active&direction={if $tri eq 'is_active' && $direction eq 'asc'}desc{else}asc{/if}">{_T string="CATEGORIES LIST.IS ACTIVE"}</a>{if $tri eq 'is_active' && $direction eq 'asc'} <img src="{$template_subdir}images/down.png">{elseif $tri eq 'is_active' && $direction eq 'desc'} <img src="{$template_subdir}images/up.png">{/if}</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$categories item=categ name=list}
            <tr>
                <td class="tbl_line_{if $smarty.foreach.list.index is odd}even{else}odd{/if}">{$categ->category_id}</td>
                <td class="tbl_line_{if $smarty.foreach.list.index is odd}even{else}odd{/if}"><img src="picture.php?category_id={$categ->category_id}{if $view_category_thumb}&thumb=1{/if}"></td>
                <td class="tbl_line_{if $smarty.foreach.list.index is odd}even{else}odd{/if}">{$categ->name}</td>
                <td class="tbl_line_{if $smarty.foreach.list.index is odd}even{else}odd{/if}" align="center">{if $categ->is_active}<img src="picts/check.png">{/if}</td>
                <td class="tbl_line_{if $smarty.foreach.list.index is odd}even{else}odd{/if}" width="40">
                    <a href="category_edit.php?category_id={$categ->category_id}"><img src="picts/edit.png" title="{_T string="CATEGORIES LIST.EDIT"}" border="0"></a>
                    <a href="javascript:void(0)"><img src="picts/delete.png" title="{_T string="CATEGORIES LIST.DELETE"}" border="0" onClick="confirmDelete('{$categ->name}', '{$categ->category_id}')"></a>
                </td>
            </tr>
        {/foreach}
    </tbody>
</table>
<p>
    &nbsp;
</p>
<form action="category_edit.php?status_id=new" method="get">
    <div class="button-container">
        <input type="submit" id="status_create" value="{_T string="CATEGORIES LIST.CREATE"}">
    </div>
</form>
<script>
    function confirmDelete(nom, categ_id) {
    if (confirm('{_T string="CATEGORIES LIST.CONFIRM DELETE"}' + nom + ' ?')) {
    window.location = 'category_delete.php?category_id=' + categ_id;
}
return false;
}
</script>
