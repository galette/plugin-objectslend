<link rel="stylesheet" type="text/css" href="{path_for name="plugin_res" data=["plugin" => $module_id, "path" => "galette_lend.css"]}" media="screen"/>
{if isset($olendsprefs) && $olendsprefs->showFullsize()}
<link rel="stylesheet" type="text/css" href="{path_for name="plugin_res" data=["plugin" => $module_id, "path" => "featherlight-1.7.0/featherlight.min.css"]}" media="screen"/>
<script type="text/javascript" src="{path_for name="plugin_res" data=["plugin" => $module_id, "path" => "featherlight-1.7.0/featherlight.min.js"]}"></script>
<script type="text/javascript">
    var _init_fullimage = function() {
        $('.picture').featherlight({
            targetAttr: 'data-fullsrc',
            type: 'image',
            beforeOpen: function(p) {
                var _img = $(p.currentTarget);
                _img.attr('data-fullsrc', _img.attr('src').replace(/thumbnail/, 'photo'));
            }
        }).css('cursor', 'pointer').attr('title', '{_T string="Click to view fullsize image" domain="objectslend" escape="js"}');
    }

    $(function(){
        _init_fullimage();
    });
</script>
{/if}
<script type="text/javascript" src="{path_for name="plugin_res" data=["plugin" => $module_id, "path" => "lend.js"]}"></script>
