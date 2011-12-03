<?php
/*

$page       = @$_REQUEST['page'];
$page_nums  = @$_REQUEST['page_nums'];
$pager = new Pager( $page ,$page_nums);

$result = $dbc->query( "SELECT * FROM posts WHERE " . $pager->to_limit_sql() );

....

    $cnt = get_record_counts();
    $pager->set_total_items( $cnt );

    // render_pagerow( $pager );
    
    $page_total = $pager->get_total_pages();
    for ( $p = 1 ; $p < $page_total ; $p++ ) {
        ?><a class="pagenumber" href="?page=<?=$p?>"><?=$p?></a><?php
    }

 */

namespace Phifty;

class Pager {

    var $current_page;
    var $per_page;
    var $total_items;
    var $total_pages;

    function __construct( $page = 1 , $pagenum  = 10 )
    {
        if( $page == null )
            $page = 1;

        if( $pagenum == null ) 
            $pagenum = 10;

        $this->per_page     = $pagenum;
        $this->current_page = $page;
    }

    public function set_total( $total ) 
    {
        $this->total_items  = $total;
        $this->calculate();
    }

    public function set_perpage( $num )
    {
        $this->per_page = $num; 
    }

    public function set_page( $num )
    {
        $this->current_page = $num; 
    }

    public function calculate()
    {
        $this->start_from   = ($this->current_page - 1) * $this->per_page;
        $this->total_pages = $this->total_items > 0 ? ceil( $this->total_items / $this->per_page ) : 1;
    }

}

?>
