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

class CapabilityShell extends Shell
{
    /**
     * {@inheritDoc}
     */
    public $tasks = [
        'RolesCapabilities.Assign'
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
                'assign',
                [
                    'help' => 'Assign all capabilities to \'Admins\' role.',
                    'parser' => $this->Assign->getOptionParser()
                ]
            );

        return $parser;
    }
}
