<?php

/**
 * project_device_render_conditions
 *
 * Picks which device frame model ("model-01" / "model-02" / "model-03") to
 * render for a given device type ("desktop" or "phone"), based on the
 * project's type(s) and start year.
 *
 * Ported as-is from the legacy post-project-functions.php ->
 * renderDeviceLayout($type, $post_data, $img, $atr) model-selection branch:
 *
 *   - Only evaluated when the project has a data.date.start value at all
 *     (legacy: isset($post_data["date"]["date_start"]) && !empty(...)).
 *     No date.start at all -> falls back to "model-03".
 *   - project.types contains "personal" AND NOT "bachelor's thesis" -> "model-01"
 *   - year < 2022  (and a "model-02" frame is configured for this type) -> "model-02"
 *   - year >= 2022 (and a "model-03" frame is configured for this type) -> "model-03"
 *   - year present but neither guard above matched                     -> "model-03"
 *
 * Field names were updated for the new schema:
 *   legacy post_data["date"]["date_start"]   -> data.date.start
 *   legacy post_data["project_types"]        -> data.project.types
 */
class project_device_render_conditions
{
    private const DEFAULT_MODEL = 'model-03';

    /**
     * @param array $postData     the post's "data" block (post['data'])
     * @param array $deviceModels ['desktop' => [model key => path, ...], 'phone' => [...]]
     * @return array{desktop:string, phone:string} model keys, e.g. "model-02"
     */
    public static function resolve(array $postData, array $deviceModels): array
    {
        return [
            'desktop' => self::resolve_for_type($postData, $deviceModels['desktop'] ?? []),
            'phone' => self::resolve_for_type($postData, $deviceModels['phone'] ?? []),
        ];
    }

    /**
     * @param array $availableModels model key => frame image path, for this device type
     */
    private static function resolve_for_type(array $postData, array $availableModels): string
    {
        $model = self::DEFAULT_MODEL;

        $dateStart = (string) ($postData['date']['start'] ?? '');
        if ($dateStart === '') {
            return $model;
        }

        $year = null;
        if (preg_match('/\d{4}/', $dateStart, $matches)) {
            $year = (int) $matches[0];
        }

        $types = is_array($postData['project']['types'] ?? null) ? $postData['project']['types'] : [];

        if (in_array('personal', $types, true) && !in_array("bachelor's thesis", $types, true)) {
            $model = 'model-01';
        } elseif ($year) {
            if ($year < 2022 && !empty($availableModels['model-02'])) {
                $model = 'model-02';
            } elseif ($year >= 2022 && !empty($availableModels['model-03'])) {
                $model = 'model-03';
            }
        } else {
            $model = self::DEFAULT_MODEL;
        }

        return $model;
    }
}
