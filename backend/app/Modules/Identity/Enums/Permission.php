<?php

namespace App\Modules\Identity\Enums;

/**
 * Permissions du back-office. Les contrôles d'accès s'appuient sur les permissions
 * (et non sur les rôles) pour pouvoir ajuster les rôles sans toucher au code.
 */
enum Permission: string
{
    case ManageUsers = 'users.manage';
    case ManageRoles = 'roles.manage';
    case ManageCurriculum = 'curriculum.manage';
    case EditContent = 'content.edit';
    case PublishContent = 'content.publish';
    case ManageExams = 'exams.manage';
    case ModerateContent = 'content.moderate';
    case ViewAiLogs = 'ai.logs.view';
    case ManageBilling = 'billing.manage';
    case ViewStatistics = 'statistics.view';

    /** @return array<string, list<self>> */
    public static function matrix(): array
    {
        return [
            Role::SuperAdmin->value => self::cases(),
            Role::PedagogicalAdmin->value => [
                self::ManageCurriculum, self::EditContent, self::PublishContent,
                self::ManageExams, self::ViewStatistics,
            ],
            Role::Contributor->value => [self::EditContent],
            Role::Moderator->value => [self::ModerateContent, self::ViewAiLogs],
            Role::Parent->value => [],
            Role::Student->value => [],
        ];
    }
}
