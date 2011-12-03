<?php

namespace Phifty;


class SimpleCRUD 
{
    public $collection;
    public $page;
    public $perPage = 15;
    public $title;
    public $options = array();
    public $columns = array();
    public $template = 'crud.tpl';

    function __construct( $collection )
    {
        if( @$_REQUEST['page'] )
            $this->page = $_REQUEST['page'];

        if( @$_REQUEST['perpage'] )
            $this->perPage = $_REQUEST['perPage'];

        $this->collection = $collection;
    }

    function page($page , $perPage = null)
    {
        $this->page = $page;
        if( $perPage )
            $this->perPage = $perPage;
        return $this;
    }

    function title( $title )
    {
        $this->title = $title;
        return $this;
    }

    function getTitle()
    {
        if( $this->title )
            return $this->title;

        return $this->getModel()->getLabel() . ' ' . _('Management');
    }

    function option($name,$value = true)
    {
        $this->options[ $name ] = $value;
        return $this;
    }

    function getCollection()
    {
        return $this->collection;
    }

    function getModel()
    {
        return $this->getCollection()->getModel();
    }

    function addColumns($names)
    {
        $args = (array) $names;
        array_unshift( $args , array($this->columns) );
        call_user_func_array('array_push', $args );
        return $this;
    }

    function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }


    function getLabels()
    {
        return $this->getModel()->getColumnLabels();
    }

    function getColumns()
    {

        $model = $this->getModel();
        $list = array();
        if( $this->columns ) {
            foreach( $this->columns as $name ) {
                array_push($list,$model->getColumn($name) );
            }
        } else {
            $columnHash = $model->getColumns();
            foreach( $columnHash as $k => $column )
                array_push($list,$column);
        }
        return $list;
    }

    function render()
    {

        $collection = $this->getCollection();
        $collection->page( $this->page , $this->perPage );
        $items = $collection->items();


        /*
        $config = array_merge( array(
                "delete_btn" => true,
                "edit_btn" => true
                ) , $config );
         */

        // $pager = new CollectionPager( @$_REQUEST['page'] , @$_REQUEST['pagenum'] , $cltn );

        /* init region pager display */
        $pager = new \Phifty\Pager( $this->page , $this->perPage );
        $regionPagerDisplay = new \Phifty\Web\RegionPagerDisplay($pager);

        $view = webapp()->view();
        $view->title = $this->getTitle();
        $view->items = $items;
        $view->pager = $regionPagerDisplay;
        $view->columns = $this->getColumns();

        foreach( $this->options as $k => $v )
            $view->$k = $v;

        $view->render('crud.tpl');

        /*
        $smt = webapp()->view();
        $smt->assign( "items" , $items );
        $smt->assign( "pager_display" , $pager_display );

        $model = $cltn->model;
        $columns = $model->columns;

        $labels = array();
        $accessors = array();
        {
            if( @$config["columns"] ) {

                if( is_string( $config["columns"] ) )
                    $config[ 'columns' ] = explode(" ", $config[ 'columns' ]);

                foreach( $config['columns'] as $name ) {
                    $c = $model->getColumn( $name );
                    $label = $c->getLabel();
                    if( ! $label ) 
                        $label = ucfirst($name);

                    array_push( $labels , $label );
                    array_push( $accessors , $name );
                }
            } else {
                foreach( $columns as $key => $c ) {
                    $label = $c->get_label();
                    if( ! $label ) 
                        $label = ucfirst($key);

                    array_push( $labels , $label );
                    array_push( $accessors , $key );
                }
            }
        }

        $smt->assign( "td" , false );
        $smt->assign( "tr" , false );
        $smt->assign( "delete_btn" , @$config['delete_btn'] );
        $smt->assign( "edit_btn" , @$config['edit_btn'] );

        $smt->assign( "form_path" , @$config["form_path"] );
        $smt->assign( "title" , @$config["title"] );
        $smt->assign( "extend_part" , @$config["extend_part"] );

        $smt->assign( "column_render" , @$config["column_render"] );

        $smt->assign( "model_class" , $cltn->model_class );
        $smt->assign( "columns" , $model->columns );
        $smt->assign( "labels" , $labels );
        $smt->assign( "accessors" , $accessors );
        # $smt->display( "admin_prizesub_list.tpl" );
        $smt->display( "admin_simple_crud.tpl" );
         */
    }






}

?>
