<?php

class SectionContactDetails
{
    public static function render(array $data = []): string
    {
        $personalData = DataService::get_personal_data();
        $globalData = $data['globalData'] ?? DataService::getGlobalSettings();
        $contactForm = $data['contactForm'] ?? ($personalData['contact']['form'] ?? []);
        $contactSocials = $data['contactSocials'] ?? ($personalData['socials'] ?? []);
        $urlPath = DataService::get_url_path();

        $themePath = $urlPath . ($globalData['themes_path'] ?? '') . '/' . ($globalData['theme_active']['dir_name'] ?? '');

        $contactScript = '<script src="' . htmlspecialchars($themePath) . '/js/contact-form-personal.js"></script>';

        $contactContent = '';

        if (!empty($contactForm)) {
            $contactContent .= '<li data-motion="transition-fade-0 transition-slideInRight-0">';
            $contactContent .= '<section class="dm-contact-form">';
            $contactContent .= '<h3>' . htmlspecialchars($contactForm['title'] ?? '') . '</h3>';

            if (!empty($contactForm['fields'])) {
                $contactContent .= '<form id="dm-form" target="_self"';
                if (!empty($contactForm['external_form_url'])) {
                    $contactContent .= ' data-external-form-url="' . htmlspecialchars($contactForm['external_form_url']) . '"';
                    $contactContent .= ' onsubmit="return contact_form_exec();"';
                }
                if (!empty($contactForm['external-form-type'])) {
                    $contactContent .= ' data-external-form-type="' . htmlspecialchars($contactForm['external-form-type']) . '"';
                }
                $contactContent .= ' data-form-type="' . (($contactForm['type'] ?? '') === 'steps' ? 'steps' : 'default') . '"';
                $contactContent .= ' action="" autocomplete="off" crossorigin="anonymous">';

                $contactFormFields = $contactForm['fields'] ?? [];
                if (!empty($contactFormFields)) {
                    foreach ($contactFormFields as $contactFormField) {
                        $contactContent .= renderContactFormField($contactFormField);
                    }
                    if (($contactForm['type'] ?? '') === 'steps' && !empty($contactForm['step-fields'])) {
                        $step = 1;
                        foreach ($contactForm['step-fields'] as $stepFields) {
                            $contactContent .= '<div class="step" data-step="' . $step . '">';
                            foreach ($stepFields as $contactFormField) {
                                $contactContent .= renderContactFormField($contactFormField);
                            }
                            $contactContent .= '</div>';
                            $step++;
                        }
                    }
                }

                $contactContent .= '</form>';
            }
            $contactContent .= '</section>';
            $contactContent .= '</li>';
        }

        if (!empty($contactSocials)) {
            $contactContent .= '<li class="dm-socials" data-motion="transition-fade-0 transition-slideInLeft-0">';
            $contactContent .= '<h4>' . htmlspecialchars($contactSocials['title'] ?? '') . '</h4>';

            if (!empty($contactSocials['visual-list'])) {
                $contactContent .= '<div class="dm-socials-list" data-socials="normal-fill">';
                $i = 1;
                foreach ($contactSocials['visual-list'] as $socialItem) {
                    $delay = 0.01 + (0.03 * $i);
                    $contactContent .= '<a data-socials="' . htmlspecialchars($socialItem['icon-svg'] ?? '') . '"';
                    $contactContent .= ' href="' . htmlspecialchars($socialItem['link'] ?? '') . '" target="_blank"';
                    $contactContent .= ' data-motion="transition-fade-0 transition-slideInLeft-0" data-delay="' . $delay . 's">';
                    $contactContent .= svg_get('socials-' . ($socialItem['icon-svg'] ?? ''));
                    $contactContent .= '</a>';
                    $i++;
                }
                $contactContent .= '</div>';
            }

            if (!empty($contactSocials['text-list'])) {
                $contactContent .= '<div class="dm-socials-text" data-motion="transition-fade-0 transition-slideInLeft-0">';
                $i = 1;
                foreach ($contactSocials['text-list'] as $socialTextItem) {
                    $delay = 0.02 + (0.1 * $i);
                    $contactContent .= '<a target="_blank" href="' . htmlspecialchars($socialTextItem['link'] ?? '') . '"';
                    $contactContent .= ' data-motion="transition-fade-0 transition-slideInLeft-0" data-delay="' . $delay . 's">';
                    $contactContent .= '<span>●</span>';
                    $contactContent .= '<b>' . htmlspecialchars($socialTextItem['title'] ?? '') . '</b>';
                    $contactContent .= '<span>' . htmlspecialchars($socialTextItem['text'] ?? '') . '</span>';
                    $contactContent .= '</a>';
                    $i++;
                }
                $contactContent .= '</div>';
            }

            $contactContent .= '</li>';
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');

        return str_replace(
            ['{{ contact_script }}', '{{ contact_content }}'],
            [$contactScript, $contactContent],
            $template
        );
    }
}
