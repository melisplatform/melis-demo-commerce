<?php

return array(
    'site' => array(
        'MelisDemoCommerce' => array(
            'conf' => array(
                'id' => 'id_MelisDemoCommerce',
                'home_page' => '[:homePageId]'
            ),
            'datas' => array(
                // Site default Country ID
                'site_country_id' => 1,
                // Site default language ID
                'site_lang_id' => 1,
                // Site Id
                'site_id' => '[:siteId]',
                // Site Search Page Id
                'search_result_page_id' => '[:searchResultsPageId]',
                // Login and Registration Page Id
                'login_regestration_page_id' => '[:loginRegistrationPageId]',
                // Logout redirect Page id
                'logout_redirect_page_id' => '[:homePageId]',
                // Lost Password Page Id
                'lost_password_page_id' => '[:lostPasswordPageId]',
                // Change Lost Password Page Id
                'lost_password_reset_page_id' => '[:lostPasswordPageId]',
                // Account Page Id
                'account_page_id' => '[:myAccountPageId]',
                // Cart Page Id
                'cart_page_id' => '[:cartPageId]',
                // Checkout Page Id
                'checkout_page_id' => '[:checkoutPageId]',
                // Submenu limit
                'sub_menu_limit' => null,
                // News Page Id
                'news_menu_page_id' => '[:newsPageId]',
                // News Details Page Id
                'news_details_page_id' => '[:newsDetailsPageId]',
                // Testimonial parent id
                'testimonial_id' => '[:testimonialPageId]',
                // Product page id
                'product_page_id' => '[:productPageId]',
                // Homepage header slider
                'homepage_header_slider' => '[:homepageHeaderSlider]',
                // Aboutus slider
                'aboutus_slider' => '[:aboutusSlider]',
                // New arrival sslider
                'newArrivalCategory' => array('[:menCategoryId]'),
                // Discount, featured, onsale categories
                'discountFeatureOnsale' => array( '[:bestSellerCategoryId]', '[:newArrivalsCategoryId]', '[:specialOfferCategoryId]' ),
                // Parent category ID
                'parent_category_id' => '[:collection2017CategoryId]',
                // Color attribute filter
                'color_attribute_id' => '[:colorAttributeId]',
                // Size attribute filter
                'size_attribute_id' => '[:sizeAttributeId]',
                // catalogue pages filter
                'catalogue_pages' => array(
                    'men_catalogue_filter' => array(
                        'page_id' => '[:menPageId]',
                        'category_id' => '[:menCategoryId]',
                    ),
                    'women_catalogue_filter' => array(
                        'page_id' => '[:womenPageId]',
                        'category_id' => '[:womenCategoryId]',
                    ),
                    'shoes_catalogue_filter' => array(
                        'page_id' => '[:shoesPageId]',
                        'category_id' => '[:shoesCategoryId]',
                    ),
                    'accessories_catalogue_filter' => array(
                        'page_id' => '[:accessoriesPageId]',
                        'category_id' => '[:accessoriesCategoryId]',
                    ),
                ),
                // Category menu id that has sub menu of products
                'category_product_menu' => array('[:joggingCategoryId]', '[:classy_CategoryId]', '[:bootsCategoryId]', '[:highHeelsCategoryId]', '[:accessoriesCategoryId]', '[:hatsCategoryId]', '[:watchesCategoryId]', '[:bagsCategoryId]'),
                // Order Details Page Id
                'order_details_page_id' => '[:orderDetailsPageId]',
                // pagination config
                'pagination_config' => array(
                    'limit' => array(9, 12, 15, 18),
                ),
                'sort_config' => array(
                    array(
                        'm_col_name' => 'ptxt_field_short',
                        'm_order' => 'ASC',
                        'text' => 'Name (A - Z)',
                    ),
                    array(
                        'm_col_name' => 'ptxt_field_short',
                        'm_order' => 'DESC',
                        'text' => 'Name (Z - A)',
                    ),
                    array(
                        'm_col_name' => 'price',
                        'm_order' => 'ASC',
                        'text' => 'Price (Low &gt; High)',
                    ),
                    array(
                        'm_col_name' => 'price',
                        'm_order' => 'DESC',
                        'text' => 'Price (High &gt; Low)',
                    ),
                ),
                'sort_default' => array(
                    'm_col_name' => 'price',
                    'm_order' => 'ASC',
                ),
                'default_image' => '/MelisDemoCommerce/images/default/melis-default.jpg',
                'image_preview' => 'DEFAULT',
                'image_src' => 'product',
                /**
                 * Required Modules for installation,
                 * to trigger services that needed to install the MelisDemoCommerce
                 * and to avoid deselect from selecting modules during installations.
                 */
                'required_modules' => array(
                    'MelisCmsNews',
                    'MelisCmsSlider',
                    'MelisCmsProspects',
                    'MelisCommerce',
                ),
                'lostpassword_email' => array(
                    'email_template_path' => 'MelisDemoCommerce/emailLayout',
                    'email_to' => '',
                    'email_from' => 'noreply@melistechnology.com',
                    'email_from_name' => 'Melis Commerce Demo',
                    'email_subject' => 'Lost Password',
                    'email_content' => 'Please click the link below to reset your password.<br>'
                    .'<a href="[lostPasswordLink]?m_recovery_key=[recoveryKey]">'
                    .'Reset password link</a><br>',
                    'email_content_tag_replace' => array(),
                ),
            ),
        )
    ),
    'plugins' => array(
        // Redefining defaut values for SEO pages so that it won't be needed to define it for
        // every category / product / variant
        'meliscommerce' => array(
            'datas' => array(
                'seo_default_pages' => array(
                    'category' => '[:menCategoryId]',
                    'product' => '[:productPageId]',
                    // variant is not used, there's no spcific page to display just a variant
                    // 'variant' => 17,
                ),
            ),
            'emails' => array(
                'VARIANTSLOWSTOCK' => array(
                    'code' => 'VARIANTSLOWSTOCK',
                    'email_name' => 'Low on stock',
                    'layout' => '',
                    'headers' => array(
                        'from' => 'noreply@melistechnology.com',
                        'from_name' => 'Melis Technology',
                        'to' => 'mbernard@melistechnology.com',
                        'name_to' => 'Mickaël Bernard',
                        'replyTo' => '',
                        'tags' => 'PRODUCT_TEXT, PRODUCT_ID, VARIANT_SKU, VARIANT_ID, STOCKS',
                    ),
                ),
            )
        ),
        'melisfront' => array(
            'plugins' => array(
                'MelisFrontMenuPlugin' => array(
                    'front' => array(
                        'template_path' => array('MelisDemoCommerce/plugin/menu'),
                    ),
                ),
                'MelisFrontBreadcrumbPlugin' => array(
                    'front' => array(
                        'template_path' => array('MelisDemoCommerce/plugin/breadcrumb'),
                    ),
                ),
                'MelisFrontShowListFromFolderPlugin' => array(
                    'front' => array(
                        'template_path' => array('MelisDemoCommerce/plugin/testimonial-slider'),
                        'files' => array(
                            'js' => array(
                                '/MelisDemoCommerce/js/MelisPlugins/MelisDemoCommerce.MelisFrontShowListFromFolderPlugin.init.js'
                            ),
                        ),
                    ),
                ),
                'MelisFrontSearchResultsPlugin' => array(
                    'front' => array(
                        'template_path' => array('MelisDemoCommerce/plugin/search-results'),
                    ),
                ),
            ),
        ),
        'meliscmsnews' => array(
            'plugins' => array(
                'MelisCmsNewsListNewsPlugin' => array(
                    'front' => array(
                        'template_path' => array('MelisDemoCommerce/plugin/news-list'),
                    ),
                ),
                'MelisCmsNewsShowNewsPlugin' => array(
                    'front' => array(
                        'template_path' => array('MelisDemoCommerce/plugin/news-details'),
                    ),
                ),
                'MelisCmsNewsLatestNewsPlugin' => array(
                    'front' => array(
                        'template_path' => array('MelisDemoCommerce/plugin/latest-news'),
                        'files' => array(
                            'js' => array(
                                '/MelisDemoCommerce/js/MelisPlugins/MelisDemoCommerce.MelisCmsNewsLatestNewsPlugin.init.js'
                            ),
                        ),
                    ),
                ),
            ),
        ),
        'meliscmsslider' => array(
            'plugins' => array(
                'MelisCmsSliderShowSliderPlugin' => array(
                    'front' => array(
                        'template_path' => array(
                            'MelisDemoCommerce/plugin/homepage-slider',
                            'MelisDemoCommerce/plugin/aboutus-slider',
                        ),
                        'files' => array(
                            'js' => array(
                                '/MelisDemoCommerce/js/MelisPlugins/MelisDemoCommerce.MelisCmsSliderShowSliderPlugin.init.js'
                            ),
                        ),
                    ),
                ),
            ),
        ),
        'meliscmsprospects' => array(
            'plugins' => array(
                'MelisCmsProspectsShowFormPlugin' => array(
                    'front' => array(
                        'template_path' => array('MelisDemoCommerce/plugin/contactus'),
                    ),
                ),
            ),
        ),
    ),
);