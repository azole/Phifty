<?php
namespace Phifty\Testing;
use Exception;

class AdminTestCase extends Selenium2TestCase 
{
    protected $urlOf = array(
        'login' => '/bs/login',
        'news' => '/bs/news',
        'newsCategory' => '/bs/news_category',
        'contacts' => '/bs/contacts',
        'contactGroups' => '/bs/contact_groups',
        'product' => '/bs/product'
    );

    public function gotoLoginPage()
    {
        $this->url( $this->getBaseUrl() . $this->urlOf['login'] );
    }

    protected function login( $transferTo = null ) 
    {
        $this->gotoLoginPage();

        $accountInput = find_element('input[name=account]');
        $accountInput->value('admin');

        $passwordInput = find_element('input[name=password]');
        $passwordInput->value('admin');

        find_element('.submit')->click();

        // ok( ! find_element('.message.error') , 'login error' );

        if ( $transferTo ) {
            if( isset($this->urlOf[ $transferTo ]) )
                $this->url( $this->getBaseUrl() . $this->urlOf[ $transferTo ] );
            else {
                throw new Exception("Url of $transferTo is not defined.");
            }
        }
        wait();
    }

    protected function logout()
    {
        find_element('#operation .buttons a[href]')->click();
        wait();
    }

    protected function isCreated() 
    {
        $msg = find_element('.message.success')->text();
        $this->assertContains('created', $msg );
    }

    protected function isUpdated() 
    {
        $msg = find_element('.message.success')->text();
        $this->assertRegExp('/updated|已經更新/', $msg );
    }

    protected function isDeleted() 
    {
        $msg = waitFor('.jGrowl-message')->text();
        $this->assertRegExp('/(deleted|刪除成功)/', $msg );
    }

    protected function isUploaded() 
    {
        $msg = waitFor('.jGrowl-message')->text();
        $this->assertContains('created', $msg );
    }

    public function uploadFile( $sel, $filepath ) {
        find_element($sel)->value( realpath( $filepath ));
    }
}
