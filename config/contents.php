<?php
return [
    'rivo' => [
        'hero' => [
            'multiple' => [
                'field_name' => [
                    'heading' => 'text',
                    'sub_heading' => 'text',
                    'button_name' => 'text',
                    'button_link' => 'url',
                    'image' => 'file',
                ],
                'validation' => [
                    'heading.*' => 'required|max:5000',
                    'sub_heading.*' => 'required|max:10000',
                    'button_name.*' => 'required|max:1000',
                    'button_link.*' => 'required|url',
                    'image.*' => 'nullable|max:10240|image|mimes:jpg,jpeg,png',
                ]
            ],
            'preview' => 'assets/preview/rivo/hero.png',
        ],

        'about' => [
            'single' => [
                'field_name' => [
                    'heading' => 'text',
                    'sub_heading' => 'text',
                    'description' => 'textarea',
                ],
                'validation' => [
                    'heading.*' => 'required|max:100',
                    'sub_heading.*' => 'required|max:100',
                    'description.*' => 'required|max:1000',
                ]
            ],
            'multiple' => [
                'field_name' => [
                    'heading' => 'text',
                    'description' => 'textarea',
                ],
                'validation' => [
                    'heading.*' => 'required|max:5000',
                    'description.*' => 'required|max:10000',
                ]
            ],
            'preview' => 'assets/preview/rivo/about.png'
        ],

        'service' => [
            'single' => [
                'field_name' => [
                    'heading' => 'text',
                    'sub_heading' => 'text',
                    'description' => 'textarea',
                ],
                'validation' => [
                    'heading.*' => 'required|max:100',
                    'sub_heading.*' => 'required|max:100',
                    'description.*' => 'required|max:1000',
                ]
            ],
            'multiple' => [
                'field_name' => [
                    'title' => 'text',
                    'sub_title' => 'text',
                    'image' => 'file',
                ],
                'validation' => [
                    'title.*' => 'required|max:5000',
                    'sub_title.*' => 'required|max:10000',
                    'image.*' => 'nullable|max:10240|image|mimes:jpg,jpeg,png',
                ]
            ],
            'preview' => 'assets/preview/rivo/service.png'
        ],

        'how_it_works' => [
            'single' => [
                'field_name' => [
                    'heading' => 'text',
                    'sub_heading' => 'text',
                    'description' => 'textarea',
                    'image' => 'file',
                ],
                'validation' => [
                    'heading.*' => 'required|max:100',
                    'sub_heading.*' => 'required|max:100',
                    'description.*' => 'required|max:10000',
                    'image.*' => 'nullable|max:10240|image|mimes:jpg,jpeg,png',
                ]
            ],
            'multiple' => [
                'field_name' => [
                    'title' => 'text',
                    'sub_title' => 'text',
                ],
                'validation' => [
                    'title.*' => 'required|max:5000',
                    'sub_title.*' => 'required|max:10000',
                ]
            ],
            'preview' => 'assets/preview/rivo/how_it_works.png'
        ],

        'features' => [
            'single' => [
                'field_name' => [
                    'heading' => 'text',
                    'sub_heading' => 'text',
                    'description' => 'textarea',
                    'image' => 'file',
                ],
                'validation' => [
                    'heading.*' => 'required|max:100',
                    'sub_heading.*' => 'required|max:100',
                    'description.*' => 'required|max:10000',
                    'image.*' => 'nullable|max:10240|image|mimes:jpg,jpeg,png',
                ]
            ],
            'multiple' => [
                'field_name' => [
                    'title' => 'text',
                    'sub_title' => 'text',
                    'image' => 'file',
                ],
                'validation' => [
                    'title.*' => 'required|max:5000',
                    'sub_title.*' => 'required|max:10000',
                    'image.*' => 'nullable|max:10240|image|mimes:jpg,jpeg,png',
                ]
            ],
            'preview' => 'assets/preview/rivo/features.png'
        ],

        'testimonial' => [
            'single' => [
                'field_name' => [
                    'heading' => 'text',
                    'sub_heading' => 'text',
                ],
                'validation' => [
                    'heading.*' => 'required|max:100',
                    'sub_heading.*' => 'required|max:300',
                ]
            ],
            'multiple' => [
                'field_name' => [
                    'name' => 'text',
                    'feedback' => 'textarea',
                    'image' => 'file',
                ],
                'validation' => [
                    'name.*' => 'required|max:5000',
                    'feedback.*' => 'required|max:10000',
                    'image.*' => 'nullable|max:10240|image|mimes:jpg,jpeg,png',
                ]
            ],
            'preview' => 'assets/preview/rivo/testimonial.png'
        ],

        'faq' => [
            'single' => [
                'field_name' => [
                    'heading' => 'text',
                    'sub_heading' => 'text',
                    'description' => 'textarea',
                ],
                'validation' => [
                    'heading.*' => 'required|max:100',
                    'sub_heading.*' => 'required|max:300',
                    'description.*' => 'required|max:10000',
                ]
            ],
            'multiple' => [
                'field_name' => [
                    'question' => 'text',
                    'answer' => 'textarea',
                ],
                'validation' => [
                    'question.*' => 'required|max:300',
                    'answer.*' => 'required|max:10000',
                ]
            ],
            'preview' => 'assets/preview/rivo/faq.png'
        ],

        'blog' => [
            'single' => [
                'field_name' => [
                    'heading' => 'text',
                    'sub_heading' => 'text',
                    'description' => 'textarea',
                ],
                'validation' => [
                    'heading.*' => 'required|max:100',
                    'sub_heading.*' => 'required|max:300',
                    'description.*' => 'required|max:10000',
                ]
            ],
            'preview' => 'assets/preview/rivo/blog.png'
        ],

        'contact' => [
            'single' => [
                'field_name' => [
                    'heading' => 'text',
                    'sub_heading' => 'text',
                ],
                'validation' => [
                    'heading.*' => 'required|max:100',
                    'sub_heading.*' => 'required|max:300',
                ]
            ],
            'multiple' => [
                'field_name' => [
                    'title' => 'text',
                    'description' => 'textarea',
                    'image' => 'file',
                ],
                'validation' => [
                    'title.*' => 'required|max:1000',
                    'description.*' => 'required|max:10000',
                    'image.*' => 'required|max:1000',
                ]
            ],
            'preview' => 'assets/preview/rivo/contact.png'
        ],

        'auth_pages' => [
            'single' => [
                'field_name' => [
                    'login_title' => 'text',
                    'login_sub_title' => 'text',
                    'login_description' => 'textarea',
                    'login_button_name' => 'text',
                    'login_button_link' => 'url',

                    'register_title' => 'text',
                    'register_sub_title' => 'text',
                    'register_description' => 'textarea',
                    'register_button_name' => 'text',
                    'register_button_link' => 'url',

                    'forgot_password_title' => 'text',
                    'forgot_password_sub_title' => 'text',
                    'forgot_password_description' => 'text',
                ],
                'validation' => [
                    'login_title.*' => 'required|max:100',
                    'login_sub_title.*' => 'required|max:300',
                    'login_description.*' => 'nullable|max:1000',
                    'login_button_name.*' => 'nullable|max:50',
                    'login_button_link.*' => 'nullable|url|max:500',

                    'register_title.*' => 'required|max:100',
                    'register_sub_title.*' => 'required|max:300',
                    'register_description.*' => 'nullable|max:1000',
                    'register_button_name.*' => 'nullable|max:50',
                    'register_button_link.*' => 'nullable|url|max:500',

                    'forgot_password_title.*' => 'nullable|max:100',
                    'forgot_password_sub_title.*' => 'nullable|max:300',
                    'forgot_password_description.*' => 'nullable|max:1000',
                ]
            ],
            'preview' => 'assets/preview/rivo/auth_pages.png'
        ]
    ],

    //nova
    'nova' => [
        'hero' => [
            'single' => [
                'field_name' => [
                    'button_name' => 'text',
                    'button_link' => 'url',
                    'my_link_name' => 'text',
                    'my_link' => 'url',
                    'user_count' => 'text',
                    'user_text' => 'text',
                    'image' => 'file',
                    'image_one' => 'file',
                    'image_two' => 'file',
                ],
                'validation' => [
                    'button_name.*' => 'required|max:100',
                    'button_link.*' => 'nullable|url',
                    'my_link_name.*' => 'required|max:100',
                    'my_link.*' => 'nullable|url',
                    'user_count.*' => 'required|max:100',
                    'user_text.*' => 'required|max:1000',
                    'image.*' => 'nullable|max:10240|image|mimes:jpg,jpeg,png',
                    'image_one.*' => 'nullable|max:10240|image|mimes:jpg,jpeg,png',
                    'image_two.*' => 'nullable|max:10240|image|mimes:jpg,jpeg,png',
                ]
            ],
            'multiple' => [
                'field_name' => [
                    'title' => 'text',
                    'sub_title' => 'text',
                ],
                'validation' => [
                    'title.*' => 'required|max:5000',
                    'sub_title.*' => 'required|max:10000',
                ]
            ],
            'preview' => 'assets/preview/nova/hero.png',
        ],

        'how_it_works' => [
            'single' => [
                'field_name' => [
                    'heading' => 'text',
                    'sub_heading' => 'text',
                    'description' => 'textarea',
                ],
                'validation' => [
                    'heading.*' => 'required|max:100',
                    'sub_heading.*' => 'required|max:100',
                    'description.*' => 'required|max:10000',
                ]
            ],
            'multiple' => [
                'field_name' => [
                    'title' => 'text',
                    'sub_title' => 'text',
                    'button_name' => 'text',
                    'button_link' => 'url',
                    'image' => 'file',
                ],
                'validation' => [
                    'title.*' => 'required|max:5000',
                    'sub_title.*' => 'required|max:10000',
                    'button_name.*' => 'required|max:1000',
                    'button_link.*' => 'required|url',
                    'image.*' => 'nullable|max:10240|image|mimes:jpg,jpeg,png',
                ]
            ],
            'preview' => 'assets/preview/nova/how_it_works.png',
        ],

        'about' => [
            'single' => [
                'field_name' => [
                    'heading' => 'text',
                    'sub_heading' => 'text',
                    'description' => 'textarea',
                    'mission_title' => 'text',
                    'mission_summary' => 'textarea',
                    'vision_title' => 'text',
                    'vision_summary' => 'textarea',
                    'button_name' => 'text',
                    'button_link' => 'url',
                    'image' => 'file',
                ],
                'validation' => [
                    'heading.*' => 'required|max:5000',
                    'sub_heading.*' => 'required|max:10000',
                    'description.*' => 'required|max:10000',
                    'mission_title' => 'nullable|max:10000',
                    'mission_summary' => 'nullable|max:10000',
                    'vision_title' => 'nullable|max:10000',
                    'vision_summary' => 'nullable|max:10000',
                    'button_name.*' => 'required|max:1000',
                    'button_link.*' => 'required|url',
                    'image.*' => 'nullable|max:10240|image|mimes:jpg,jpeg,png',
                ]
            ],
            'multiple' => [
                'field_name' => [
                    'title' => 'text',
                ],
                'validation' => [
                    'title.*' => 'required|max:500',
                ]
            ],
            'preview' => 'assets/preview/nova/about.png'
        ],

        'features' => [
            'single' => [
                'field_name' => [
                    'heading' => 'text',
                    'sub_heading' => 'text',
                    'description' => 'textarea',
                    'image' => 'file',
                ],
                'validation' => [
                    'heading.*' => 'required|max:5000',
                    'sub_heading.*' => 'required|max:10000',
                    'description.*' => 'required|max:1000',
                    'image.*' => 'nullable|max:10240|image|mimes:jpg,jpeg,png',
                ]
            ],
            'multiple' => [
                'field_name' => [
                    'title' => 'text',
                ],
                'validation' => [
                    'title.*' => 'required|max:5000',
                ]
            ],
            'preview' => 'assets/preview/nova/features.png',
            'theme' => 'nova'
        ],

        'brand' => [
            'single' => [
                'field_name' => [
                    'heading' => 'text',
                ],
                'validation' => [
                    'heading.*' => 'required|max:5000',
                ]
            ],
            'multiple' => [
                'field_name' => [
                    'image' => 'file',
                ],
                'validation' => [
                    'image.*' => 'nullable|max:10240|image|mimes:jpg,jpeg,png',
                ]
            ],
            'preview' => 'assets/preview/nova/brand.png'
        ],

        'service' => [
            'single' => [
                'field_name' => [
                    'heading' => 'text',
                    'sub_heading' => 'text',
                    'description' => 'textarea',
                ],
                'validation' => [
                    'heading.*' => 'required|max:100',
                    'sub_heading.*' => 'required|max:100',
                    'description.*' => 'required|max:1000',
                ]
            ],
            'multiple' => [
                'field_name' => [
                    'title' => 'text',
                    'sub_title' => 'text',
                    'my_link' => 'url',
                    'icon' => 'icon',
                ],
                'validation' => [
                    'title.*' => 'required|max:5000',
                    'sub_title.*' => 'required|max:10000',
                    'my_link.*' => 'required|url',
                    'icon.*' => 'required|max:1000',
                ]
            ],
            'preview' => 'assets/preview/nova/service.png'
        ],

        'video' => [
            'single' => [
                'field_name' => [
                    'my_link' => 'url',
                    'image' => 'file',
                ],
                'validation' => [
                    'my_link.*' => 'required|url',
                    'image.*' => 'nullable|max:10240|image|mimes:jpg,jpeg,png',
                ]
            ],
            'preview' => 'assets/preview/nova/video.png'
        ],

        'gateway' => [
            'single' => [
                'field_name' => [
                    'heading' => 'text',
                    'sub_heading' => 'text',
                    'description' => 'textarea',
                    'button_name' => 'text',
                    'button_link' => 'url',
                    'title' => 'text',
                    'title_two' => 'text',
                    'sub_title' => 'text',
                    'image' => 'file',
                    'image_one' => 'file',
                    'image_two' => 'file',
                ],
                'validation' => [
                    'heading.*' => 'required|max:100',
                    'sub_heading.*' => 'required|max:300',
                    'description.*' => 'required|max:10000',
                    'button_name.*' => 'required|max:1000',
                    'button_link.*' => 'required|url',
                    'title.*' => 'required|max:1000',
                    'title_two.*' => 'required|max:1000',
                    'sub_title.*' => 'required|max:1000',
                    'image.*' => 'nullable|max:10240|image|mimes:jpg,jpeg,png',
                    'image_one.*' => 'nullable|max:10240|image|mimes:jpg,jpeg,png',
                    'image_two.*' => 'nullable|max:10240|image|mimes:jpg,jpeg,png',
                ]
            ],
            'preview' => 'assets/preview/nova/gateway.png'
        ],

        'testimonial' => [
            'multiple' => [
                'field_name' => [
                    'title' => 'text',
                    'feedback' => 'textarea',
                    'name' => 'text',
                    'designation' => 'text',
                    'image' => 'file',
                ],
                'validation' => [
                    'title.*' => 'required|max:10000',
                    'feedback.*' => 'required|max:10000',
                    'name.*' => 'required|max:5000',
                    'designation.*' => 'required|max:5000',
                    'image.*' => 'nullable|max:10240|image|mimes:jpg,jpeg,png',
                ]
            ],
            'preview' => 'assets/preview/nova/testimonial.png'
        ],

        'faq' => [
            'single' => [
                'field_name' => [
                    'heading' => 'text',
                    'sub_heading' => 'text',
                ],
                'validation' => [
                    'heading.*' => 'required|max:100',
                    'sub_heading.*' => 'required|max:300',
                ]
            ],
            'multiple' => [
                'field_name' => [
                    'question' => 'text',
                    'answer' => 'textarea',
                ],
                'validation' => [
                    'question.*' => 'required|max:300',
                    'answer.*' => 'required|max:10000',
                ]
            ],
            'preview' => 'assets/preview/nova/faq.png'
        ],

        'contact' => [
            'single' => [
                'field_name' => [
                    'heading' => 'text',
                    'button_name' => 'text',
                    'image' => 'file',
                ],
                'validation' => [
                    'heading.*' => 'required|max:100',
                    'button_name.*' => 'required|max:300',
                    'image.*' => 'nullable|max:10240|image|mimes:jpg,jpeg,png',
                ]
            ],
            'preview' => 'assets/preview/nova/contact.png'
        ],

        'contact_info' => [
            'single' => [
                'field_name' => [
                    'title' => 'text',
                    'sub_title' => 'text',
                ],
                'validation' => [
                    'title.*' => 'required|max:100',
                    'sub_title.*' => 'required|max:1000',
                ]
            ],
            'multiple' => [
                'field_name' => [
                    'name' => 'text',
                    'icon' => 'icon',
                    'value' => 'text',
                ],
                'validation' => [
                    'name.*' => 'required|max:1000',
                    'icon.*' => 'required|max:10000',
                    'value.*' => 'required|max:1000',
                ]
            ],
            'preview' => 'assets/preview/nova/contact_info.png'
        ],

        'newsletter' => [
            'single' => [
                'field_name' => [
                    'heading' => 'text',
                    'sub_heading' => 'text',
                    'button_name' => 'text',
                ],
                'validation' => [
                    'heading.*' => 'required|max:100',
                    'sub_heading.*' => 'required|max:300',
                    'button_name.*' => 'required|max:1000',
                ]
            ],
            'preview' => 'assets/preview/nova/newsletter.png'
        ]
    ],

    'all' => [
        'counter' => [
            'single' => [
                'field_name' => [
                    'heading' => 'text',
                    'sub_heading' => 'text',
                    'description' => 'textarea',
                    'total_users' => 'number',
                    'global_office' => 'number',
                    'total_wallet' => 'number',
                    'total_gateways' => 'number',
                ],
                'validation' => [
                    'heading.*' => 'required|max:100',
                    'sub_heading.*' => 'required|max:300',
                    'description.*' => 'required|max:10000',
                    'total_users.*' => 'required|integer',
                    'global_office.*' => 'required|integer',
                    'total_wallet.*' => 'required|integer',
                    'total_gateways.*' => 'required|integer',
                ]
            ],
            'preview' => 'assets/preview/:theme/counter.png'
        ],

        'footer' => [
            'single' => [
                'field_name' => [
                    'phone' => 'text',
                    'email' => 'text',
                    'address' => 'text',
                    'description' => 'textarea',

                    'cookie_title' => 'text',
                    'cookie_sub_title' => 'text',
                    'cookie_button_link' => 'url',
                ],
                'validation' => [
                    'phone.*' => 'required|max:100',
                    'email.*' => 'required|max:100',
                    'address.*' => 'required|max:100',
                    'description.*' => 'required|max:10000',

                    'cookie_title.*' => 'required|max:100',
                    'cookie_sub_title.*' => 'required|max:1000',
                    'cookie_button_link.*' => 'nullable|url',
                ]
            ],
            'multiple' => [
                'field_name' => [
                    'name' => 'text',
                    'icon' => 'icon',
                    'my_link' => 'url',
                ],
                'validation' => [
                    'name.*' => 'required|max:1000',
                    'icon.*' => 'required|max:1000',
                    'my_link.*' => 'required|url',
                ]
            ],
            'preview' => 'assets/preview/:theme/footer.png'
        ],
    ],



    'message' => [
        'required' => 'This field is required.',
        'min' => 'This field must be at least :min characters.',
        'max' => 'This field may not be greater than :max characters.',
        'image' => 'This field must be image.',
        'mimes' => 'This image must be a file of type: jpg, jpeg, png.',
        'integer' => 'This field must be an integer value',
    ],

    'content_media' => [
        'image' => 'file',
        'image_one' => 'file',
        'image_two' => 'file',
        'thumb_image' => 'file',
        'my_link' => 'url',
        'button_link' => 'url',
        'login_button_link' => 'url',
        'register_button_link' => 'url',
        'cookie_button_link' => 'url',
        'icon' => 'icon',
        'start_date' => 'date',
        'count_number' => 'number',
        'total_users' => 'number',
        'global_office' => 'number',
        'total_wallet' => 'number',
        'total_gateways' => 'number',
    ]
];

