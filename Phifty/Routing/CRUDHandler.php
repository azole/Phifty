<?php
namespace Phifty\Routing;
use Phifty\Web\RegionPagerDisplay;
use Phifty\Region;
use Phifty\Controller;


/**
 * Current CRUD template structure:
 *
 *    templates/
 *          {crudId}/create.html (***not yet)
 *          {crudId}/edit.html
 *          {crudId}/list.html
 */

abstract class CRUDHandler extends Controller
{
    /**
     * configurations
     */
    public $canCreate = true;

    public $canUpdate = true;

    public $canDelete = true;




    public $namespace; /* like News\... */

    public $modelClass; /* full-qualified model class */

    public $modelName;  /* model name */

    public $model;

    public $crudId;

    public $currentRecord;

    public $pageLimit = 10;


    /* vars to be export to template */
    public $vars = array();

    /* collection order */
    public $defaultOrder = array('id', 'desc');

    public $listColumns;

    static function expand()
    {
        $class = get_called_class();
        $routeset = new \Roller\RouteSet;
        $routeset->add( '/'            ,"$class:crud_index" );
        $routeset->add( '/crud/list'   ,"$class:crud_list");
        $routeset->add( '/crud/edit'   ,"$class:crud_edit");
        $routeset->add( '/crud/create' ,"$class:crud_create");
        $routeset->add( '/edit'        ,"$class:edit");
        $routeset->add( '/create'      ,"$class:create");
        return $routeset;
    }

    function init()
    {
        $this->vars['CRUD']['Object'] = $this;
        $this->vars['CRUD']['Title'] = $this->getListTitle();
        parent::init();
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
        if( kernel()->getPlugin('I18N') 
            && $langColumn = $model->getColumn('lang') )
        {
            if( $this->env->request->has('_data_lang') ) {
                if( $lang = $this->env->request->_data_lang ) 
                    $collection->where(array( 'lang' => $lang ));
            }
            else {
                $lang = kernel()->locale->current;
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
        $tiles[] = Region::ajaxTile( 'crud-list',  $this->getRoute()->getPath() . '/crud/list' );
        return $tiles;
    }

    /* this method should take a collection to render */
    function renderCrudList( $args = array() )
    {
        return $this->render( 
            'plugins/' 
            . $this->namespace 
            . '/template/' . $this->crudId . '/list.html' , $args );
    }

    function renderCrudEdit( $args = array() )
    {
        return $this->render( 
            'plugins/' 
            . $this->namespace 
            . '/template/' . $this->crudId . '/edit.html' , $args);
    }

    function renderCrudPage( $args = array() )
    {
        return $this->render( 'Core/template/crud/page.html' , $args );
    }


    /*
     * CRUD List Prepare Data
     */
    function crud_list_prepare()
    {
        $collection = $this->getCollection();
        $env = $this->env;
        $order_column = $this->request->param('_order_column');
        $order_by     = $this->request->param('_order_by');

        if( ! $order_column )
            $order_column = 'id';
        if( ! $order_by )
            $order_by = 'desc';
        $collection->order( $order_column , $order_by );

        $pager = $collection->pager( $env->request->page ?: 1 , $env->request->pagenum ?: $this->pageLimit );
        $pagerDisplay = new RegionPagerDisplay( $pager );
        $data = array(
            'Object' => $this,
            'Items' => $pager->items(),
            'Pager' => $pagerDisplay,
            'Title' => $this->getListTitle(),
            'Columns' => $this->getListColumns(),
        );
        foreach( $data as $k => $v ) {
            $this->vars['CRUD'][ $k ] = $v;
        }
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

        $data = array(
            'Object'      => $this,
            'Title'       => $title,
            'ActionClass' => $actionClass,
            'Action'      => $action,
            'Record'      => $record,
        );
        foreach( $data as $k => $v ) {
            $this->vars['CRUD'][$k] = $v;
        }
    }


    /* crud_edit_{{ id }} template must be declare */
    // TODO: Support create with pre-defined value 
    public function crud_edit()
    {
        $this->crud_edit_prepare();
        return $this->renderCrudEdit();
    }


    // XXX: let admin page could be pushed by tiles.
    public function create()
    {
        $tiles = array();
        $tiles[] = $this->crud_edit();
        return $this->renderCrudPage(array( 'tiles' => $tiles ));
    }

    public function edit()
    {
        $tiles = array();
        $tiles[] = $this->crud_edit();
        return $this->renderCrudPage(array( 'tiles' => $tiles ));
    }



    /* crud_index is a tiled page,
     * you can use tile to push template blocks into it. */
    public function crud_index()
    {
        $tiles = $this->renderCrudIndexTiles();
        return $this->renderCrudPage(array( 'tiles' => $tiles ));
    }



}
