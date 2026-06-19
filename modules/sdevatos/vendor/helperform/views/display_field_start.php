<?php // FORM-GROUP - OPEN TAG ?>
<?php if ((bool)$form_group): ?>
    <div
        <?php if ($form_group_attr && is_array($form_group_attr) && !empty($form_group_attr)): ?>
            <?php if (!array_key_exists('class', $form_group_attr)): ?>
                class="form-group"
            <?php endif; ?>

            <?php foreach ($form_group_attr as $key => $value): ?>
                <?php if (is_bool($value) && $value): ?>
                    <?= $key ?>="<?= $key ?>"
                <?php elseif (!is_bool($value)): ?>
                    <?php if ($key == 'class'): ?>
                        class="form-group <?= $value ?>"
                    <?php else: ?>
                        <?= $key ?>="<?= $value ?>"
                    <?php endif; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            class="form-group"
        <?php endif; ?>
    >
<?php endif; ?>
<?php // END FORM-GROUP - OPEN TAG ?>

<?php // LABEL ?>
<?php if ($label): ?>
    <label
        <?php if ($attr && is_array($attr) && !empty($attr) && array_key_exists('id', $attr)): ?>
            for="<?= $attr['id'] ?>"
        <?php endif; ?>

        <?php if ($label_attr && is_array($label_attr) && !empty($label_attr)): ?>
            <?php foreach ($label_attr as $key => $value): ?>
                <?php if (is_bool($value) && $value): ?>
                    <?= $key ?>="<?= $key ?>"
                <?php elseif (!is_bool($value)): ?>
                    <?= $key ?>="<?= $value ?>"
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    >
        <?= $label ?>
    </label>
<?php endif; ?>
<?php // END LABEL ?>

<?php // MARGIN-FORM - OPEN TAG ?>
<?php if ((bool)$margin_form): ?>
    <div
        <?php if ($margin_form_attr && is_array($margin_form_attr) && !empty($margin_form_attr)): ?>
            <?php if (!array_key_exists('class', $margin_form_attr)): ?>
                class="margin-form"
            <?php endif; ?>

            <?php foreach ($margin_form_attr as $key => $value): ?>
                <?php if (is_bool($value) && $value): ?>
                    <?= $key ?>="<?= $key ?>"
                <?php elseif (!is_bool($value)): ?>
                    <?php if ($key == 'class'): ?>
                        class="margin-form <?= $value ?>"
                    <?php else: ?>
                        <?= $key ?>="<?= $value ?>"
                    <?php endif; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            class="margin-form"
        <?php endif; ?>
    >
<?php endif; ?>
<?php // END MARGIN-FORM - OPEN TAG ?>

<?php // PREFIX-SUFFIX - OPEN TAG ?>
<?php if ($prefix || $suffix): ?>
    <div class="input-group">
<?php endif; ?>
<?php // END PREFIX-SUFFIX - OPEN TAG ?>

<?php // PREFIX ?>
<?php if ($prefix): ?>
    <div class="input-group-addon">
        <span class="input-group-text"><?= $prefix ?></span>
    </div>
<?php endif; ?>
<?php // END PREFIX ?>