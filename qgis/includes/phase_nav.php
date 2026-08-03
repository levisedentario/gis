<?php
$activePage = $activePage ?? 'all';
$hiddenNavItems = $hiddenNavItems ?? [];
$navItems = [
    ['key' => 'maps', 'label' => 'Maps', 'title' => 'Maps', 'href' => '/gis/mapping.php'],
    ['key' => 'all', 'label' => 'ALL', 'title' => 'All Area', 'href' => '../MappingDeca/index.php'],
    ['key' => 'phase1', 'label' => 'P1', 'title' => 'Phase 1', 'href' => '../phase1and5/index.php'],
    ['key' => 'phase2', 'label' => 'P2', 'title' => 'Phase 2', 'href' => '../phasetwo/index.php'],
    ['key' => 'phase3', 'label' => 'P3', 'title' => 'Phase 3', 'href' => '../phasethree/index.php'],
    ['key' => 'phase4', 'label' => 'P4', 'title' => 'Phase 4', 'href' => '#'],
    ['key' => 'phase5', 'label' => 'P5', 'title' => 'Phase 5', 'href' => '../phase1and5/index.php'],
    ['key' => 'phase6', 'label' => 'P6', 'title' => 'Phase 6', 'href' => '../phase6/index.php'],
    ['key' => 'target', 'label' => 'TA', 'title' => 'Target Area', 'href' => '../targetarea/index.php'],
];
?>
<div class="phase-toolbar">
    <?php foreach ($navItems as $item): ?>
        <?php if (in_array($item['key'], $hiddenNavItems, true)) continue; ?>
        <?php $isActive = $item['key'] === $activePage; ?>
        <?php $isDisabled = $item['key'] === 'phase4'; ?>
        <?php $isDisabled = $item['key'] === 'phase6'; ?>
        <a class="phase-option<?= $isActive ? ' active' : '' ?><?= $isDisabled ? ' disabled' : '' ?>"
              data-phase="<?= $item['key'] ?>"
           href="<?= $isDisabled ? '#' : $item['href'] ?>"
           title="<?= $item['title'] ?>"><?= $item['label'] ?></a>
    <?php endforeach; ?>
</div>
