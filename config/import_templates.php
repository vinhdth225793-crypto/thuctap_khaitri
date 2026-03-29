<?php

return [
    'base_directory' => 'templates/imports',

    'templates' => [
        'question_bank_mcq' => [
            'disk' => 'local',
            'path' => 'templates/imports/cau-hoi/mau-import-cau-hoi-trac-nghiem.xlsx',
            'download_name' => 'mau-import-cau-hoi-trac-nghiem.xlsx',
            'sheet' => 'Mau_Import',
            'data_starts_on_row' => 7,
            'headers' => [
                'cau_hoi',
                'dap_an_1',
                'dap_an_2',
                'dap_an_3',
                'dap_an_4',
                'dap_an_dung',
            ],
        ],
    ],

    'legacy_profiles' => [
        'question_bank_mcq_csv' => [
            'headers' => [
                'cau_hoi',
                'dap_an_sai_1',
                'dap_an_sai_2',
                'dap_an_sai_3',
                'dap_an_dung',
            ],
        ],
    ],
];
