<?php
return [
    'plugins' => [
        'MelisDemoCommerce' => [
            'forms' => [
                'MelisDemoCommerce_checkout_fake_payment_form' => [
                    'attributes' => [
                        'method' => '',
                        'action' => '',
                    ],
                    'hydrator'  => 'Laminas\Hydrator\ArraySerializable',
                    'elements' => [
                        [
                            'spec' => [
                                'name' => 'order-id',
                                'type' => 'hidden',
                            ]
                        ],
                        [
                            'spec' => [
                                'name' => 'payment-transaction-total-cost',
                                'type' => 'hidden',
                            ]
                        ],
                        [
                            'spec' => [
                                'name' => 'payment-transaction-currency-code',
                                'type' => 'hidden',
                            ]
                        ],
                        [
                            'spec' => [
                                'name' => 'card-holder-name',
                                'type' => 'Text',
                                'options' => [
                                    'label' => 'Card Holder\'s Name',
                                ],
                                'attributes' => [
                                    'id' => 'card-holder-name',
                                ]
                            ]
                        ],
                        [
                            'spec' => [
                                'name' => 'card-number',
                                'type' => 'Text',
                                'options' => [
                                    'label' => 'Debit/Credit Card Number',
                                ],
                                'attributes' => [
                                    'id' => 'card-number',
                                ]
                            ]
                        ],
                        [
                            'spec' => [
                                'name' => 'expiry-month',
                                'type' => 'Select',
                                'options' => [
                                    'label' => 'Month expiration',
                                    'value_options' => [
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
                                    ],
                                ],
                                'attributes' => [
                                    'id' => 'expiry-month',
                                ]
                            ]
                        ],
                        [
                            'spec' => [
                                'name' => 'expiry-year',
                                'type' => 'Select',
                                'options' => [
                                    'label' => 'Year expiration',
                                    'value_options' => [
                                        '2017' => '2017',
                                        '2018' => '2018',
                                        '2019' => '2019',
                                        '2020' => '2020',
                                        '2021' => '2021',
                                        '2022' => '2022',
                                        '2023' => '2023',
                                    ],
                                ],
                                'attributes' => [
                                    'id' => 'expiry-year"',
                                ]
                            ]
                        ],
                        [
                            'spec' => [
                                'name' => 'cvv',
                                'type' => 'Text',
                                'options' => [
                                    'label' => 'Card CVV',
                                ],
                                'attributes' => [
                                    'id' => 'cvv',
                                ]
                            ]
                        ],
                    ],
                    'input_filter' => [
                        'card-holder-name' => [
                            'name'     => 'card-holder-name',
                            'required' => true,
                            'validators' => [
                            ],
                            'filters'  => [
                                ['name' => 'StripTags'],
                                ['name' => 'StringTrim'],
                            ],
                        ],
                        'card-number' => [
                            'name'     => 'card-number',
                            'required' => true,
                            'validators' => [
                                [
                                    'name' => 'InArray',
                                    'options' => [
                                        'haystack' => ['1111222233334444'],
                                        'messages' => [
                                            \Laminas\Validator\InArray::NOT_IN_ARRAY => 'Invalid Card number, Please try again'
                                        ],
                                    ],
                                ],
                            ],
                            'filters'  => [
                                ['name' => 'StripTags'],
                                ['name' => 'StringTrim'],
                            ],
                        ],
                        'cvv' => [
                            'name'     => 'cvv',
                            'required' => true,
                            'validators' => [
                                [
                                    'name' => 'InArray',
                                    'options' => [
                                        'haystack' => ['123'],
                                        'messages' => [
                                            \Laminas\Validator\InArray::NOT_IN_ARRAY => 'Invalid Card CVV, Please try again'
                                        ],
                                    ],
                                ],
                            ],
                            'filters'  => [
                                ['name' => 'StripTags'],
                                ['name' => 'StringTrim'],
                            ],
                        ],
                    ],
                ],
                'MelisDemoCommerce_checkout_fake_paypal_form' => [
                    'attributes' => [
                        'method' => '',
                        'action' => '',
                    ],
                    'hydrator'  => 'Laminas\Hydrator\ArraySerializable',
                    'elements' => [
                        [
                            'spec' => [
                                'name' => 'order-id',
                                'type' => 'hidden',
                            ]
                        ],
                        [
                            'spec' => [
                                'name' => 'payment-transaction-country-id',
                                'type' => 'hidden',
                            ]
                        ],
                        [
                            'spec' => [
                                'name' => 'payment-transaction-coupon-id',
                                'type' => 'hidden',
                            ]
                        ],
                        [
                            'spec' => [
                                'name' => 'payment-transaction-total-cost',
                                'type' => 'hidden',
                            ]
                        ],
                        [
                            'spec' => [
                                'name' => 'payment-transaction-currency-code',
                                'type' => 'hidden',
                            ]
                        ],
                        [
                            'spec' => [
                                'name' => 'checkout-confirm-url',
                                'type' => 'hidden',
                            ]
                        ],
                        [
                            'spec' => [
                                'name' => 'card-holder-name',
                                'type' => 'Text',
                                'options' => [
                                    'label' => 'Card Holder\'s Name',
                                ],
                                'attributes' => [
                                    'id' => 'card-holder-name',
                                ]
                            ]
                        ],
                        [
                            'spec' => [
                                'name' => 'card-number',
                                'type' => 'Text',
                                'options' => [
                                    'label' => 'Debit/Credit Card Number',
                                ],
                                'attributes' => [
                                    'id' => 'card-number',
                                ]
                            ]
                        ],
                        [
                            'spec' => [
                                'name' => 'expiry-month',
                                'type' => 'Select',
                                'options' => [
                                    'label' => 'Month expiration',
                                    'value_options' => [
                                        '01' => 'Jan (01]',
                                        '02' => 'Feb (02]',
                                        '03' => 'Mar (03]',
                                        '04' => 'Apr (04]',
                                        '05' => 'May (05]',
                                        '06' => 'June (06]',
                                        '07' => 'July (07]',
                                        '08' => 'Aug (08]',
                                        '09' => 'Sep (09]',
                                        '10' => 'Oct (10]',
                                        '11' => 'Nov (11]',
                                        '12' => 'Dec (12]',
                                    ],
                                ],
                                'attributes' => [
                                    'id' => 'expiry-month',
                                ]
                            ]
                        ],
                        [
                            'spec' => [
                                'name' => 'expiry-year',
                                'type' => 'Select',
                                'options' => [
                                    'label' => 'Year expiration',
                                    'value_options' => [
                                        '2017' => '2017',
                                        '2018' => '2018',
                                        '2019' => '2019',
                                        '2020' => '2020',
                                        '2021' => '2021',
                                        '2022' => '2022',
                                        '2023' => '2023',
                                    ],
                                ],
                                'attributes' => [
                                    'id' => 'expiry-year"',
                                ]
                            ]
                        ],
                        [
                            'spec' => [
                                'name' => 'cvv',
                                'type' => 'Text',
                                'options' => [
                                    'label' => 'Card CVV',
                                ],
                                'attributes' => [
                                    'id' => 'cvv',
                                ]
                            ]
                        ],
                    ],
                    'input_filter' => [
                        'card-holder-name' => [
                            'name'     => 'card-holder-name',
                            'required' => true,
                            'validators' => [
                            ],
                            'filters'  => [
                                ['name' => 'StripTags'],
                                ['name' => 'StringTrim'],
                            ],
                        ],
                        'card-number' => [
                            'name'     => 'card-number',
                            'required' => true,
                            'validators' => [
                                [
                                    'name' => 'InArray',
                                    'options' => [
                                        'haystack' => ['1111222233334444'],
                                        'messages' => [
                                            \Laminas\Validator\InArray::NOT_IN_ARRAY => 'Invalid Card number, Please try again'
                                        ],
                                    ],
                                ],
                            ],
                            'filters'  => [
                                ['name' => 'StripTags'],
                                ['name' => 'StringTrim'],
                            ],
                        ],
                        'cvv' => [
                            'name'     => 'cvv',
                            'required' => true,
                            'validators' => [
                                [
                                    'name' => 'InArray',
                                    'options' => [
                                        'haystack' => ['123'],
                                        'messages' => [
                                            \Laminas\Validator\InArray::NOT_IN_ARRAY => 'Invalid Card CVV, Please try again'
                                        ],
                                    ],
                                ],
                            ],
                            'filters'  => [
                                ['name' => 'StripTags'],
                                ['name' => 'StringTrim'],
                            ],
                        ],
                    ],
                ],
            ]
        ]
    ]
];