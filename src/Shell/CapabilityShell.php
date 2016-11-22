<?php
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
