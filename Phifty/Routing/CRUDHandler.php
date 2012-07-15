<?php
namespace Phifty\Routing;
use Phifty\Web\RegionPagerDisplay;
use Phifty\Web\RegionPager;
use Phifty\Region;
use Phifty\Controller;

use ReflectionClass;


/**
 * Current CRUD template structure:
 *
 *    templates/
 *          {crudId}/create.html (***not yet)
 *          {crudId}/edit.html
 *          {crudId}/list.html
 */
abstract class CRUDHandler extends Controller
    implements ExpandableInterface
{
    /**
     * configurations
     */
    public $canCreate = true;

    public $canUpdate = true;

    public $canDelete = true;

    public $canBulkEdit = false;


    /**
     * @var array predefined data for new record
     */
    public $predefined = array();


    /** Namespace **/
    public $namespace; /* like News\... */

    /** Must **/
    public $modelClass; /* full-qualified model class */

    /** model short class name, optional, can be extracted from full-qualified model class name **/
    public $modelName;

    /** model object: record */
    public $model;

    public $crudId;

    public $currentRecord;

    public $pageLimit = 15;


    /* vars to be export to template */
    public $vars = array();

    /** 
     * Collection order 
     */
    public $defaultOrder = array('id', 'desc');

    public $listColumns;

    static function expand()
    {
        $class = get_called_class();
        $routeset = new \Roller\RouteSet;
        $routeset->add( '/'            , $class . ':indexAction' );

        $routeset->add( '/crud/list'   , $class . ':listRegionAction');
        $routeset->add( '/crud/edit'   , $class . ':editRegionAction');
        $routeset->add( '/crud/create' , $class . ':createRegionAction');

        $routeset->add( '/edit'        , $class . ':editAction');
        $routeset->add( '/create'      , $class . ':createAction');
        return $routeset;
    }

    function init()
    {
        $this->vars['CRUD']['Object'] = $this;
        $this->vars['CRUD']['Title'] = $this->getListTitle();

        // extract namespace from model class name
        $parts = explode('\\', ltrim($this->modelClass,'\\') );
        $refl = new ReflectionClass( $this->modelClass );

        if( ! $this->namespace ) {
            $this->namespace = $parts[0];
        }

        if( ! $this->modelName ) {
            $this->modelName = $refl->getShortName();
        }

        parent::init();
    }

    function assign( $name , $value )
    {
        $this->vars[ $name ] = $value;
    }

    function assignVars( $args )
    {
        $this->vars = array_merge( $this->vars , $args );
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
        if( kernel()->plugin('I18N') && $langColumn = $model->getColumn('lang') )
        {
            if( $lang = $this->request->param('_data_lang') ) {
                $collection->where()
                    ->equal('lang', $lang);
            }
        }

        $orderColumn = $this->request->param('_order_column');
        $orderBy     = $this->request->param('_order_by');

        if( $orderColumn && $orderBy ) {
            $collection->order( $orderColumn , $orderBy );
        }
        elseif( $this->defaultOrder ) {
            $collection->order( $this->defaultOrder[0] , $this->defaultOrder[1] );
        } 
        return $collection;
    }

    function loadRecord()
    {
        $id = $this->request->param('id');
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

        // get the mounted path, and load the page through ajax region.
        // ajaxTile returns a javascript code block.
        $tiles[] = Region::ajaxTile( 'crud-list',  $this->getRoute()->getPath() . '/crud/list' );
        return $tiles;
    }

    /* this method should take a collection to render */
    function renderCrudList( $args = array() )
    {
        return $this->render( 
            $this->namespace 
            . '/template/' 
            . $this->crudId 
            . '/list.html' , $args );
    }

    function renderCrudEdit( $args = array() )
    {
        return $this->render( 
            $this->namespace 
            . '/template/' 
            . $this->crudId 
            . '/edit.html' , $args);
    }

    function renderCrudPage( $args = array() )
    {
        return $this->render( 
            'CRUD/template/page.html',
            $args );
    }


    /**
     * CRUD List Prepare Data
     */
    function listRegionActionPrepare()
    {
        $page = $this->request->param('page') ?: 1;
        $pageSize = $this->request->param('pagenum') ?: $this->pageLimit;

        // SQLBuilder query doesn't support __clone, for that 
        // we have to create two collection for two queries.
        $totalItems = $this->getCollection()->queryCount();
        $collection   = $this->getCollection()->page( $page ,$pageSize );
        $items = $collection->items();

        $pager = new RegionPager;
        $pager->currentPage = $page;
        $pager->calculatePages( $totalItems , $pageSize );
        $data = array(
            'Object' => $this,
            'Items' => $items,
            'Pager' => $pager,
            'Title' => $this->getListTitle(),
            'Columns' => $this->getListColumns(),
        );

        // var_dump( $collection->getLastSQL() , $collection->getVars() ); 
        foreach( $data as $k => $v ) {
            $this->vars['CRUD'][ $k ] = $v;
        }
    }

    /*
        listRegionAction:

            builtin vars
            - _order_column => {{column}}
            - _order_by     => {{asc|desc}}
    */
    function listRegionAction()
    {
        $this->listRegionActionPrepare();
        return $this->renderCrudList();
    }


    function createRegionAction() 
    { 
        return $this->editRegionAction();
    }


    function editRegionActionPrepare()
    {
        $record = $this->loadRecord();
        $isCreate = $record->id ? false : true;

        // if the record is not loaded, we can use predefined values
        if( $isCreate ) {
            foreach( $this->predefined as $k => $v ) {
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
            'Action'      => $action,
            'Record'      => $record,
        );
        foreach( $data as $k => $v ) {
            $this->vars['CRUD'][$k] = $v;
        }
    }


    /* editRegionAction_{{ id }} template must be declare */
    // TODO: Support create with pre-defined value 
    public function editRegionAction()
    {
        $this->editRegionActionPrepare();
        return $this->renderCrudEdit();
    }


    // XXX: let admin page could be pushed by tiles.
    public function createAction()
    {
        $tiles = array();
        $tiles[] = $this->editRegionAction();
        return $this->renderCrudPage(array( 'tiles' => $tiles ));
    }

    public function editAction()
    {
        $tiles = array();
        $tiles[] = $this->editRegionAction();
        return $this->renderCrudPage(array( 'tiles' => $tiles ));
    }



    /* indexAction is a tiled page,
     * you can use tile to push template blocks into it. */
    public function indexAction()
    {
        $tiles = $this->renderCrudIndexTiles();
        return $this->renderCrudPage(array( 'tiles' => $tiles ));
    }



}
