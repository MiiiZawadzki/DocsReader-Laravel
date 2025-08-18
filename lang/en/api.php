<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during API responses.
    |
    */

    'document' => [
        'store' => [
            'success' => 'Document: :name created successfully',
        ],
        'read' => [
            'success' => 'Familiarization with the Document: :name confirmed',
        ],
        'update' => [
            'success' => 'Document: :name updated successfully',
        ],
        'manage' => [
            'userAssignment' => [
                'success' => 'User assignment successful',
            ]
        ],
        'statuses' => [
            'new' => 'New',
            'read' => 'Read',
        ],
        'not_found' => 'Document not found.'
    ],
    'settings' => [
        'update' => [
            'success' => 'User data updated successfully',
        ],
    ],
    'charts' => [
        'user' => [
            'read' => [
                'name' => 'Document Familiarization Chart',
                'description' => 'The diagram illustrates the proportion of documents you have read (Read) compared to those you have not yet reviewed (Not read).',
            ]
        ],
        'manage' => [
            'read' => [
                'name' => 'Document Familiarization Ratio Chart',
                'description' => 'This diagram shows the proportion of documents read by assigned users compared to those that remain unreviewed.',
            ],
        ]
    ],
    'statistics' => [
        'user' => [
            'active' => [
                'name' => 'Active Documents',
                'description' => 'Active Documents assigned to You in the system.',
            ],
            'total' => [
                'name' => 'Total Documents',
                'description' => 'Total number of Documents assigned to You in the system.',
            ],
            'read' => [
                'name' => 'Read Documents',
                'description' => 'Total number of read Documents assigned to You in the system.',
            ]
        ],
        'manage' => [
            'active' => [
                'name' => 'Active Documents',
                'description' => 'Active Documents created by You in the system.',
            ],
            'total' => [
                'name' => 'Total Documents',
                'description' => 'Total number of Documents created by You in the system.',
            ],
        ]
    ]
];
