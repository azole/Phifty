<?php

namespace Phifty\Action;
use Phifty\Action\RecordAction;

class CreateRecordAction 
    extends RecordAction
{

    function create($args)
    {
        $ret = $this->record->create( $args );

        /* error checking */
        if( is_array($ret) )
            $this->convertRecordValidation( $ret );
        if( $ret ) 
            return $this->createError();
        if( $this->record->id )
            return $this->createSuccess();
        return $this->createError();
    }

    function run()
    {
        /* default run method , to run create action */
        return $this->create( $this->args );
    }

    function createSuccess() 
    {
        return $this->success( __("%1 Record has been created." , $this->record->getLabel() ) , array( 
            'id' => $this->record->id
        ));
    }

    function createError() 
    {
        return $this->error( __('Can not create %1 record.' , $this->record->getLabel() ) );
    }

}


