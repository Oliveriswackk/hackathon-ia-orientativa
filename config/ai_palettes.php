<?php

/**
 * Paletas para el selector de tema (variables CSS en :root).
 * La vista emite JSON al documento para el script anti-parpadeo en <head>.
 */
return [
    'presets' => [
        'orientavox' => [
            'label' => 'ORIENTAVOX',
            'vars' => [
                '--color-primary' => '#2F77E2',
                '--color-secondary' => '#1e56b8',
                '--color-accent' => '#429FF1',
                '--color-bg' => '#E7EDFE',
                '--color-text' => '#0f172a',
                '--color-surface' => '#ffffff',
                '--color-on-primary' => '#ffffff',
            ],
        ],
        'koi' => [
            'label' => 'KOI FISH',
            'vars' => [
                '--color-primary' => '#5D64EE',
                '--color-secondary' => '#9D5F5E',
                '--color-accent' => '#F79A75',
                '--color-bg' => '#FEF1D8',
                '--color-text' => '#2D2D2D',
                '--color-surface' => '#FFF9EF',
                '--color-on-primary' => '#FEF1D8',
            ],
        ],
        'blood_water' => [
            'label' => 'BLOOD & WATER',
            'vars' => [
                '--color-primary' => '#DF3A31',
                '--color-secondary' => '#D56D6C',
                '--color-accent' => '#718BAE',
                '--color-bg' => '#FAF5DD',
                '--color-text' => '#2A4A71',
                '--color-surface' => '#FFFEF6',
                '--color-on-primary' => '#FAF5DD',
            ],
        ],
        'sand_sea' => [
            'label' => 'SAND & SEA',
            'vars' => [
                '--color-primary' => '#0E8992',
                '--color-secondary' => '#8CB6BC',
                '--color-accent' => '#E9A27C',
                '--color-bg' => '#FDF8F4',
                '--color-text' => '#0D313A',
                '--color-surface' => '#FFFFFF',
                '--color-on-primary' => '#FDF8F4',
            ],
        ],
        'dark' => [
            'label' => 'MODO OSCURO',
            'vars' => [
                '--color-primary' => '#5D64EE',
                '--color-secondary' => '#9D5F5E',
                '--color-accent' => '#F79A75',
                '--color-bg' => '#0F0F13',
                '--color-text' => '#F0EEE8',
                '--color-surface' => '#1A1A22',
                '--color-on-primary' => '#F0EEE8',
            ],
        ],
    ],
];
