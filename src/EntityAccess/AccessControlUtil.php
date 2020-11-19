<?php
declare(strict_types=1);

namespace RolesCapabilities\EntityAccess;

class AccessControlUtil
{
    use AccessControlTrait;

    /**
     * @var ?SubjectInterface
     */
    private $subject;

    /**
     * @param ?SubjectInterface $subject The subject
     */
    public function __construct(?SubjectInterface $subject)
    {
        $this->subject = $subject;
    }

    /**
     * Creates a new AccesControlUtil from the current context
     *
     * @return AccessControlUtil
     */
    public static function fromContext(): AccessControlUtil
    {
        $ctx = AuthorizationContextHolder::context();

        if (empty($ctx)) {
            $subject = null;
        } else {
            $subject = $ctx->subject();
        }

        return new AccessControlUtil($subject);
    }

    /**
     * Checks whether the given operation is allowed.
     * @param Table $table The table to check
     * @param string $operation The operation
     * @param ?string $entityId The entity
     * @return bool
     */
    public function isAllowed(Table $table, string $operation, ?string $entityId): bool
    {
        return $this->isTableActionAuthorized($table, $operation, $entityId, $this->subject);
    }
}
