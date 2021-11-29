{if $ajax}
    {assign var="extend" value="ajax.tpl"}
{else}
    {assign var="extend" value="page.tpl"}
{/if}
{extends file=$extend}
{block name="content"}
    {assign var=dformat value={_T string="Y-m-d"}}
<div class="bigtable">
    <table class="listing">
        <thead>
            <tr>
                <th>{_T string="Id" domain="objectslend"}</th>
                <th>{_T string="Status" domain="objectslend"}</th>
                <th>{_T string="Begin date" domain="objectslend"}</th>
                <th>{_T string="End date" domain="objectslend"}</th>
                <th>{_T string="Return" domain="objectslend"}</th>
                <th>{_T string="Name" domain="objectslend"}</th>
                <th>{_T string="Comments" domain="objectslend"}</th>
            </tr>
        </thead>
        <tbody>
    {foreach from=$rents item=rent name=rentlist}
            <tr class="{if $smarty.foreach.rentlist.index is odd}odd{else}even{/if}">
                <td>{$rent->rent_id}</td>
                <td>{$rent->status_text}{if $rent->in_stock} ({_T string="In stock" domain="objectslend"}){/if}</td>
                <td>{$rent->date_begin|date_format:$dformat}</td>
                <td>{$rent->date_forecast|date_format:$dformat}</td>
                <td>{$rent->date_end|date_format:$dformat}</td>
                <td>
                    {$rent->nom_adh} {$rent->prenom_adh}
                </td>
                <td>{$rent->comments}</td>
            </tr>
    {foreachelse}
            <tr>
                <td colspan="7" class="center">{_T string="No lend found" domain="objectslend"}</td>
            </tr>
    {/foreach}
        </tbody>
    </table>
</div>
{if !$ajax}
<div class="button-container">
    <p>
        <a href="{path_for name="objectslend_objects"}" class="button">
            <i class="fas fa-th-list"></i> {_T string="Return" domain="objectslend"}
        </a>
    </p>
</div>
{/if}
{/block}
