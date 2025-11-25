<?php

return [
    'charts' => [
        'user' => [
            'read' => [
                'name' => 'Wykres zapoznania z dokumentami',
                'description' => 'Wykres przedstawia proporcję dokumentów, które przeczytałeś (Przeczytane) w porównaniu do tych, których jeszcze nie przejrzałeś (Nieprzeczytane).',
            ]
        ],
        'manage' => [
            'read' => [
                'name' => 'Wykres wskaźnika zapoznania z dokumentami',
                'description' => 'Wykres przedstawia proporcję dokumentów przeczytanych przez przypisanych użytkowników w porównaniu do tych, które pozostały nieprzejrzane.',
            ],
        ],
        'document' => [
            'read' => [
                'name' => 'Wykres wskaźnika zapoznania z dokumentem',
                'description' => 'Wykres przedstawia proporcję użytkowników, którzy przeczytali dokument w porównaniu do tych, którzy jeszcze go nie przejrzeli.',
            ],
        ]
    ],
    'statistics' => [
        'user' => [
            'active' => [
                'name' => 'Aktywne dokumenty',
                'description' => 'Aktywne dokumenty przypisane do Ciebie w systemie.',
            ],
            'total' => [
                'name' => 'Wszystkie dokumenty',
                'description' => 'Całkowita liczba dokumentów przypisanych do Ciebie w systemie.',
            ],
            'read' => [
                'name' => 'Przeczytane dokumenty',
                'description' => 'Całkowita liczba przeczytanych dokumentów przypisanych do Ciebie w systemie.',
            ]
        ],
        'manage' => [
            'active' => [
                'name' => 'Aktywne dokumenty',
                'description' => 'Aktywne dokumenty utworzone przez Ciebie w systemie.',
            ],
            'total' => [
                'name' => 'Wszystkie dokumenty',
                'description' => 'Całkowita liczba dokumentów utworzonych przez Ciebie w systemie.',
            ],
        ],
        'document' => [
            'assigned' => [
                'name' => 'Przypisani użytkownicy',
                'description' => 'Całkowita liczba użytkowników przypisanych do dokumentu.',
            ],
            'reads' => [
                'name' => 'Przeczytania',
                'description' => 'Całkowita liczba przypisanych użytkowników, którzy przeczytali dokument.',
            ],
            'ratio' => [
                'name' => 'Wskaźnik',
                'description' => 'Wskaźnik użytkowników, którzy przeczytali dokument.',
            ],
        ],
    ]
];
