<?php
namespace Phifty\Model\Mixin;
use LazyRecord\Schema\MixinSchemaDeclare;

class MetaSchema extends MixinSchemaDeclare
{
    function schema()
    {
        $this->column( 'created_on' )
            ->timestamp()
            ->renderAs('DateTimeInput')
            ->label( _('Created on') )
            ;

        $this->column( 'updated_on' )
            ->timestamp()
            ->renderAs('DateTimeInput')
            ->label( _('Updated on') )
            ;

        $this->column( 'created_by' )
            ->integer()
            ->default(function() { 
                return kernel()->currentUser->id; 
            })
            ->label('建立者')
            ;

        $this->column( 'updated_by' )
            ->integer()
            ->default(function() { 
                return kernel()->currentUser->id; 
            })
            ->label('更新者')
            ;
    }
}

