<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Stud.IP - Presentation "<?= $page['keyword'] ?>"</title>
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:regular,semibold,italic,italicsemibold|PT+Sans:400,700,400italic,700italic|PT+Serif:400,700,400italic,700italic" rel="stylesheet" />
    <link href="<?= $plugin->getPluginURL() ?>/css/style.css" rel="stylesheet">
    <link href="<?= $plugin->getPluginURL() ?>/css/theme.css" rel="stylesheet">

</head>
<body>


<div id="impress">


    <div class="fallback-message">
        <p>Your browser <b>doesn't support the features required</b> by impress.js, so you are presented with a simplified version of this presentation.</p>
        <p>For the best experience please use the latest <b>Chrome</b> or <b>Safari</b> browser. Firefox 10 and Internet Explorer 10 <i>should</i> also handle it.</p>
    </div>

    <? foreach ($page['slides'] as $slide) :
         $attributes = array();
         foreach ($slide['options'] as $k => $v) {
             $attributes[] = sprintf(' %s="%s"', ($k === "class" ? $k : "data-$k"), $v);
         } ?>

      <div<?= join(" ", $attributes) ?>>
        <?= wikiReady($slide['text'], FALSE) ?>
      </div>
    <? endforeach ?>

</div>

<!--
<script src="<?= $plugin->getPluginURL() ?>/js/impress.js"></script>
-->
<script src="<?= $plugin->getPluginURL() ?>/js/jquery.js"></script>
<script src="<?= $plugin->getPluginURL() ?>/js/jmpress.all.js"></script>


<script type="text/javascript">
$(function() {
  $('#impress').jmpress();
});
</script>

</body>
</html>
