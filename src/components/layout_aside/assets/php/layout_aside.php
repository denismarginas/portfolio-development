<?php

/**
 * Slot component for section_layout_aside ("aside" column).
 * section_layout_aside reads this block's children directly; render() is only
 * used when the slot is placed somewhere else, as a plain wrapper.
 */
class layout_aside
{
    public static function render(array $data = []): string
    {
        $html = section::render_children($data);
        return $html === '' ? '' : '<div class="layout-aside">' . $html . '</div>';
    }
}
