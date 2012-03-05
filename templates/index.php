<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Stud.IP - Presentation "<?= $page['keyword'] ?>"</title>
<!--
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:regular,semibold,italic,italicsemibold" rel="stylesheet">
-->
    <link href="<?= $plugin->getPluginURL() ?>/css/style.css" rel="stylesheet">
    <link href="<?= $plugin->getPluginURL() ?>/css/theme.css" rel="stylesheet">

</head>
<body class="template-<?= htmlReady($page['meta']['template']) ?>">

<div id="jmpress" data-template="<?= htmlReady($page['meta']['template']) ?>">

    <?= $this->render_partial_collection('slide', $page['slides']['slides']) ?>

</div>

<script src="<?= $plugin->getPluginURL() ?>/js/jquery.js"></script>
<script src="<?= $plugin->getPluginURL() ?>/js/jmpress.all.js"></script>
<script src="<?= $plugin->getPluginURL() ?>/js/templates.js"></script>


<script type="text/javascript">
$(function() {

  $('#jmpress').jmpress();

});
</script>

</body>
</html>
