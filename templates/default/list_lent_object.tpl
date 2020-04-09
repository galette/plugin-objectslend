{extends file="page.tpl"}
{block name="content"}
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
    {foreach $rents as $rent}
            <tr>
                <td>{$rent->rent_id}</td>
                <td>{$rent->status_text}</td>
                <td>{$rent->date_begin|date_format:_T("Y-m-d")}</td>
                <td>{$rent->date_forecast|date_format:_T("Y-m-d")}</td>
                <td>{$rent->date_end|date_format:_T("Y-m-d")}</td>
                <td>{$rent->nom_adh} {$rent->prenom_adh}</td>
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
<div class="button-container">
    <p>
        <a href="{path_for name="objectslend_objects"}" class="button">
            <i class="fas fa-th-list"></i> {_T string="Return" domain="objectslend"}
        </a>
    </p>
</div>
{/block}
