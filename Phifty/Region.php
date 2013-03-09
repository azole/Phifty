<?php
namespace Phifty;
use Phifty\View\Heredoc;

class Region
{

    public static function ajaxTile($regionId, $ajaxPath, $args = array() )
    {
        $args['_is_ajax'] = 1;
        $heredoc = new \Phifty\View\Heredoc('twig');
        $heredoc->content =<<<'TWIG'
    <div id="{{ regionId }}"> </div>
    <script type="text/javascript">
    $(function() {
        if( window.console )
            console.log('Load ajax region #{{ regionId }} from {{ ajaxPath }}');
        $('#{{ regionId }}').asRegion().load( '{{ ajaxPath }}' , {{ args|json_encode|raw }} );
    });
    </script>
TWIG;

        return $heredoc->render(array(
            'regionId' => $regionId,
            'ajaxPath' => $ajaxPath,
            'args' => $args
        ));
    }

}
