<?php
namespace HooshinaAi\App;

class Fields
{
    const SELECT = 1;
    const CHECKBOX = 2;
    
    public static function render_field($type, $name, $id, $label, $selected = '', $options = [], $attributes = []) {
        $default_attributes = [
            'description' => '',
            'description_if' => '',
            'description_if_condition' => false,
            'class' => '',
            'required' => false,
            'disabled' => false,
            'readonly' => false,
            'placeholder' => '',
            'data' => []
        ];

        $attributes = array_merge($default_attributes, $attributes);
        $field_classes = ['hai-form-field'];
        if ($attributes['class']) {
            $field_classes[] = $attributes['class'];
        }

        $html = '<div class="hai-option-field-wrap">';

        if($label && !in_array($type, [self::CHECKBOX])){
            $html .= sprintf('<label for="%s">%s</label>', esc_attr($id), esc_html($label));
        }

        $html .= sprintf('<div class="%s">', esc_attr(implode(' ', $field_classes)));

        if (!Connection::isConnected()) {
            $html .= hooshina_ai_noconnect_placeholder(true);
        } else {
            switch ($type) {
                case self::SELECT:
                    $html .= self::render_select($name, $id,  $selected, $options, $attributes);
                    break;
                case self::CHECKBOX:
                    $html .= self::render_checkbox($label, $name, $id,  $selected, $attributes);
                    break;
            }

            if ($attributes['description'] || $attributes['description_if']) {
                $des_html = '<p class="hai-des">%s</p>';

                if ($attributes['description_if']) {
                    if ($attributes['description_if_condition']) {
                        $html .= sprintf($des_html, esc_html($attributes['description_if']));
                    }
                } elseif($attributes['description']) {
                    $html .= sprintf($des_html, esc_html($attributes['description']));
                }
            }
        }

        $html .= '</div></div>';
        return $html;
    }

    private static function render_select($name, $id,  $selected, $options, $attributes)
    {
        $html = sprintf('<select name="%s" id="%s"', esc_attr($name), esc_attr($id));
                    
        if ($attributes['required']) {
            $html .= ' required';
        }
        if ($attributes['disabled']) {
            $html .= ' disabled';
        }
        if ($attributes['readonly']) {
            $html .= ' readonly';
        }
        if ($attributes['placeholder']) {
            $html .= sprintf(' placeholder="%s"', esc_attr($attributes['placeholder']));
        }

        foreach ($attributes['data'] as $key => $value) {
            $html .= sprintf(' data-%s="%s"', esc_attr($key), esc_attr($value));
        }

        $html .= '>';
        $html .= sprintf('<option value="">%s</option>', esc_html__('Select...', 'hooshina-ai'));
        
        if (!empty($options)) {
            foreach ($options as $key => $title) {
                $html .= sprintf(
                    '<option %s value="%s">%s</option>',
                    selected($key, $selected, false),
                    esc_attr($key),
                    esc_html($title)
                );
            }
        }
        $html .= '</select>';

        return $html;
    }

    public static function render_checkbox($label, $name, $id, $selected, $attributes)
    {
        $html = sprintf('<div class="hai-checkbox-wrap %s">', $attributes['classes'] ?? '');
        $html .= sprintf(
            '<input type="checkbox" name="%s" id="%s" value="1" %s',
            esc_attr($name),
            esc_attr($id),
            checked($selected, true, false)
        );

        if ($attributes['required']) {
            $html .= ' required';
        }
        if ($attributes['disabled']) {
            $html .= ' disabled';
        }
        if ($attributes['readonly']) {
            $html .= ' readonly';
        }

        foreach ($attributes['data'] as $key => $value) {
            $html .= sprintf(' data-%s="%s"', esc_attr($key), esc_attr($value));
        }

        $html .= '>';
        $html .= sprintf('<label for="%s" class="hai-checkbox-label">', esc_attr($id));
        $html .= '<span class="hai-checkbox-toggle"> <svg viewBox="0 0 10 10" height="10px" width="10px"> <path d="M5,1 L5,1 C2.790861,1 1,2.790861 1,5 L1,5 C1,7.209139 2.790861,9 5,9 L5,9 C7.209139,9 9,7.209139 9,5 L9,5 C9,2.790861 7.209139,1 5,1 L5,9 L5,1 Z"></path> </svg> </span>';
        $html .= '</label>';
        $html .= sprintf('<label for="%s">%s</label>', esc_attr($id), esc_html($label));

        $html .= '</div>';

        return $html;
    }
}