<?php // SUFFIX ?>
<?php if ($suffix): ?>
    <div class="input-group-addon">
        <span class="input-group-text"><?= $suffix ?></span>
    </div>
<?php endif; ?>
<?php // END SUFFIX ?>

<?php // PREFIX-SUFFIX - CLOSE TAG ?>
<?php if ($prefix || $suffix): ?>
    </div>
<?php endif; ?>
<?php // END PREFIX-SUFFIX - CLOSE TAG ?>

<?php // HELPER ?>
<?php if ($helper): ?>
    <p
        <?php if ($helper_attr && is_array($helper_attr) && !empty($helper_attr)): ?>
            <?php if (!array_key_exists('class', $helper_attr)): ?>
                class="help-block"
            <?php endif; ?>

            <?php foreach ($helper_attr as $key => $value): ?>
                <?php if (is_bool($value) && $value): ?>
                    <?= $key ?>="<?= $key ?>"
                <?php else: ?>
                    <?php if ($key == 'class'): ?>
                        class="help-block <?= $value ?>"
                    <?php else: ?>
                        <?= $key ?>="<?= $value ?>"
                    <?php endif; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            class="help-block"
        <?php endif; ?>
    >
        <?= $helper ?>
    </p>
<?php endif; ?>
<?php // END HELPER ?>

<?php // MARGIN-FORM - CLOSE TAG ?>
<?php if ((bool)$margin_form): ?>
    </div>
<?php endif; ?>
<?php // END MARGIN-FORM - CLOSE TAG ?>

<?php // FORM-GROUP - CLOSE TAG ?>
<?php if ((bool)$form_group): ?>
    </div>
<?php endif; ?>
<?php // END FORM-GROUP - CLOSE TAG ?>