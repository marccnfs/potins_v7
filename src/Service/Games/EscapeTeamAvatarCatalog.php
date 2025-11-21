<?php

namespace App\Service\Games;

/**
 * Central list of the available avatars for team mode so front/back share the same constraints.
 */
class EscapeTeamAvatarCatalog
{
    private const TEAM_AVATARS = [
        'astronaut',
        'dragon',
        'robot',
        'unicorn',
        'penguin',
        'ninja',
        'pirate',
        'octopus',
        'koala',
        'alien',
        'rocket',
        't-rex',
    ];

    private const MEMBER_AVATARS = [
        'astronaut',
        'dragon',
        'robot',
        'unicorn',
        'penguin',
        'ninja',
        'pirate',
        'octopus',
        'koala',
        'alien',
        'rocket',
        't-rex',
        'owl',
        'fox',
        'cat',
        'dog',
    ];

    /** @return string[] */
    public function getTeamAvatars(): array
    {
        return self::TEAM_AVATARS;
    }

    /** @return string[] */
    public function getMemberAvatars(): array
    {
        return self::MEMBER_AVATARS;
    }

    public function isValidTeamAvatar(string $avatarKey): bool
    {
        return in_array($avatarKey, self::TEAM_AVATARS, true);
    }

    public function isValidMemberAvatar(string $avatarKey): bool
    {
        return in_array($avatarKey, self::MEMBER_AVATARS, true);
    }
}
