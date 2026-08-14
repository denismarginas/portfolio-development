<?php

class Slider
{
    public static function render(array $data): string
    {
        $slides = $data['slides'] ?? [];
        $showArrows = !empty($data['show_arrows']);
        $showDots = !empty($data['show_dots']);
        $showNumbers = !empty($data['show_numbers']);

        if (!is_array($slides) || empty($slides)) {
            return "";
        }

        $nav = "";
        if ($showArrows) $nav .= "arrows";
        if ($showDots) $nav .= ($nav ? " " : "") . "dots";

        $elementsHtml = "";
        $count = count($slides);
        foreach ($slides as $index => $slide) {
            $content = "";
            if (is_array($slide)) {
                $content = $slide["content"] ?? "";
                if (!empty($slide["image"])) {
                    $img = render_component("image", [
                        "src" => $slide["image"],
                        "alt" => $slide["alt"] ?? "",
                        "lazy" => true,
                    ]);
                    $content = $img . $content;
                }
            } elseif (is_string($slide)) {
                $content = $slide;
            }

            $numHtml = "";
            if ($showNumbers) {
                $numHtml = "<div class=\"number-text\">" . ($index + 1) . " / " . $count . "</div>";
            }

            $elementsHtml .= "<div class=\"slider-element\">" . $numHtml . $content . "</div>\n";
        }

        $template = file_get_contents(__DIR__ . "/../html/template.html");
        return str_replace(
            ["{{ navigation }}", "{{ elements }}"],
            [htmlspecialchars($nav, ENT_QUOTES, "UTF-8"), $elementsHtml],
            $template
        );
    }
}
