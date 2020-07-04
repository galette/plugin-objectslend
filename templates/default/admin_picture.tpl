{extends file="page.tpl"}
{block name="content"}
<form action="{path_for name="objectslend_adminimages_action"}" method="post">
    <div class="button-container">
        <button type="submit" name="save_categories">
            <i class="fas fa-hdd" aria-hidden="true"></i>
            {_T string="Backup categories pictures" domain="objectslend"}
        </button>
        <button type="submit" name="save_objects">
            <i class="fas fa-hdd" aria-hidden="true"></i>
            {_T string="Backup objects pictures" domain="objectslend"}
        </button>
        <button type="submit" name="restore_objects">
            <i class="far fa-hdd" aria-hidden="true"></i>
            {_T string="Restore objects pictures from database" domain="objectslend"}
        </button>
        <button type="submit" name="restore_categories">
            <i class="far fa-hdd" aria-hidden="true"></i>
            {_T string="Restore categories pictures from database" domain="objectslend"}
        </button>
    </div>
</form>
{/block}
