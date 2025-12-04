<?php

namespace App\Service\Games;

/**
 * Central list of the available avatars for team mode so front/back share the same constraints.
 */
class EscapeTeamAvatarCatalog
{
    private const AVATAR_IMAGES = [
        'Loupe' => 'escape/icône_les_boises_temoins.svg',
        'Aureo' => 'escape/icône_les_saints_jean_foutistes.svg',
        'Bidon' => 'escape/icône_les_bidons_de_boiseau.svg',
        'Agent' => 'escape/icône_les_agents_tres_speciaux.svg',
        'Radeau' => 'escape/icône_les_naufrages_villa_cheminée.svg',
        'Bulle' => 'escape/icône_les_elus_sans_filtre.svg',
        'Vœux' => 'escape/icône_la_brigade_voeux_mystérieux.svg',
        'Erreur' => 'escape/icône_404_communaux.svg',
    ];

    private const AVATAR_BACKGROUND_IMAGES = [
        'Loupe' => 'escape/lesboisestemoins.png',
        'Aureo' => 'escape/lessaintjeanfoutistes.png',
        'Bidon' => 'escape/lesbidonsdeboiseau.png',
        'Agent' => 'escape/lesagnetstresspeciauxdeboiseau.png',
        'Radeau' => 'escape/lesnaufragesdelavillacheminee.png',
        'Bulle' => 'escape/leselussansfiltre.png',
        'Vœux' => 'escape/labrigadedesvoeuxmysterieux.png',
        'Erreur' => 'escape/les404communaux.png',
    ];
    private const TEAM_AVATARS = [
        'Loupe',
        'Aureo',
        'Bidon',
        'Agent',
        'Radeau',
        'Bulle',
        'Vœux',
        'Erreur',
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

    /**
     * @param string[]|null $avatarKeys
     *
     * @return array<int, array{key:string, image:string}>
     */
    public function getTeamAvatarDetails(?array $avatarKeys = null): array
    {
        $keys = $avatarKeys ?? $this->getTeamAvatars();

        return $this->buildAvatarDetails($keys);
    }

    /**
     * @param string[]|null $avatarKeys
     *
     * @return array<int, array{key:string, image:string}>
     */
    public function getMemberAvatarDetails(?array $avatarKeys = null): array
    {
        $keys = $avatarKeys ?? $this->getMemberAvatars();

        return $this->buildAvatarDetails($keys);
    }

    public function isValidTeamAvatar(string $avatarKey): bool
    {
        return in_array($avatarKey, self::TEAM_AVATARS, true);
    }

    public function isValidMemberAvatar(string $avatarKey): bool
    {
        return in_array($avatarKey, self::MEMBER_AVATARS, true);
    }
    /**
     * @return array{teams:string[], members:string[]}
     */
    public function all(): array
    {
        return [
            'teams' => $this->getTeamAvatarDetails(),
            'members' => $this->getMemberAvatarDetails(),
        ];
    }

    /** @return array<string, string> */
    public function getImages(): array
    {
        return self::AVATAR_IMAGES;
    }

    /**
     * @param string[] $avatarKeys
     *
     * @return array<int, array{key:string, image:string}>
     */
    private function buildAvatarDetails(array $avatarKeys): array
    {
        $knownKeys = array_intersect($avatarKeys, array_keys(self::AVATAR_IMAGES));

        return array_values(array_map(function (string $key): array {
            return [
                'key' => $key,
                'image' => self::AVATAR_IMAGES[$key],
            ];
        }, $knownKeys));
    }
}
