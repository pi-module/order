<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt New BSD License
 */

/**
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */

namespace Module\Order\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;

class SubscriptionController extends ActionController
{
    public function indexAction()
    {


        // Set view
        $this->view()->setTemplate('subscription-index');
    }

    public function detailAction()
    {


        // Set view
        $this->view()->setTemplate('subscription-detail');
    }

    public function customerAction()
    {


        // Set view
        $this->view()->setTemplate('subscription-customer');
    }

    public function productAction()
    {

        // Set view
        $this->view()->setTemplate('subscription-product');
    }
}