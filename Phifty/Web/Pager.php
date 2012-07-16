<?php
namespace Phifty\Web;


/** 
 * A simple pager, does not depends on Pager interface.
 *
 * @version 2
 */
class Pager
{
    public $first_text;
    public $last_text;
    public $next_text;
    public $prev_text;

    public $showHeader = false;
    public $showNavigator = true;
    public $wrapperClass = 'pager';
    public $whenOverflow  = true;

    public $rangeLimit = 3;

    public $totalPages = 0;
    public $pageSize = 20;
    public $currentPage = 1;


    /**
     *
     * @param integer current page
     * @param integer total size
     * @param integer page size (optional)
     */
    function __construct()
    {
        $this->first_text = _('Page.First');
        $this->last_text  = _('Page.Last');
        $this->next_text  = _('Page.Next');
        $this->prev_text  = _('Page.Previous');
        if( $args = func_get_args() ) {
            if( 2 === count($args) ) {
                $this->currentPage = $args[0] ?: 1;
                $this->calculatePages($args[1]);
            }
            elseif( 3 === count($args) ) {
                $this->currentPage = $args[0] ?: 1;
                $this->calculatePages($args[1],$args[2]);
            }
        }
    }

    /**
     * @param integer $total
     * @param integer $size = null  (optional)
     */
    function calculatePages($total,$size = 20)
    {
        $this->pageSize = $size ?: 20;
        $this->totalPages = $total > 0 ? (int) ($total / $this->pageSize ) + 1 : 0;
    }


    function mergeQuery( $orig_params , $params = array() )
    {
        $params = array_merge(  $orig_params , $params );
        return '?' . http_build_query( $params );
    }

    function render_link( $num , $text = null , $moreclass = "" , $disabled = false )
    {
        if( $text == null )
            $text = $num;

        if( $disabled )
            return $this->render_link_dis( $text , $moreclass );

        $args = array_merge( $_GET , $_POST );
        $href = $this->mergeQuery( $args , array( "page" => $num ) );

        return <<<EOF
<a class="pager-link $moreclass" href="$href">$text</a>
EOF;
    }

    function render_link_dis( $text , $moreclass = "" ) {
        return <<<EOF
<a class="pager-link pager-disabled $moreclass">$text</a>
EOF;
    }

    function __toString() 
    {
        return $this->render();
    }


    function render2()
    {
        $heredoc = new \Phifty\View\Heredoc('twig');
        $heredoc->content =<<<TWIG
<div class="pager">
    {% for i in 0 .. totalPages %}

    {% endfor %}
</div>
TWIG;
        $html = $heredoc->render(array( 
            'currentPage' => $this->currentPage,
            'totalPages'  => $this->totalPages,
        ));
    }


    function render()
    {


        $cur = $this->currentPage;
        $total_pages = $this->totalPages;

        if( $this->whenOverflow && $this->totalPages == 1 ) {
            return "";
        }


        $pagenum_start = $cur > $this->rangeLimit ? $cur - $this->rangeLimit : 1 ;
        $pagenum_end   = $cur + $this->rangeLimit < $total_pages ?  $cur + $this->rangeLimit : $total_pages;

        $output = "";
        $output .= '<div class="'.$this->wrapperClass.'">';


        if( $this->showHeader )
            $output .= '<div class="pager-current">' . _('Pager.page') .': ' . $this->currentPage . '</div>';
    
        if( $this->showNavigator ) {

            if( $cur > 1 )
                $output .= $this->render_link( 1       , $this->first_text , 'pager-first' , $cur == 1 );

            if( $cur > 5 )
                $output .= $this->render_link( $cur - 5 , _("Pager.Prev 5 Pages") , 'pager-number' );

            if( $cur > 1 )
                $output .= $this->render_link( $cur -1 , $this->prev_text  , 'pager-prev'  , $cur == 1 );
        }

        if( $cur > 5 )
            $output .= $this->render_link( 1 , 1 , 'pager-number' ) . ' ... ';

        for ( $i = $pagenum_start ; $i <= $pagenum_end ; $i++ ) {
            if( $i == $this->currentPage ) 
                $output .= $this->render_link( $i , $i , 'pager-number active pager-number-current' );
            else 
                $output .= $this->render_link( $i , $i , 'pager-number' );
        }


        if( $cur + 5 < $total_pages )
            $output .= ' ... ' . $this->render_link( $total_pages , $total_pages , 'pager-number' );

        if( $this->showNavigator ) {

            if( $cur < $total_pages )
                $output .= $this->render_link( $cur + 1, 
                            $this->next_text , 'pager-next' , $cur == $this->totalPages );

            if( $cur + 5 < $total_pages )
                $output .= $this->render_link( $cur + 5, 
                            _("Pager.Next 5 Pages") , 'pager-number' );

            if( $total_pages > 1 && $cur < $total_pages )
                $output .= $this->render_link( $this->totalPages,
                            $this->last_text , 'pager-last' , $cur == $this->totalPages );
        }

        $output .= '</div>';
        return $output;
    }
}

