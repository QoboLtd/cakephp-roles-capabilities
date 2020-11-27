<?php
declare(strict_types=1);

namespace RolesCapabilities\View\Helper;

use Cake\View\Helper;

class CapabilitiesHelper extends Helper
{
    /**
     * Checks whether the given capability array contains the capability.
     *
     * @param array $capArray The array to check
     * @param string $resource The resource to check
     * @param string $operation The operation to check
     * @param string $association The association to check
     *
     * @return bool
     */
    public function containsCapability(?array $capArray, string $resource, string $operation, string $association): bool
    {
        if ($capArray === null || count($capArray) === 0) {
            return false;
        }

        foreach ($capArray as $cap) {
            if (isset($cap['resource']) && $cap['resource'] !== $resource) {
                continue;
            }

            if (
                $cap['operation'] === $operation
                && $cap['association'] === $association
            ) {
                return true;
            }
        }

        return false;
    }
}
