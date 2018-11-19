<?php
return array(
    'plugins' => array(
        'MelisDemoCommerce' => array(
            'forms' => array(
                'MelisDemoCommerce_checkout_fake_payment_form' => array(
                    'attributes' => array(
                        'method' => '',
                        'action' => '',
                    ),
                    'hydrator'  => 'Zend\Stdlib\Hydrator\ArraySerializable',
                    'elements' => array(
                        array(
                            'spec' => array(
                                'name' => 'order-id',
                                'type' => 'hidden',
                            )
                        ),
                        array(
                            'spec' => array(
                                'name' => 'payment-transaction-total-cost',
                                'type' => 'hidden',
                            )
                        ),
                        array(
                            'spec' => array(
                                'name' => 'payment-transaction-currency-code',
                                'type' => 'hidden',
                            )
                        ),
                        array(
                            'spec' => array(
                                'name' => 'card-holder-name',
                                'type' => 'Text',
                                'options' => array(
                                    'label' => 'Card Holder\'s Name',
                                ),
                                'attributes' => array(
                                    'id' => 'card-holder-name',
                                )
                            )
                        ),
                        array(
                            'spec' => array(
                                'name' => 'card-number',
                                'type' => 'Text',
                                'options' => array(
                                    'label' => 'Debit/Credit Card Number',
                                ),
                                'attributes' => array(
                                    'id' => 'card-number',
                                )
                            )
                        ),
                        array(
                            'spec' => array(
                                'name' => 'expiry-month',
                                'type' => 'Select',
                                'options' => array(
                                    'label' => 'Month expiration',
                                    'value_options' => array(
                                        '01' => 'Jan (01)',
                                        '02' => 'Feb (02)',
                                        '03' => 'Mar (03)',
                                        '04' => 'Apr (04)',
                                        '05' => 'May (05)',
                                        '06' => 'June (06)',
                                        '07' => 'July (07)',
                                        '08' => 'Aug (08)',
                                        '09' => 'Sep (09)',
                                        '10' => 'Oct (10)',
                                        '11' => 'Nov (11)',
                                        '12' => 'Dec (12)',
                                    ),
                                ),
                                'attributes' => array(
                                    'id' => 'expiry-month',
                                )
                            )
                        ),
                        array(
                            'spec' => array(
                                'name' => 'expiry-year',
                                'type' => 'Select',
                                'options' => array(
                                    'label' => 'Year expiration',
                                    'value_options' => array(
                                        '2017' => '2017',
                                        '2018' => '2018',
                                        '2019' => '2019',
                                        '2020' => '2020',
                                        '2021' => '2021',
                                        '2022' => '2022',
                                        '2023' => '2023',
                                    ),
                                ),
                                'attributes' => array(
                                    'id' => 'expiry-year"',
                                )
                            )
                        ),
                        array(
                            'spec' => array(
                                'name' => 'cvv',
                                'type' => 'Text',
                                'options' => array(
                                    'label' => 'Card CVV',
                                ),
                                'attributes' => array(
                                    'id' => 'cvv',
                                )
                            )
                        ),
                    ),
                    'input_filter' => array(
                        'card-holder-name' => array(
                            'name'     => 'card-holder-name',
                            'required' => true,
                            'validators' => array(
                            ),
                            'filters'  => array(
                                array('name' => 'StripTags'),
                                array('name' => 'StringTrim'),
                            ),
                        ),
                        'card-number' => array(
                            'name'     => 'card-number',
                            'required' => true,
                            'validators' => array(
                                array(
                                    'name' => 'InArray',
                                    'options' => array(
                                        'haystack' => array('1111222233334444'),
                                        'messages' => array(
                                            \Zend\Validator\InArray::NOT_IN_ARRAY => 'Invalid Card number, Please try again'
                                        ),
                                    ),
                                ),
                            ),
                            'filters'  => array(
                                array('name' => 'StripTags'),
                                array('name' => 'StringTrim'),
                            ),
                        ),
                        'cvv' => array(
                            'name'     => 'cvv',
                            'required' => true,
                            'validators' => array(
                                array(
                                    'name' => 'InArray',
                                    'options' => array(
                                        'haystack' => array('123'),
                                        'messages' => array(
                                            \Zend\Validator\InArray::NOT_IN_ARRAY => 'Invalid Card CVV, Please try again'
                                        ),
                                    ),
                                ),
                            ),
                            'filters'  => array(
                                array('name' => 'StripTags'),
                                array('name' => 'StringTrim'),
                            ),
                        ),
                    ),
                ),
                'MelisDemoCommerce_checkout_fake_paypal_form' => array(
                    'attributes' => array(
                        'method' => '',
                        'action' => '',
                    ),
                    'hydrator'  => 'Zend\Stdlib\Hydrator\ArraySerializable',
                    'elements' => array(
                        array(
                            'spec' => array(
                                'name' => 'order-id',
                                'type' => 'hidden',
                            )
                        ),
                        array(
                            'spec' => array(
                                'name' => 'payment-transaction-country-id',
                                'type' => 'hidden',
                            )
                        ),
                        array(
                            'spec' => array(
                                'name' => 'payment-transaction-coupon-id',
                                'type' => 'hidden',
                            )
                        ),
                        array(
                            'spec' => array(
                                'name' => 'payment-transaction-total-cost',
                                'type' => 'hidden',
                            )
                        ),
                        array(
                            'spec' => array(
                                'name' => 'payment-transaction-currency-code',
                                'type' => 'hidden',
                            )
                        ),
                        array(
                            'spec' => array(
                                'name' => 'checkout-confirm-url',
                                'type' => 'hidden',
                            )
                        ),
                        array(
                            'spec' => array(
                                'name' => 'card-holder-name',
                                'type' => 'Text',
                                'options' => array(
                                    'label' => 'Card Holder\'s Name',
                                ),
                                'attributes' => array(
                                    'id' => 'card-holder-name',
                                )
                            )
                        ),
                        array(
                            'spec' => array(
                                'name' => 'card-number',
                                'type' => 'Text',
                                'options' => array(
                                    'label' => 'Debit/Credit Card Number',
                                ),
                                'attributes' => array(
                                    'id' => 'card-number',
                                )
                            )
                        ),
                        array(
                            'spec' => array(
                                'name' => 'expiry-month',
                                'type' => 'Select',
                                'options' => array(
                                    'label' => 'Month expiration',
                                    'value_options' => array(
                                        '01' => 'Jan (01)',
                                        '02' => 'Feb (02)',
                                        '03' => 'Mar (03)',
                                        '04' => 'Apr (04)',
                                        '05' => 'May (05)',
                                        '06' => 'June (06)',
                                        '07' => 'July (07)',
                                        '08' => 'Aug (08)',
                                        '09' => 'Sep (09)',
                                        '10' => 'Oct (10)',
                                        '11' => 'Nov (11)',
                                        '12' => 'Dec (12)',
                                    ),
                                ),
                                'attributes' => array(
                                    'id' => 'expiry-month',
                                )
                            )
                        ),
                        array(
                            'spec' => array(
                                'name' => 'expiry-year',
                                'type' => 'Select',
                                'options' => array(
                                    'label' => 'Year expiration',
                                    'value_options' => array(
                                        '2017' => '2017',
                                        '2018' => '2018',
                                        '2019' => '2019',
                                        '2020' => '2020',
                                        '2021' => '2021',
                                        '2022' => '2022',
                                        '2023' => '2023',
                                    ),
                                ),
                                'attributes' => array(
                                    'id' => 'expiry-year"',
                                )
                            )
                        ),
                        array(
                            'spec' => array(
                                'name' => 'cvv',
                                'type' => 'Text',
                                'options' => array(
                                    'label' => 'Card CVV',
                                ),
                                'attributes' => array(
                                    'id' => 'cvv',
                                )
                            )
                        ),
                    ),
                    'input_filter' => array(
                        'card-holder-name' => array(
                            'name'     => 'card-holder-name',
                            'required' => true,
                            'validators' => array(
                            ),
                            'filters'  => array(
                                array('name' => 'StripTags'),
                                array('name' => 'StringTrim'),
                            ),
                        ),
                        'card-number' => array(
                            'name'     => 'card-number',
                            'required' => true,
                            'validators' => array(
                                array(
                                    'name' => 'InArray',
                                    'options' => array(
                                        'haystack' => array('1111222233334444'),
                                        'messages' => array(
                                            \Zend\Validator\InArray::NOT_IN_ARRAY => 'Invalid Card number, Please try again'
                                        ),
                                    ),
                                ),
                            ),
                            'filters'  => array(
                                array('name' => 'StripTags'),
                                array('name' => 'StringTrim'),
                            ),
                        ),
                        'cvv' => array(
                            'name'     => 'cvv',
                            'required' => true,
                            'validators' => array(
                                array(
                                    'name' => 'InArray',
                                    'options' => array(
                                        'haystack' => array('123'),
                                        'messages' => array(
                                            \Zend\Validator\InArray::NOT_IN_ARRAY => 'Invalid Card CVV, Please try again'
                                        ),
                                    ),
                                ),
                            ),
                            'filters'  => array(
                                array('name' => 'StripTags'),
                                array('name' => 'StringTrim'),
                            ),
                        ),
                    ),
                ),
            )
        )
    )
);