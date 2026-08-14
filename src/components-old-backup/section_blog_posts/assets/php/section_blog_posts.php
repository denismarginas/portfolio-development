<?php

class SectionBlogPosts
{
    public static function render(array $data = []): string
    {
        $blogData = $data['blogData'] ?? (DataService::get_personal_data()['posts_socials'] ?? []);
        $blogPosts = $data['blogPosts'] ?? DataService::getJson('data-post-socials', 'data') ?? [];
        $urlPath = DataService::get_url_path();

        $colors = $blogData['blog_colors'] ?? [];
        $style = '';
        if (!empty($colors['blog_color_primary']) && !empty($colors['blog_color_secondary'])) {
            $style = ' style="'
                . '--blog-color-primary: ' . htmlspecialchars($colors['blog_color_primary']) . ';'
                . '--blog-color-secondary: ' . htmlspecialchars($colors['blog_color_secondary']) . ';'
                . '"';
        }

        $blogPostsHtml = '';

        $postNr = 1;
        foreach ($blogPosts as $post) {
            $blogPostsHtml .= '<div class="dm-blog-post" id="' . $postNr . '">';
            $blogPostsHtml .= '<div class="dm-blog-post-user-data">';
            $blogPostsHtml .= '<div class="dm-blog-post-user-logo">';
            if (!empty($blogData['blog_img_logo'])) {
                $blogPostsHtml .= render_image($urlPath . $blogData['blog_img_logo']);
            }
            $blogPostsHtml .= '</div>';
            $blogPostsHtml .= '<div class="dm-blog-post-information">';
            $blogPostsHtml .= '<h5 class="dm-blog-post-user-name">';
            $blogPostsHtml .= htmlspecialchars($blogData['blog_username'] ?? '');
            $blogPostsHtml .= '</h5>';
            if (!empty($post['date'])) {
                $dateObj = DateTime::createFromFormat('d-m-Y', $post['date']);
                $formatted = $dateObj ? $dateObj->format('j F, Y') : '';
                $blogPostsHtml .= '<p class="dm-blog-post-date">' . htmlspecialchars($formatted) . '</p>';
            }
            $blogPostsHtml .= '</div>';
            $blogPostsHtml .= '</div>';

            $blogPostsHtml .= '<div class="dm-blog-post-content">';

            if (!empty($post['description'])) {
                $lineCount = substr_count($post['description'], "\n") + 1;
                $isLong = $lineCount > 4 || strlen($post['description']) > 150;
                $blogPostsHtml .= '<div class="dm-blog-post-content-text' . ($isLong ? ' see-more' : '') . '">';
                $blogPostsHtml .= nl2br(htmlspecialchars($post['description']));
                $blogPostsHtml .= '</div>';
                if ($isLong) {
                    $blogPostsHtml .= '<p class="dm-blog-post-description-show">See More</p>';
                }
            }

            if (!empty($post['sections'])) {
                foreach ($post['sections'] as $section) {
                    if (!empty($section['section_description'])) {
                        $lineCount = substr_count($section['section_description'], "\n") + 1;
                        $isLong = $lineCount > 4 || strlen($section['section_description']) > 150;
                        $blogPostsHtml .= '<div class="dm-blog-post-section-text' . ($isLong ? ' see-more' : '') . '">';
                        $blogPostsHtml .= htmlspecialchars($section['section_description']);
                        $blogPostsHtml .= '</div>';
                        if ($isLong) {
                            $blogPostsHtml .= '<p class="dm-blog-post-section-description-show">See More</p>';
                        }
                    }

                    if (!empty($section['buttons'])) {
                        $blogPostsHtml .= '<div class="dm-blog-post-section-buttons">';
                        foreach ($section['buttons'] as $button) {
                            if (!empty($button['link'])) {
                                if (strpos($button['link'], 'http') === 0 || strpos($button['link'], 'www') === 0) {
                                    $buttonLink = $button['link'];
                                } else {
                                    $buttonLink = $urlPath . $button['link'];
                                }
                                $blogPostsHtml .= '<a data-button="primary" target="_blank" href="' . htmlspecialchars($buttonLink) . '">';
                                if (!empty($button['icon'])) {
                                    $blogPostsHtml .= svg_get($button['icon']);
                                }
                                if (!empty($button['text'])) {
                                    $blogPostsHtml .= '<span>' . htmlspecialchars($button['text']) . '</span>';
                                }
                                $blogPostsHtml .= '</a>';
                            }
                        }
                        $blogPostsHtml .= '</div>';
                    }
                }
            }

            if (!empty($post['media'])) {
                $blogPostsHtml .= '<div class="dm-blog-post-content-media">';
                $countImages = 0;
                $images = [];
                foreach ($post['media'] as $media) {
                    if (($media['type'] ?? '') === 'photo') {
                        $countImages++;
                        $images[] = render_image($urlPath . $media['path'], true);
                    }
                }
                if ($countImages >= 2) {
                    $blogPostsHtml .= renderSlider($images, true, false);
                }
                foreach ($post['media'] as $media) {
                    $type = $media['type'] ?? '';
                    if ($countImages < 2 && $type === 'photo') {
                        $blogPostsHtml .= render_image($urlPath . $media['path'], true);
                    } elseif ($type === 'video' && isset($media['thumbnail'])) {
                        $blogPostsHtml .= renderVideo($urlPath . $media['path'], $urlPath . $media['thumbnail']);
                    } elseif ($type === 'video') {
                        $blogPostsHtml .= renderVideo($urlPath . $media['path']);
                    }
                }
                $blogPostsHtml .= '</div>';
            }

            $blogPostsHtml .= '</div>';
            $blogPostsHtml .= '</div>';
            $postNr++;
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');

        return str_replace(
            ['{{ blog_style }}', '{{ blog_posts }}'],
            [$style, $blogPostsHtml],
            $template
        );
    }
}
