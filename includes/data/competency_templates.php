<?php
// Pre-built Competency Templates for Easy Selection
class CompetencyTemplates {
    
    public static function getTemplatesByCategory() {
        return [
            'E-Commerce' => [
                [
                    'name' => 'Online Customer Service',
                    'description' => 'Ability to provide excellent customer service through digital channels including chat, email, social media, and phone support',
                    'weight' => 1.2,
                    'max_score' => 5
                ],
                [
                    'name' => 'Digital Marketing',
                    'description' => 'Skills in SEO, social media marketing, email campaigns, content creation, and online advertising',
                    'weight' => 1.3,
                    'max_score' => 5
                ],
                [
                    'name' => 'Inventory Management',
                    'description' => 'Competency in stock tracking, demand forecasting, supplier management, and inventory optimization',
                    'weight' => 1.1,
                    'max_score' => 5
                ],
                [
                    'name' => 'Data Analysis',
                    'description' => 'Ability to analyze sales data, customer metrics, conversion rates, and generate business insights',
                    'weight' => 1.4,
                    'max_score' => 5
                ],
                [
                    'name' => 'Platform Management',
                    'description' => 'Proficiency in e-commerce platforms (Shopify, WooCommerce), order management, and related tools',
                    'weight' => 0.9,
                    'max_score' => 5
                ]
            ],
            'Leadership' => [
                [
                    'name' => 'Team Leadership',
                    'description' => 'Ability to lead, motivate, and guide team members, delegate tasks, and achieve team goals',
                    'weight' => 1.5,
                    'max_score' => 5
                ],
                [
                    'name' => 'Strategic Thinking',
                    'description' => 'Capacity for long-term planning, strategic decision-making, and aligning activities with organizational goals',
                    'weight' => 1.3,
                    'max_score' => 5
                ],
                [
                    'name' => 'Communication',
                    'description' => 'Effective verbal and written communication, active listening, and presentation skills',
                    'weight' => 1.2,
                    'max_score' => 5
                ],
                [
                    'name' => 'Decision Making',
                    'description' => 'Ability to make timely, well-informed decisions and take responsibility for outcomes',
                    'weight' => 1.4,
                    'max_score' => 5
                ],
                [
                    'name' => 'Performance Management',
                    'description' => 'Skills in setting goals, monitoring performance, providing feedback, and developing team members',
                    'weight' => 1.3,
                    'max_score' => 5
                ]
            ],
            'Technical' => [
                [
                    'name' => 'Software Development',
                    'description' => 'Proficiency in programming languages, coding practices, debugging, and software development lifecycle',
                    'weight' => 1.5,
                    'max_score' => 5
                ],
                [
                    'name' => 'Database Management',
                    'description' => 'Skills in database design, SQL queries, data modeling, and performance optimization',
                    'weight' => 1.2,
                    'max_score' => 5
                ],
                [
                    'name' => 'System Integration',
                    'description' => 'Ability to integrate systems, APIs, third-party services, and ensure seamless data flow',
                    'weight' => 1.3,
                    'max_score' => 5
                ],
                [
                    'name' => 'Technical Documentation',
                    'description' => 'Ability to create clear technical documentation, user guides, and system specifications',
                    'weight' => 1.0,
                    'max_score' => 5
                ],
                [
                    'name' => 'Problem Solving',
                    'description' => 'Analytical thinking, troubleshooting skills, and ability to resolve technical issues efficiently',
                    'weight' => 1.4,
                    'max_score' => 5
                ]
            ],
            'Customer Service' => [
                [
                    'name' => 'Customer Communication',
                    'description' => 'Excellent verbal and written communication skills for interacting with customers professionally',
                    'weight' => 1.3,
                    'max_score' => 5
                ],
                [
                    'name' => 'Problem Resolution',
                    'description' => 'Ability to identify, analyze, and resolve customer issues quickly and effectively',
                    'weight' => 1.4,
                    'max_score' => 5
                ],
                [
                    'name' => 'Product Knowledge',
                    'description' => 'Comprehensive understanding of company products, services, and policies',
                    'weight' => 1.1,
                    'max_score' => 5
                ],
                [
                    'name' => 'Empathy & Patience',
                    'description' => 'Ability to understand customer perspectives and maintain patience in difficult situations',
                    'weight' => 1.2,
                    'max_score' => 5
                ],
                [
                    'name' => 'Multi-Channel Support',
                    'description' => 'Proficiency in handling customer inquiries across phone, email, chat, and social media',
                    'weight' => 1.0,
                    'max_score' => 5
                ]
            ],
            'Sales' => [
                [
                    'name' => 'Sales Techniques',
                    'description' => 'Mastery of various sales methodologies, closing techniques, and sales process management',
                    'weight' => 1.5,
                    'max_score' => 5
                ],
                [
                    'name' => 'Relationship Building',
                    'description' => 'Ability to build trust, rapport, and long-term relationships with customers and prospects',
                    'weight' => 1.3,
                    'max_score' => 5
                ],
                [
                    'name' => 'Negotiation Skills',
                    'description' => 'Skills in negotiating terms, handling objections, and reaching mutually beneficial agreements',
                    'weight' => 1.4,
                    'max_score' => 5
                ],
                [
                    'name' => 'Market Knowledge',
                    'description' => 'Understanding of market trends, competitor analysis, and industry dynamics',
                    'weight' => 1.0,
                    'max_score' => 5
                ],
                [
                    'name' => 'CRM Proficiency',
                    'description' => 'Effective use of CRM systems for lead management, pipeline tracking, and customer data management',
                    'weight' => 1.1,
                    'max_score' => 5
                ]
            ]
        ];
    }
    
    public static function getQuickStartModels() {
        return [
            [
                'name' => 'Entry Level E-Commerce',
                'description' => 'Basic competencies for new e-commerce employees',
                'category' => 'E-Commerce',
                'target_roles' => ['employee', 'junior_staff'],
                'assessment_method' => 'self_assessment',
                'competencies' => [
                    'Online Customer Service',
                    'Platform Management',
                    'Product Knowledge',
                    'Communication',
                    'Digital Literacy'
                ]
            ],
            [
                'name' => 'E-Commerce Manager',
                'description' => 'Leadership and management competencies for e-commerce managers',
                'category' => 'Leadership',
                'target_roles' => ['manager', 'team_lead'],
                'assessment_method' => 'manager_review',
                'competencies' => [
                    'Team Leadership',
                    'Strategic Thinking',
                    'Performance Management',
                    'Digital Marketing',
                    'Data Analysis'
                ]
            ],
            [
                'name' => 'Customer Service Excellence',
                'description' => 'Comprehensive customer service competencies',
                'category' => 'Customer Service',
                'target_roles' => ['customer_service', 'support_staff'],
                'assessment_method' => 'peer_review',
                'competencies' => [
                    'Customer Communication',
                    'Problem Resolution',
                    'Product Knowledge',
                    'Empathy & Patience',
                    'Multi-Channel Support'
                ]
            ]
        ];
    }
}
?>
