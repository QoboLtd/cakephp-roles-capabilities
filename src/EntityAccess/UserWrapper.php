<?php
declare(strict_types=1);

namespace RolesCapabilities\EntityAccess;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Groups\Model\Table\GroupsTable;
use Webmozart\Assert\Assert;

class UserWrapper implements SubjectInterface
{
    /**
     * Wraps a user
     *
     * @param array|\ArrayAccess<string,mixed> $user The user to wrap
     * @return SubjectInterface
     */
    public static function forUser($user): SubjectInterface
    {
        return new UserWrapper($user);
    }

    /**
     * @var array|\ArrayAccess<string,mixed>
     */
    private $user;

    /**
     * @param array|\ArrayAccess<string,mixed> $user The user to wrap
     */
    private function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * @inheritdoc
     */
    public function getId(): string
    {
        return $this->user['id'];
    }

    /**
     * Whether the user is a supervisor.
     *
     * @return bool
     */
    private function isSupervisor(): bool
    {
        if (!isset($this->user['is_supervisor'])) {
            return false;
        }

        return (bool)$this->user['is_supervisor'];
    }

    /**
     * @inheritdoc
     */
    public function isSuperuser(): bool
    {
        if (isset($this->user['is_admin'])) {
            return (bool)$this->user['is_admin'];
        }

        if (isset($this->user['is_superuser'])) {
            return (bool)$this->user['is_superuser'];
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getSubordinates(): array
    {
        $subs = [];

        if (!$this->isSupervisor()) {
            return $subs;
        }

        foreach (self::getReportToUsers($this->getId()) as $subordinate) {
            $subs[] = self::forUser($subordinate);
        }

        return $subs;
    }

    /**
     * @param string $userId The userId
     * @return mixed[] The subordinates
     */
    private static function getReportToUsers(string $userId): array
    {
        $table = TableRegistry::get(Configure::read('RolesCapabilities.users.table', 'Users'));

        $users = $table->find()
            ->applyOptions(['filterQuery' => true])
            ->where([
                'reports_to' => $userId,
                ])
            ->all()
            ->toArray();

        if (empty($users)) {
            return $users;
        }

        $userMap = [];
        $filteredUsers = [];

        foreach ($users as $user) {
            if ($user->get('id') === $userId || isset($userMap[$user->get('id')])) {
                continue;
            }

            $userMap[$user->get('id')] = $user;
            $filteredUsers[] = $user;
        }

        return $filteredUsers;
    }

    /**
     * @param mixed[] $data The data
     * @return string[] The ids
     */
    private function toIds(array $data): array
    {
        $ids = [];
        foreach ($data as $group) {
            $ids[] = $group['id'];
        }

        return $ids;
    }

    /**
     * Gets the Groups table
     *
     * @return GroupsTable
     */
    private function getGroupsTable(): GroupsTable
    {
        $table = TableRegistry::get('Groups.Groups');
        Assert::isInstanceOf($table, GroupsTable::class);

        return $table;
    }

    /**
     * @inheritdoc
     */
    public function getGroups(): array
    {
        $table = $this->getGroupsTable();

        $data = $table->getUserGroupsAll($this->getId(), [
            'fields' => ['id'],
            'contain' => [],
            'filterQuery' => true,
        ]);

        return $this->toIds($data);
    }

    /**
     * @inheritdoc
     */
    public function getRoles(): array
    {
        $userGroups = $this->getGroups();
        if (count($userGroups) === 0) {
            return [];
        }

        $groupsTable = $this->getGroupsTable();

        $roles = TableRegistry::getTableLocator()->get('RolesCapabilities.Roles');

        $primaryKey = $groupsTable->getPrimaryKey();
        Assert::string($primaryKey);
        $groupField = $groupsTable->aliasField($primaryKey);

        $userRoles = $roles->find()->select(['id'])->applyOptions(['filterQuery' => true])
            ->matching('Groups', function ($q) use ($userGroups, $groupField) {
                return $q->where([$groupField . ' IN' => $userGroups]);
            })->toArray();

        return $this->toIds($userRoles);
    }

    /**
     * Unwraps the user
     *
     * @return array|\ArrayAccess<string,mixed> The original user
     */
    public function unwrap()
    {
        return $this->user;
    }
}
