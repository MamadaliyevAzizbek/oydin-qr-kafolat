<?php

namespace App\Support;

class PanelPages
{
    /** @return list<array{id:string,label:string,group:string}> */
    public static function all(): array
    {
        return config('panel_pages', []);
    }

    /** @return list<string> */
    public static function ids(): array
    {
        return array_values(array_map(fn ($p) => (string) $p['id'], self::all()));
    }

    public static function isValid(string $id): bool
    {
        return in_array($id, self::ids(), true);
    }

    /**
     * @param  list<string>|null  $pages
     * @return list<string>
     */
    public static function sanitize(?array $pages): array
    {
        $allowed = self::ids();
        $pages = is_array($pages) ? $pages : [];

        return array_values(array_intersect($allowed, array_map('strval', $pages)));
    }
}
