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
        'product' => '/bs/product',
        'pages' => '/bs/pages'
    );

    protected function gotoLoginPage()
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
        wait_for('.admin-menu');
    }

    protected function logout()
    {
        find_element('#operation .buttons a[href]')->click();
        wait();
    }

    protected function isCreated() 
    {
        message_like('/created|已經建立|成功/');
    }

    protected function isUpdated() 
    {
        $msg = find_element('.message.success')->text();
        jgrowl_like('/updated|已經更新|成功|更新成功/');
    }

    protected function isDeleted() 
    {
        jgrowl_like('/(deleted|刪除成功|成功)/');
    }

    public function isUploaded() 
    {
        jgrowl_like('/(created|已經建立|成功)/');
    }

    public function uploadFile( $sel, $filepath ) 
    {
        find_element($sel)->value( realpath( $filepath ));
    }
}
