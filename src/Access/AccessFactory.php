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
namespace RolesCapabilities\Access;

use Cake\Core\Configure;
use RolesCapabilities\Access\AccessInterface;

/**
 *  AccessFactory Class
 *
 *  Base class for checking of user's access rights
 *
 * @author Michael Stepanov <m.stepanov@qobo.biz>
 */
class AccessFactory
{
    /**
     *  List of rules for hasAccess() function
     *
     * @var array
     */
    protected $checkRules = [];

    /**
     *  Constructor
     *
     * @param mixed[] $rules  list of rules to override defalt ones
     */
    public function __construct(array $rules = [])
    {
        $this->checkRules = (array)Configure::read('RolesCapabilities.accessCheck.defaultRules');

        if (!empty($rules)) {
            $this->checkRules = $rules;
        }
    }

    /**
     *  hasAccess
     *
     *  Implement basic logic to check user's access
     *
     * @param mixed[] $url    URL user tries to access for
     * @param mixed[] $user   user's session data
     * @return bool         true in case of access is granted and false otherwise
     */
    public function hasAccess(array $url = [], array $user = []): bool
    {
        foreach ($this->getCheckRules() as $rule) {
            if (! class_exists($rule) || ! in_array(AccessInterface::class, class_implements($rule))) {
                throw new \InvalidArgumentException(sprintf('Unknown rule class: %s', $rule));
            }

            $result = (new $rule)->hasAccess($url, $user);

            if ($result) {
                return true;
            }
        }

        return false;
    }

    /**
     *  Return a list of rules to check access
     *
     * @return mixed[]    list of rules
     */
    public function getCheckRules(): array
    {
        return $this->checkRules;
    }
}
