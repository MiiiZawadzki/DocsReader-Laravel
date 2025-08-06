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
    'statistics' => [
        'user' => [
            'read' => [
                'name' => 'Document Familiarization Chart',
                'description' => 'The diagram illustrates the proportion of documents you have read (Read) compared to those you have not yet reviewed (Not read).',
            ]
        ]
    ]
];
