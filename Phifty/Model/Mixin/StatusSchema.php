<?php
namespace Phifty\Model\Mixin;
use LazyRecord\Schema\MixinSchemaDeclare;

class StatusSchema extends MixinSchemaDeclare
{
    public function schema()
    {
        $this->column( 'status' )
            ->validValues(array(
                '草稿' => 'draft',
                '公開發佈' => 'publish'
            ))
            ->label('儲存為')
            ->renderAs('SelectInput')
            ;
    }
}

