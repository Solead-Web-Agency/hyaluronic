<select
    <?php if ($attr && is_array($attr) && !empty($attr)): ?>
        <?php foreach ($attr as $key => $value): ?>
            <?php if (is_bool($value) && $value): ?>
                <?= $key ?>="<?= $key ?>"
            <?php elseif (!is_bool($value)): ?>
                <?= $key ?>="<?= $value ?>"
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
>
    <?php // DEFAULT OPTION ?>
    <?php if (isset($default_option) && $default_option): ?>
        <?php
            if (is_array($default_option) && !empty($default_option)) {
                $option_key = array_keys($default_option)[0];
                $option_value = $default_option[$option_key];
            } else {
                $option_key = 0;
                $option_value = $default_option;
            }
        ?>

        <option value="<?= $option_key ?>"
            <?php if (isset($default_option_attr) && $default_option_attr && is_array($default_option_attr) && !empty($default_option_attr)): ?>
                <?php foreach ($default_option_attr as $key => $value): ?>
                    <?php if (is_bool($value) && $value): ?>
                        <?= $key ?>="<?= $key ?>"
                    <?php elseif (!is_bool($value)): ?>
                        <?= $key ?>="<?= $value ?>"
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        >
            <?= $option_value ?>
        </option>
    <?php endif; ?>
    <?php // END DEFAULT OPTION ?>

    <?php // OPTIONS ?>
    <?php if (isset($options) && $options && is_array($options) && !empty($options)): ?>
        <?php foreach ($options as $option_key => $option_name): ?>
            <option value="<?= $option_key ?>"
                <?php if (isset($options_attr) && $options_attr && is_array($options_attr) && !empty($options_attr)): ?>
                    <?php foreach ($options_attr as $key => $value): ?>
                        <?php if (is_bool($value) && $value): ?>
                            <?= $key ?>="<?= $key ?>"
                        <?php elseif (!is_bool($value)): ?>
                            <?= $key ?>="<?= $value ?>"
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            >
                <?= $option_name ?>
            </option>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php // END OPTIONS ?>
</select>