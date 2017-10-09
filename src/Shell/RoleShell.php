<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace RolesCapabilities\Shell;

use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;

class RoleShell extends Shell
{
    /**
     * {@inheritDoc}
     */
    public $tasks = [
        'RolesCapabilities.Import'
    ];

    /**
     * {@inheritDoc}
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();

        $parser
            ->description('Roles Shell that handle\'s related tasks.')
            ->addSubcommand(
                'import',
                ['help' => 'Import system role(s).', 'parser' => $this->Import->getOptionParser()]
            );

        return $parser;
    }
}
