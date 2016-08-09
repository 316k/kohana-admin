<?php
echo View::factory('admin/menu', array(
    'modules' => $modules,
));
$active_tab = Kohana::$config->load('admin.default_tab') ?: Arr::get(array_keys($modules), 0);
?>

<div class="tab-content">
    <?php foreach($modules as $module => $details): ?>
        <?php
        if(Arr::get($details, 'href', '#!') != '#!') {
            continue;
        }

        $models = Arr::get($details, 'models', FALSE);
        $model_name = Arr::get($details, 'model_name', $module);

        if($models === FALSE) {
            $models = ORM::factory($model_name);
        }
        ?>
        <div class="tab-pane<?php echo $active_tab == $module ? ' active' : '' ?>" id="tab<?php echo ucfirst($module); ?>">
            <?php
            foreach(Arr::get($details, 'views', array()) as $view) {
                echo View::factory('admin/board/'.$view, array(
                    'model_name' => $model_name,
                    'details' => $details,
                    'models' => $models
                ));
            }
            ?>
        </div>
    <?php endforeach ?>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#tabs a').click(function() {
        window.location.hash = $(this).attr('href');
    });
    
    $('#tabs a[href="' + window.location.hash + '"]').tab('show');
});

function confirm_delete(model, id) {
    if(confirm("Voulez-vous vraiement supprimer l'élément sélectionné ?")) {
        window.location = '<?php echo URL::site("/admin/delete", "http"); ?>/' + model + '/' + id;
    }
}
</script>
