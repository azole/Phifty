<?php
namespace Phifty\Service;
use Kendo\Acl\RuleLoader;
use Kendo\Acl\Acl;

class KendoService
    implements ServiceRegister
{

    /**
     * The RuleLoader
     */
    public $loader;

    public function getId() { return 'KendoAccessControl'; }

    public function register($kernel,$options = array())
    {
        $self = $this;
        $kernel->acl = function() use ($self,$kernel,$options) {
            $loader = new RuleLoader;
            foreach ($options['Rules'] as $ruleClass) {
                $loader->load($ruleClass);
            }
            return Acl::getInstance($loader);
        };
    }
}
