<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
namespace Module\Order\Api;

use Pi;
use Pi\Application\Api\AbstractBreadcrumbs;

class Breadcrumbs extends AbstractBreadcrumbs
{
    /**
     * {@inheritDoc}
     */
    public function load()
    {
        // Get params
        $params = Pi::service('url')->getRouteMatch()->getParams();
        // Set module link
        $moduleData = Pi::registry('module')->read($this->getModule());
        // Set index
        if ($params['controller'] == 'index' && $params['action'] == 'index') {
            $result = array(
                array(
                    'label' => $moduleData['title'],
                ),
            );
        } else {
            $result = array(
                array(
                    'label' => $moduleData['title'],
                    'href' => Pi::url(Pi::service('url')->assemble('order', array(
                        'module' => $this->getModule(),
                    ))),
                ),
            );

            switch ($params['controller']) {
                case 'index':
                    switch ($params['action']) {
                        case 'remove':
                            $result[] = array(
                                'label' => __('Remove cache'),
                            );
                            break;

                        case 'error':
                            $result[] = array(
                                'label' => __('Error'),
                            );
                            break;
                    }
                    break;

                case 'detail':
                    switch ($params['action']) {
                        case 'index':
                            $result[] = array(
                                'label' => __('Order detail'),
                            );
                            break;
                    }

                    break;

                case 'checkout':
                    switch ($params['action']) {
                        case 'index':
                            $result[] = array(
                                'label' => __('Checkout'),
                            );
                            break;
                    }

                    switch ($params['action']) {
                        case 'installment':
                            $result[] = array(
                                'label' => __('Installment plans'),
                            );
                            break;
                    }
                    break;

                case 'credit':
                    $result[] = array(
                        'label' => __('Credit'),
                    );
                    break;
            }
        }
        return $result;
    }
}