<?php

/**
 * Slot component for section_layout_aside ("content" column).
 * section_layout_aside reads this block's children directly; render() is only
 * used when the slot is placed somewhere else, as a plain wrapper.
 */
class layout_content
{
    public static function render(array $data = []): string
    {
        $html = section::render_children($data);
        return $html === '' ? '' : '<div class="layout-content">' . $html . '</div>';
    }
}
