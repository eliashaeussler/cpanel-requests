<?php

declare(strict_types=1);

$header = <<<EOM
This file is part of the Composer package "eliashaeussler/cpanel-requests".

Copyright (C) %d Elias Häußler <elias@haeussler.dev>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <https://www.gnu.org/licenses/>.
EOM;

$config = new \PhpCsFixer\Config();

$config
    ->setRules([
        '@PSR2' => true,
        '@Symfony' => true,
        'global_namespace_import' => ['import_classes' => true, 'import_functions' => true],
        'header_comment' => [
            'header' => sprintf($header, date('Y')),
            'comment_type' => 'comment',
            'location' => 'after_declare_strict',
            'separate' => 'both',
        ],
        'no_superfluous_phpdoc_tags' => ['allow_mixed' => true],
        'ordered_imports' => ['imports_order' => ['const', 'class', 'function']],
    ])
;

$config->getFinder()
    ->files()
    ->name(['*.php', 'cpanel-requests'])
    ->in(__DIR__)
    ->ignoreVCSIgnored(true)
;

return $config;
