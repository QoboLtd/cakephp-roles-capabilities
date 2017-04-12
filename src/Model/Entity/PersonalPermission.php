<?php
namespace RolesCapabilities\Model\Entity;

use Cake\ORM\Entity;

/**
 * PersonalPermission Entity
 *
 * @property string $id
 * @property string $foreign_key
 * @property string $model
 * @property string $user_id
 * @property string $creator
 * @property string $type
 * @property bool $is_active
 * @property \Cake\I18n\Time $expired
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \RolesCapabilities\Model\Entity\User $user
 */
class PersonalPermission extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
}
