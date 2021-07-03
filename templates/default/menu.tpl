{if $login->isLogged()}
<h1 class="nojs">
    {_T string="Objects lend" domain="objectslend"}
</h1>
<ul>
   <!--<li{if $PAGENAME eq "objects_list.php" || $PAGENAME eq "take_object.php" || $PAGENAME eq "give_object_back.php"
        || $PAGENAME eq "give_more_objects_back.php" || $PAGENAME eq "take_more_objects_away.php"} class="selected"{/if}>
       <a href="{$galette_base_path}{$lend_dir}objects_list.php">
           {_T string="Objects list" domain="objectslend"}
       </a>
   </li>-->
    <li{if $cur_route eq "objectslend_objects" or $cur_route eq "objectslend_object_take" or $cur_route eq "objectslend_show_object_lend" or $cur_route eq "objectslend_object_edit"} class="selected"{/if}>
       <a href="{path_for name="objectslend_objects"}">
           {_T string="Objects list" domain="objectslend"}
       </a>
    </li>
    {if $login->isAdmin() || $login->isStaff()}
    <li{if $cur_route eq "objectslend_object_add"} class="selected"{/if}>
        <a href="{path_for name="objectslend_object_add"}">{_T string="Add an object" domain="objectslend"}</a>
    </li>
    <li{if $cur_route eq "objectslend_statuses" or $cur_route eq "objectlends_status_edit"} class="selected"{/if}>
        <a href="{path_for name="objectslend_statuses"}">{_T string="Borrow status" domain="objectslend"}</a>
    </li>
    <li{if $cur_route eq "objectslend_status_add"} class="selected"{/if}>
        <a href="{path_for name="objectslend_status_add"}">{_T string="Add a status" domain="objectslend"}</a>
    </li>
    <li{if $cur_route eq "objectslend_categories" or $cur_route eq "objectslend_category_edit"} class="selected"{/if}>
        <a href="{path_for name="objectslend_categories"}">{_T string="Object categories" domain="objectslend"}</a>
    </li>
    <li{if $cur_route eq "objectslend_category_add"} class="selected"{/if}>
        <a href="{path_for name="objectslend_category_add"}">{_T string="Add a category" domain="objectslend"}</a>
    </li>
    <li{if $cur_route eq "objectslend_preferences"} class="selected"{/if}>
        <a href="{path_for name="objectslend_preferences"}">{_T string="Preferences" domain="objectslend"}</a>
    </li>
    {/if}
</ul>
{/if}
