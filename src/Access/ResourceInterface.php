<?php
namespace RolesCapabilities\Access;

interface ResourceInterface
{
    const OPERATION_CREATE = 'create';
    const OPERATION_READ = 'read';
    const OPERATION_UPDATE = 'update';
    const OPERATION_DELETE = 'delete';
}
