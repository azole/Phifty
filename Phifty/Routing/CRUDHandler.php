<?php
namespace Phifty\Routing;
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
    implements ExpandableController
{
    /**
     * configurations
     */
    public $canCreate = true;

    public $canUpdate = true;

    public $canDelete = true;

    public $canBulkEdit = false;
    public $canBulkCopy = false;
    public $canBulkDelete = false;
    public $canEditInNewWindow = false;


    /**
     * @var array predefined data for new record
     */
    public $predefined = array();

    public $templatePage = 'CRUD/template/page.html';


    /** Namespace **/
    public $namespace; /* like News\... */

    /** Must **/
    public $modelClass; /* full-qualified model class */

    /** model short class name, optional, can be extracted from full-qualified model class name **/
    public $modelName;

    /** model object: record */
    public $model;



    /**
     * Current action object. (created from currentRecord)
     */
    public $currentAction;

    public $actionViewClass = 'AdminUI\\Action\\View\\StackView';

    public $actionViewOptions = array( 
        'ajax' => true,
        'close_button' => true,
    );

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

    public static function expand()
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

    public function init()
    {
        $this->vars['CRUD']['Object'] = $this;
        $this->vars['CRUD']['Title'] = $this->getListTitle();

        if( ! $this->namespace ) {
            // extract namespace from model class name
            $parts = explode('\\', ltrim($this->modelClass,'\\') );
            $this->namespace = $parts[0];
        }

        if( ! $this->modelName ) {
            $refl = new ReflectionClass( $this->modelClass );
            $this->modelName = $refl->getShortName();
        }
        parent::init();
    }

    public function assign( $name , $value )
    {
        $this->vars[ $name ] = $value;
    }

    public function assignVars( $args )
    {
        $this->vars = array_merge( $this->vars , $args );
    }

    /**
     * Returns edit form label
     *
     * @return string Label
     */
    public function getEditTitle()
    {
        $record = $this->getCurrentRecord();
        return $record->id
            ?  __('Create %1' , $record->getLabel() )
            :  __('Edit %1: %2', $record->getLabel() , $record->dataLabel() );
    }

    public function getListTitle()
    {
        return __('%1 Management' , $this->getModel()->getLabel() );
    }

    /* Return column names for CRUD List table */
    public function getListColumns()
    {
        if( $this->listColumns )
            return $this->listColumns;
        return $this->getModel()->getColumnNames();
    }


    /**
     * Get model object.
     *
     * @return Phifty\Model
     */
    public function getModel()
    {
        if( $this->model )
            return $this->model;
        return $this->model = new $this->modelClass;
    }

    public function getCollection()
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


    /**
     * Load record by primary key (id)
     *
     * @return mixed Record object.
     */
    public function loadRecord()
    {
        if( $this->currentRecord )
            return $this->currentRecord;
        $id = $this->request->param('id');
        $record = $this->getModel();
        $record->load( (int) $id );
        return $record;
    }

    public function render( $template , $args = array() , $engineOpts = array() )
    {
        // merge export vars
        $args = array_merge( $this->vars , $args );

        // var_dump( $args['CRUD']['Edit']['record']->data ); 
        return parent::render( $template , $args , $engineOpts );
    }

    /* renderer helpers */
    public function renderCrudIndexTiles()
    {
        $tiles   = array();

        // get the mounted path, and load the page through ajax region.
        // ajaxTile returns a javascript code block.
        $tiles[] = Region::ajaxTile( 'crud-list',  $this->getRoute()->getPath() . '/crud/list' );
        return $tiles;
    }

    /* this method should take a collection to render */
    public function renderCrudList( $args = array() )
    {
        return $this->render( 
            $this->namespace 
            . '/template/' 
            . $this->crudId 
            . '/list.html' , $args );
    }

    public function renderCrudEdit( $args = array() )
    {
        return $this->render( 
            $this->namespace 
            . '/template/' 
            . $this->crudId 
            . '/edit.html' , $args);
    }

    public function renderCrudPage( $args = array() )
    {
        return $this->render($this->templatePage,$args);
    }

    public function createCollectionPager($collection) 
    {
        $page     = $this->request->param('page') ?: 1;
        $pageSize = $this->request->param('pagenum') ?: $this->pageLimit;
        $count    = $collection->queryCount();
        $collection->page( $page ,$pageSize );
        return new RegionPager( $page, $count, $pageSize );
    }

    /**
     * CRUD List Prepare Data
     */
    public function listRegionActionPrepare()
    {
        $collection = $this->getCollection();
        $pager = $this->createCollectionPager($collection);
        $data = array(
            'Object' => $this,
            'Items' => $collection->items(),
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
    public function listRegionAction()
    {
        $this->listRegionActionPrepare();
        return $this->renderCrudList();
    }

    public function createRegionAction() 
    { 
        return $this->editRegionAction();
    }

    public function getDefaultData()
    {
        return $this->predefined;
    }

    public function getCurrentRecord()
    {
        if( $this->currentRecord )
            return $this->currentRecord;
        return $this->currentRecord = $this->loadRecord();
    }

    /**
     * Create record action object from record
     *
     * @return ActionKit\RecordAction
     */
    public function getRecordAction()
    {
        $record = $this->getCurrentRecord();
        $action = $record->id 
            ? $record->asUpdateAction()
            : $record->asCreateAction();
        return $action;
    }

    /**
     *
     */
    public function getActionView()
    {
        return $this->createActionView($this->currentAction);
    }

    /**
     * Create Action View from Action object.
     *
     * @param ActionKit\RecordAction
     */
    public function createActionView($action)
    {
        // {{ CRUD.Action.asView('AdminUI\\Action\\View\\StackView',{ ajax: true, close_button: true }).render|raw}}
        return $action->asView($this->actionViewClass,$this->actionViewOptions);
    }

    public function editRegionActionPrepare()
    {
        $record = $this->getCurrentRecord();
        $isCreate = $record->id ? false : true;

        // if the record is not loaded, we can use predefined values
        if( $isCreate ) {
            foreach( $this->getDefaultData() as $k => $v ) {
                $record->{ $k } = $v;
            }
        }

        $title = $this->getEditTitle();
        $this->currentAction = $this->getRecordAction($record);
        $data = array(
            'Object'      => $this,
            'Title'       => $title,
            'Action'      => $this->currentAction,
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
