<?php
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
