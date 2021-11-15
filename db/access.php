<?php

$capabilities = [
    'qtype/moopt:create' => [
        'riskbitmask' => RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array (
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ],
];
