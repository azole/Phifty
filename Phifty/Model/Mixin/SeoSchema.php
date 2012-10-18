<?php
namespace Phifty\Model\Mixin;
use LazyRecord\Schema\MixinSchemaDeclare;

class SEOSchema extends MixinSchemaDeclare
{
    public function schema()
    {

        $this->column( 'seo_keywords' )
            ->text()
            ->label('SEO 關鍵字');
            ;

        $this->column( 'seo_description' )
            ->text()
            ->label('SEO 敘述')
            ;

        $this->column( 'seo_title' )
            ->varchar(256)
            ->label('SEO 標題');
    }
}



