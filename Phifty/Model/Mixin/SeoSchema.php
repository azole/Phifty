<?php
namespace Phifty\Model\Mixin;
use LazyRecord\Schema\MixinSchemaDeclare;

class SEOSchema extends MixinSchemaDeclare
{
    public function schema()
    {

        $this->column( 'seo_keywords' )
            ->text()
            ->label('頁面關鍵字');
            ;

        $this->column( 'seo_description' )
            ->text()
            ->label('頁面敘述')
            ;

        $this->column( 'seo_author' )
            ->varchar(32)
            ->label('作者');

        $this->column( 'seo_copyright' )
            ->varchar(32)
            ->label('版權宣告');

        $this->column( 'seo_title' )
            ->varchar(256)
            ->label('標題');
    }
}



