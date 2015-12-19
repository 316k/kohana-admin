<?php
$default_icon = 'glyphicon glyphicon-info-sign';
$default_tab = Kohana::$config->load('admin.default_tab') ?: Arr::get(array_keys($modules), 0);
?>

<ul id="tabs" class="nav nav-tabs" style="margin-bottom: 30px;">
    <?php foreach($modules as $module => $details): ?>
        <li<?php echo $default_tab == $module ? ' class="active"' : '' ?>>
            <a href="<?php echo @$details['href'] ?: '#tab'.ucfirst($module); ?>" <?php echo isset($details['href']) ? 'target="_blank"' : 'data-toggle="tab"'; ?>>
                <i class="<?php echo Arr::get($details, 'icon', $default_icon); ?>"></i>

                <span class="hidden-xs hidden-print" style="margin-left: 10px;">
                    <?php echo __('admin-board-'.strtolower($module)); ?>
                </span>
            </a>
        </li>
    <?php endforeach ?>
</ul>

<div class="tab-content">
    <?php foreach($modules as $module => $details): ?>
        <?php
        if(isset($details['href'])) {
            continue;
        }

        $models = Arr::get($details, 'models', FALSE);

        if($models === FALSE) {
            $models = ORM::factory($module);
        }
        ?>
        <div class="tab-pane<?php echo $default_tab == $module ? ' active' : '' ?>" id="tab<?php echo ucfirst($module); ?>">
            <?php
            foreach(Arr::get($details, 'views', array()) as $view) {
                echo View::factory('admin/board/'.$view, array(
                    'model_name' => $module,
                    'details' => $details,
                    'models' => $models
                ));
            }
            ?>
        </div>
    <?php endforeach ?>
</div>
<script>
function confirm_delete(model, id) {
    if(confirm("Voulez-vous vraiement supprimer l'élément sélectionné ?")) {
        window.location = '<?php echo URL::site("/admin/delete", "http"); ?>/' + model + '/' + id;
    }
}
</script>
