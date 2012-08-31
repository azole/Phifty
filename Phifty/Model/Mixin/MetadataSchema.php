<?php
namespace Phifty\Model\Mixin;
use LazyRecord\Schema\MixinSchemaDeclare;

class MetadataSchema extends MixinSchemaDeclare
{
    function schema()
    {
        $this->column( 'created_on' )
            ->timestamp()
            ->renderAs('DateTimeInput')
            ->label( _('Created on') )
            ->default(function() {
                return date('c');
            })
            ;

        $this->column( 'updated_on' )
            ->timestamp()
            ->renderAs('DateTimeInput')
            ->default(function() {
                return date('c');
            })
            ->label( _('Updated on') )
            ;

        $this->column( 'created_by' )
            ->integer()
            ->refer( kernel()->currentUser->userModelClass )
            ->default(function() { 
                if( isset($_SESSION) ) {
                    return kernel()->currentUser->id; 
                }
            })
            ->renderAs('SelectInput')
            ->label('建立者')
            ;

        // XXX: inject value to beforeUpdate
        $this->column( 'updated_by' )
            ->integer()
            ->refer( kernel()->currentUser->userModelClass )
            ->default(function() { 
                if( isset($_SESSION) ) {
                    return kernel()->currentUser->id; 
                }
            })
            ->renderAs('SelectInput')
            ->label('更新者')
            ;
    }
}

