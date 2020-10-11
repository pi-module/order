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
            $result = [
                [
                    'label' => $moduleData['title'],
                ],
            ];
        } else {
            $result = [
                [
                    'label' => $moduleData['title'],
                    'href'  => Pi::url(
                        Pi::service('url')->assemble(
                            'order', [
                                'module' => $this->getModule(),
                            ]
                        )
                    ),
                ],
            ];

            switch ($params['controller']) {
                case 'index':
                    switch ($params['action']) {
                        case 'remove':
                            $result[] = [
                                'label' => __('Remove cache'),
                            ];
                            break;

                        case 'error':
                            $result[] = [
                                'label' => __('Error'),
                            ];
                            break;
                    }
                    break;

                case 'detail':
                    switch ($params['action']) {
                        case 'index':
                            $result[] = [
                                'label' => __('Order detail'),
                            ];
                            break;
                    }

                    break;

                case 'checkout':
                    switch ($params['action']) {
                        case 'index':
                            $result[] = [
                                'label' => __('Checkout'),
                            ];
                            break;
                    }

                    switch ($params['action']) {
                        case 'installment':
                            $result[] = [
                                'label' => __('Installment plans'),
                            ];
                            break;
                    }
                    break;

                case 'credit':
                    $result[] = [
                        'label' => __('Credit'),
                    ];
                    break;
            }
        }
        return $result;
    }
}
