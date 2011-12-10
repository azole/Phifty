<?php
namespace Phifty;
use Phifty\Routing\RouteSet;
use Phifty\Web\RegionPagerDisplay;
use Phifty\Region;

// XXX: Extract plugin, AdminUI stuff to AdminUICRUDSet
abstract class CRUDRouteSet extends RouteSet 
{
    public $namespace; /* like News\... */
    public $modelClass; /* full-qualified model class */
    public $modelName;  /* model name */
    public $model;
    public $crudId;
    public $currentRecord;

    /* vars to be export to template */
    public $vars = array();

    /* collection order */
    public $defaultOrder = array('id', 'desc');
    public $listColumns;

    function init()
    {
        $this->vars['CRUD'] = array( 'object' => $this );
        $model = $this->getModel(); // init model column

		if( $model->getColumn('updated_on') )
			$this->defaultOrder = array('updated_on', 'desc');
		elseif( $model->getColumn('created_on') )
			$this->defaultOrder = array('created_on', 'desc');
    }

    function assign( $name , $value )
    {
        $this->vars[ $name ] = $value;
    }

    function assignVars( $args )
    {
        foreach( $args as $k => $v )
            $this->vars[ $k ] = $v;
    }

    function getListTitle()
    {
        return __('%1 Management' , $this->getModel()->getLabel() );
    }

    /* Return column names for CRUD List table */
    function getListColumns()
    {
        if( $this->listColumns )
            return $this->listColumns;
        return $this->getModel()->getColumnNames();
    }

    function getModel()
    {
        if( $this->model )
            return $this->model;
        return $this->model = new $this->modelClass;
    }

    function getCollection()
    {
        $model = $this->getModel();
        $collection = $model->asCollection();

        # support for lang query,
        # make sure the model has defined lang column for I18N
        if( webapp()->getPlugin('I18N') 
            && $model->getColumn('lang') )
        {
            if( $this->env->request->has('_data_lang') ) {
                if( $lang = $this->env->request->_data_lang ) 
                    $collection->where(array( 'lang' => $lang ));
            }
            else {
                $lang = webapp()->currentLang();
                $collection->where(array( 'lang' => $lang ));
            }
        }

        if( $this->defaultOrder )
            $collection->order( $this->defaultOrder[0] , $this->defaultOrder[1] );
        return $collection;
    }

    function loadRecord()
    {
        $id = $this->env->request->id;
        $record = $this->getModel();
        $record->load( (int) $id );
        return $this->currentRecord = $record;
    }


    function render( $template , $args = array() , $engineOpts = array() )
    {
        // merge export vars
        $args = array_merge( $this->vars , $args );

        // var_dump( $args['CRUD']['Edit']['record']->data ); 
        return parent::render( $template , $args , $engineOpts );
    }


    /* renderer helpers */
    function renderCrudIndexTiles()
    {
        $tiles   = array();
        $tiles[] = Region::ajaxTile( 'crud-list',  $this->getRoute()->getPrefix() . '/crud/list' );
        return $tiles;
    }

    /* this method should take a collection to render */
    function renderCrudList( $args = array() )
    {
        return $this->render( 'plugins/' . $this->namespace . '/template/crud_list_' . $this->crudId . '.html' , $args );
    }

    function renderCrudEdit( $args = array() )
    {
        return $this->render( 'plugins/' . $this->namespace . '/template/crud_edit_' . $this->crudId . '.html', $args );
    }

    function renderCrudPage( $args = array() )
    {
        return $this->render( 'Core/template/crud.html' , $args );
    }

    /* **************** Router methods ****************** */
    function table()
    {
        $this->route( '/' , 'crud_index' );
        $this->route( '/crud/list' , 'crud_list' );
        $this->route( '/crud/edit' , 'crud_edit' );
        $this->route( '/crud/create' , 'crud_create' );
        $this->route( '/edit' , 'edit' );
        $this->route( '/create' , 'create' );
    }

    /*
     * CRUD List Prepare Data
     */
    function crud_list_prepare()
    {
        $collection = $this->getCollection();
        $env = $this->env;
        $order_column = $env->request->_order_column;
        $order_by     = $env->request->_order_by;

        if( ! $order_column )
            $order_column = 'id';
        if( ! $order_by )
            $order_by = 'desc';
        $collection->order( $order_column , $order_by );
        $collection->fetch();

        $pager = $collection->pager( $env->request->page , $env->request->pagenum, 10);
        $pagerDisplay = new RegionPagerDisplay($pager);

        $this->vars['CRUD']['List'] = array(
            'items' => $pager->items(),
            'pager' => $pagerDisplay,
            'title' => $this->getListTitle(),
            'columns' => $this->getListColumns(),
        );
    }

    /*
        crud_list:

            builtin vars
            - _order_column => {{column}}
            - _order_by     => {{asc|desc}}
    */
    function crud_list( )
    {
        $this->crud_list_prepare();
        return $this->renderCrudList();
    }

    function crud_create() 
    { 
        return $this->crud_edit();
    }


    function crud_edit_prepare($predefined = array())
    {
        $env = $this->env;
        $record = $this->loadRecord();
        $isCreate = $record->id ? false : true;

        // if the record is not loaded, we can use predefined values
        if( $isCreate ) {
            foreach( $predefined as $k => $v ) {
                $record->{ $k } = $v;
            }
        }

        $actionClass = ( $isCreate 
            ? $this->namespace . '::Action::Create' . $this->modelName 
            : $this->namespace . '::Action::Update' . $this->modelName );

        $action = $isCreate ? $record->asCreateAction() : $record->asUpdateAction();
        $title = $isCreate
            ?  __('Create %1' , $record->getLabel() )
            :  __('Edit %1 %2', $record->getLabel() , (int) $record->id );


        $this->vars['CRUD']['Edit'] = array(
            'title'       => $title,
            'actionClass' => $actionClass,
            'action'      => $action,
            'record'      => $record,
        );
    }


    /* crud_edit_{{ id }} template must be declare */
    // TODO: Support create with pre-defined value 
    function crud_edit()
    {
        $this->crud_edit_prepare();
        return $this->renderCrudEdit();
    }


    // XXX: let admin page could be pushed by tiles.
    function create()
    {
        $tiles = array();
        $tiles[] = $this->crud_edit();
        return $this->renderCrudPage(array( 'tiles' => $tiles ));
    }

    function edit()
    {
        $tiles = array();
        $tiles[] = $this->crud_edit();
        return $this->renderCrudPage(array( 'tiles' => $tiles ));
    }


    /* crud_index is a tiled page,
     * you can use tile to push template blocks into it. */
    function crud_index()
    {
        $tiles = $this->renderCrudIndexTiles();
        return $this->renderCrudPage(array( 'tiles' => $tiles ));
    }

}
