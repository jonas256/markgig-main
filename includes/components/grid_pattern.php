<?php
/**
 * MarkGigs Grid Pattern Helper
 * Ported from the React implementation
 */

function render_grid_pattern($options = []) {
    $width = $options['width'] ?? 40;
    $height = $options['height'] ?? 40;
    $x = $options['x'] ?? -1;
    $y = $options['y'] ?? -1;
    $strokeDasharray = $options['strokeDasharray'] ?? "0";
    $squares = $options['squares'] ?? [];
    $className = $options['class'] ?? "";
    $id = "grid-" . uniqid();

    ?>
    <svg aria-hidden="true" class="grid-pattern <?= $className ?>" style="position: absolute; inset: 0; height: 100%; width: 100%; pointer-events: none;">
        <defs>
            <pattern
                id="<?= $id ?>"
                width="<?= $width ?>"
                height="<?= $height ?>"
                patternUnits="userSpaceOnUse"
                x="<?= $x ?>"
                y="<?= $y ?>"
            >
                <path
                    d="M.5 <?= $height ?>V.5H<?= $width ?>"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1"
                    stroke-dasharray="<?= $strokeDasharray ?>"
                />
            </pattern>
        </defs>
        <rect width="100%" height="100%" stroke-width="0" fill="url(#<?= $id ?>)" />
        <?php if (!empty($squares)): ?>
            <svg x="<?= $x ?>" y="<?= $y ?>" class="grid-squares" style="overflow: visible;">
                <?php foreach ($squares as $sq): ?>
                    <rect
                        stroke-width="0"
                        key="<?= $sq[0] ?>-<?= $sq[1] ?>"
                        width="<?= $width - 1 ?>"
                        height="<?= $height - 1 ?>"
                        x="<?= $sq[0] * $width + 1 ?>"
                        y="<?= $sq[1] * $height + 1 ?>"
                        fill="currentColor"
                    />
                <?php endforeach; ?>
            </svg>
        <?php endif; ?>
    </svg>
    <?php
}
