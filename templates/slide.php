<?
$attributes = array();
foreach ($slide['options'] as $k => $v) {
    $attributes[] = sprintf(' %s="%s"', ($k === "class" ? $k : "data-$k"), $v);
}
?>
<div<?= join(" ", $attributes) ?>>
    <section>
    <?= studip_utf8encode($slide['text']) ?>
    </section>
</div>
