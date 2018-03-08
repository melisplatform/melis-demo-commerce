<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'router' => array(
        'routes' => array(
        	'MelisDemoCommerce-pageids' => array(
				'type'    => 'regex',
				'options' => array(
					'regex'    => '.*/MelisDemoCommerce/.*/id/(?<idpage>[0-9]+)',
					'defaults' => array(
						'controller' => 'MelisDemoCommerce\Controller\Index',
						'action'     => 'indexsite',
					),
					'spec' => '%idpage'
				),
			),
        	'MelisDemoCommerce-homepage' => array(
				'type'    => 'Literal',
				'options' => array(
					'route'    => '/',
					'defaults' => array(
						'controller'     => 'MelisFront\Controller\Index',
						'action'         => 'index',
					    'renderType'     => 'melis_zf2_mvc',
					    'renderMode'     => 'front',
					    'preview'        => false,
					    'idpage'         => '[:homePageId]'
					)
				),
			),
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            'applicationMelisDemoCommerce' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/MelisDemoCommerce',
                    'defaults' => array(
                        '__NAMESPACE__' => 'MelisDemoCommerce\Controller',
                        'controller'    => 'Index',
                        'action'        => 'indexsite',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                    'setup' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/setup',
                            'defaults' => array(
                                'controller' => 'MelisDemoCommerce\Controller\Setup',
                                'action' => 'setup',
                            ),
                        ),
                    ),
                ),
            ), 
        ),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
            'MelisPlatformTable' => 'MelisDemoCommerce\Model\Tables\MelisPlatformTable',
        ),
        'factories' => array(
            // MelisDemoCommerce Services
            'DemoCommerceService'       => 'MelisDemoCommerce\Service\Factory\DemoCommerceServiceFactory',
            'SiteShipmentCostService'  => 'MelisDemoCommerce\Service\Factory\SiteShipmentCostServiceFactory',
            
            'MelisDemoCommerce\Model\Tables\MelisPlatformTable' => 'MelisDemoCommerce\Model\Tables\Factory\MelisPlatformTableFactory',
        )
    ),
    'translator' => array(
        // 'locale' => 'en_US',
    ),
    'controllers' => array(
        'invokables' => array(
            'MelisDemoCommerce\Controller\Test'             => 'MelisDemoCommerce\Controller\TestController',
            'MelisDemoCommerce\Controller\Base'             => 'MelisDemoCommerce\Controller\BaseController',
            'MelisDemoCommerce\Controller\Home'             => 'MelisDemoCommerce\Controller\HomeController',
            'MelisDemoCommerce\Controller\ComCatalogue'     => 'MelisDemoCommerce\Controller\ComCatalogueController',
            'MelisDemoCommerce\Controller\News'             => 'MelisDemoCommerce\Controller\NewsController',
            'MelisDemoCommerce\Controller\About'            => 'MelisDemoCommerce\Controller\AboutController',
            'MelisDemoCommerce\Controller\Contact'          => 'MelisDemoCommerce\Controller\ContactController',
            'MelisDemoCommerce\Controller\Testimonial'      => 'MelisDemoCommerce\Controller\TestimonialController',
            'MelisDemoCommerce\Controller\ComProduct'       => 'MelisDemoCommerce\Controller\ComProductController',
            'MelisDemoCommerce\Controller\ComCheckout'      => 'MelisDemoCommerce\Controller\ComCheckoutController',
            'MelisDemoCommerce\Controller\ComLogin'         => 'MelisDemoCommerce\Controller\ComLoginController',
            'MelisDemoCommerce\Controller\ComLostPassword'  => 'MelisDemoCommerce\Controller\ComLostPasswordController',
            'MelisDemoCommerce\Controller\Search'           => 'MelisDemoCommerce\Controller\SearchController',
            'MelisDemoCommerce\Controller\ComMyAccount'     => 'MelisDemoCommerce\Controller\ComMyAccountController',
            'MelisDemoCommerce\Controller\ComLogout'        => 'MelisDemoCommerce\Controller\ComLogoutController',
            'MelisDemoCommerce\Controller\ComOrder'         => 'MelisDemoCommerce\Controller\ComOrderController',
            'MelisDemoCommerce\Controller\FakeServerPayment'=> 'MelisDemoCommerce\Controller\FakeServerPaymentController',
            'MelisDemoCommerce\Controller\Setup'            => 'MelisDemoCommerce\Controller\SetupController',
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'DemoSiteFieldCollection'  => 'MelisDemoCommerce\Form\View\Helper\DemoSiteFieldCollection',
            'DemoSiteFieldRow'         => 'MelisDemoCommerce\Form\View\Helper\DemoSiteFieldRow',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'controller_map' => array(
            'MelisDemoCommerce' => true,
        ),
        'template_map' => array(
            // Zend default layout
            'layout/layout'                     => __DIR__ . '/../view/layout/defaultLayout.phtml',
            // Main layout
            'MelisDemoCommerce/defaultLayout'   => __DIR__ . '/../view/layout/defaultLayout.phtml',
            'MelisDemoCommerce/setupLayout'     => __DIR__ . '/../view/layout/setupLayout.phtml',
            'layout/errorLayout'                => __DIR__ . '/../view/layout/errorLayout.phtml',
            // Errors layout
            'error/404'               		    => __DIR__ . '/../view/error/404.phtml',
            'error/index'             		    => __DIR__ . '/../view/error/index.phtml',
            // Email layout
            'MelisDemoCommerce/emailLayout'     => __DIR__ . '/../view/layout/reset-password-email-layout.phtml',

            // Plugins layout
            'MelisDemoCommerce/plugin/menu'                             => __DIR__ . '/../view/plugins/menu.phtml',
            'MelisDemoCommerce/plugin/contactus'                        => __DIR__ . '/../view/plugins/contactus.phtml',
            'MelisDemoCommerce/plugin/homepage-slider'                  => __DIR__ . '/../view/plugins/homepage-slider.phtml',
            'MelisDemoCommerce/plugin/testimonial-slider'               => __DIR__ . '/../view/plugins/testimonial-slider.phtml',
            'MelisDemoCommerce/plugin/latest-news'                      => __DIR__ . '/../view/plugins/latest-news.phtml',
            'MelisDemoCommerce/plugin/aboutus-slider'                   => __DIR__ . '/../view/plugins/aboutus-slider.phtml',
            'MelisDemoCommerce/plugin/news-list'                        => __DIR__ . '/../view/plugins/news-list.phtml',
            'MelisDemoCommerce/plugin/list-paginator'                   => __DIR__ . '/../view/plugins/list-paginator.phtml',
            'MelisDemoCommerce/plugin/news-details'                     => __DIR__ . '/../view/plugins/news-details.phtml',
            'MelisDemoCommerce/plugin/search-results'                   => __DIR__ . '/../view/plugins/search-results.phtml',
            'MelisDemoCommerce/plugin/registration'                     => __DIR__ . '/../view/plugins/registration.phtml',
            'MelisDemoCommerce/plugin/login'                            => __DIR__ . '/../view/plugins/login.phtml',
            
            'MelisDemoCommerce/plugin/category-product-list-slider'     => __DIR__ . '/../view/plugins/category-product-list-slider.phtml',
            
            'MelisDemoCommerce/plugin/category-tree'                    => __DIR__ . '/../view/plugins/category-tree.phtml',
            'MelisDemoCommerce/plugin/product-price-range'              => __DIR__ . '/../view/plugins/product-price-range.phtml',
            'MelisDemoCommerce/plugin/product-color-attribute'          => __DIR__ . '/../view/plugins/product-color-attribute.phtml',
            'MelisDemoCommerce/plugin/product-size-attribute'           => __DIR__ . '/../view/plugins/product-size-attribute.phtml',
            'MelisDemoCommerce/plugin/product-list'                     => __DIR__ . '/../view/plugins/product-list.phtml',
            'MelisDemoCommerce/plugin/product-list-paginator'           => __DIR__ . '/../view/plugins/product-list-paginator.phtml',
            'MelisDemoCommerce/plugin/show-product'                     => __DIR__ . '/../view/plugins/show-product.phtml',
            'MelisDemoCommerce/plugin/related-products'                 => __DIR__ . '/../view/plugins/related-products.phtml',
            'MelisDemoCommerce/plugin/account'                          => __DIR__ . '/../view/plugins/account.phtml',
            'MelisDemoCommerce/plugin/profile'                          => __DIR__ . '/../view/plugins/profile.phtml',
            'MelisDemoCommerce/plugin/delivery-address'                 => __DIR__ . '/../view/plugins/delivery-address.phtml',
            'MelisDemoCommerce/plugin/billing-address'                  => __DIR__ . '/../view/plugins/billing-address.phtml',
            'MelisDemoCommerce/plugin/my-cart'                          => __DIR__ . '/../view/plugins/my-cart.phtml',
            'MelisDemoCommerce/plugin/lost-password'                    => __DIR__ . '/../view/plugins/lost-password.phtml',
            'MelisDemoCommerce/plugin/reset-password'                   => __DIR__ . '/../view/plugins/reset-password.phtml',
            'MelisDemoCommerce/plugin/show-attributes'                  => __DIR__ . '/../view/plugins/show-attributes.phtml',
            'MelisDemoCommerce/plugin/show-add-to-cart'                 => __DIR__ . '/../view/plugins/show-add-to-cart.phtml',
            
            'MelisDemoCommerce/plugin/check-out'                        => __DIR__ . '/../view/plugins/check-out.phtml',
            'MelisDemoCommerce/plugin/check-out-cart'                   => __DIR__ . '/../view/plugins/check-out-cart.phtml',
            'MelisDemoCommerce/plugin/check-out-cart-coupon'            => __DIR__ . '/../view/plugins/check-out-cart-coupon.phtml',
            'MelisDemoCommerce/plugin/check-out-addresses'              => __DIR__ . '/../view/plugins/check-out-addresses.phtml',
            'MelisDemoCommerce/plugin/check-out-summary'                => __DIR__ . '/../view/plugins/check-out-summary.phtml',
            'MelisDemoCommerce/plugin/check-out-confirm-summary'        => __DIR__ . '/../view/plugins/check-out-confirm-summary.phtml',
            'MelisDemoCommerce/plugin/check-out-confirmation'           => __DIR__ . '/../view/plugins/check-out-confirmation.phtml',
            
            'MelisDemoCommerce/plugin/check-out-fake-payment'           => __DIR__ . '/../view/plugins/check-out-fake-payment.phtml',
            'MelisDemoCommerce/plugin/check-out-fake-paypal-style'      => __DIR__ . '/../view/plugins/check-out-fake-paypal-style.phtml',
            
            'MelisDemoCommerce/plugin/show-cart-menu'                   => __DIR__ . '/../view/plugins/show-cart.phtml',
            'MelisDemoCommerce/plugin/order-history'                    => __DIR__ . '/../view/plugins/order-history.phtml',
            'MelisDemoCommerce/show-order-details'                      => __DIR__ . '/../view/plugins/show-order-details.phtml',
            'MelisDemoCommerce/show-order-addresses'                    => __DIR__ . '/../view/plugins/show-order-addresses.phtml',
            'MelisDemocommerce/show-shipping-details'                   => __DIR__ . '/../view/plugins/show-shipping-details.phtml',
            'MelisDemocommerce/show-order-messages'                     => __DIR__ . '/../view/plugins/show-order-messages.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'asset_manager' => array(
        'resolver_configs' => array(
            'aliases' => array(
                'MelisDemoCommerce/' => __DIR__ . '/../public/',
            ),
        ),
    ),
);
