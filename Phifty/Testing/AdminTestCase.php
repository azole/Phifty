<?php
namespace Phifty\Testing;

class AdminTestCase extends Selenium2TestCase 
{
    protected $urlOf = [
        'login' => '',
        'news' => 'http://phifty.dev/bs/news',
        'newsCategory' => 'http://phifty.dev/bs/news_category',
        'contacts' => 'http://phifty.dev/bs/contacts',
        'contactGroups' => 'http://phifty.dev/bs/contact_groups',
        'product' => 'http://phifty.dev/bs/product'
    ];

    protected function login( $transferTo='' ) 
    {
        // hard code here
        $this->url('http://phifty.dev/bs/login');

        $accountInput = get('input[name=account]');
        $accountInput->value('admin');

        $passwordInput = get('input[name=password]');
        $passwordInput->value('admin');

        get('.submit')->click();

        if ( '' !== $transferTo  ) {
            $this->url( $this->urlOf[ $transferTo ] );
        }

        wait();
    }

    protected function logout()
    {
        get('#operation .buttons a[href]')->click();
        wait();
    }

    protected function isCreated() 
    {
        $msg = get('.message.success', 5)->text();
        $o = get_obj(4); 
        $o->assertContains('created', $msg );
    }

    protected function isUpdated() 
    {

        $msg = get('.message.success', 5)->text();
        $o = get_obj(4); 
        $o->assertContains('updated', $msg );
    }

    protected function isDeleted() 
    {

        $msg = get('.jGrowl-message', 5)->text();
        $o = get_obj(4);
        $o->assertRegExp('/(deleted|刪除成功)/', $msg );
    }

    protected function isUploaded() 
    {
        $msg = get('.jGrowl-message', 5)->text();
        $o = get_obj(4); 
        $o->assertContains('created', $msg );
    }
}
