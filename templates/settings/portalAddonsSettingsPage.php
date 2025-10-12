<?php
/**
 * Template for rendering the dynamic settings page
 *
 * Variables available:
 * - $pageTitle
 * - $optionGroup
 * - $settings
 */
?>

<div class="wrap">
    <h1><?php echo esc_html($pageTitle); ?></h1>

    <form method="post" action="options.php">
        <?php settings_fields($optionGroup); ?>
        <?php do_settings_sections($optionGroup); ?>

        <table class="form-table" role="presentation">
            <tbody>
                <?php foreach ($settings as $setting): 
                    $name    = esc_attr($setting['optionName'] ?? '');
                    if (empty($name)) continue;

                    $label   = esc_html($setting['label'] ?? $name);
                    $desc    = esc_html($setting['description'] ?? '');
                    $type    = $setting['type'] ?? 'text';
                    $choices = $setting['choices'] ?? [];
                    $default = $setting['default'] ?? '';
                    $value   = get_option($name, $default);
                ?>
                    <tr>
                        <th scope="row">
                            <label for="<?php echo $name; ?>"><?php echo $label; ?></label>
                        </th>
                        <td>
                            <?php
                            switch ($type) {
                                case 'checkbox':
                                    ?>
                                    <input type="checkbox"
                                        id="<?php echo $name; ?>"
                                        name="<?php echo $name; ?>"
                                        value="1"
                                        <?php checked(1, $value, true); ?> />
                                    <?php
                                    break;

                                case 'select':
                                    ?>
                                    <select id="<?php echo $name; ?>" name="<?php echo $name; ?>">
                                        <?php foreach ($choices as $key => $choiceLabel): ?>
                                            <option value="<?php echo esc_attr($key); ?>"
                                                <?php selected($value, $key); ?>>
                                                <?php echo esc_html($choiceLabel); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php
                                    break;

                                case 'number':
                                    ?>
                                    <input type="number"
                                        id="<?php echo $name; ?>"
                                        name="<?php echo $name; ?>"
                                        value="<?php echo esc_attr($value); ?>" />
                                    <?php
                                    break;

                                default:
                                    ?>
                                    <input type="<?php echo esc_attr($type); ?>"
                                        id="<?php echo $name; ?>"
                                        name="<?php echo $name; ?>"
                                        value="<?php echo esc_attr($value); ?>"
                                        class="regular-text" />
                                    <?php
                                    break;
                            }

                            if (!empty($desc)): ?>
                                <p class="description"><?php echo $desc; ?></p>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php submit_button(); ?>
    </form>
</div>
